<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Models\Order;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class InvoiceService
{
    public static function generateInvoice(Order $order)
    {
        $mainCategories = $order->cartProducts
            ->flatMap(fn($cartProduct) => $cartProduct->product->categories)
            ->filter(fn($category) => $category->type === CategoryType::MAIN)
            ->unique('id')
            ->values();

        // Count how many products belong to each category
        $categoryCount = [];
        foreach ($order->cartProducts as $cartProduct) {
            foreach ($cartProduct->product->categories as $category) {
                if ($category->type === CategoryType::MAIN) {
                    $categoryCount[$category->id] = ($categoryCount[$category->id] ?? 0) + 1;
                }
            }
        }

        // Determine best category for each product (category with max count)
        $productToCategory = [];
        foreach ($order->cartProducts as $cartProduct) {
            $eligibleCategories = $cartProduct->product->categories
                ->filter(fn($cat) => $cat->type === CategoryType::MAIN);

            if ($eligibleCategories->isNotEmpty()) {
                $bestCategory = $eligibleCategories->sortByDesc(fn($cat) => $categoryCount[$cat->id] ?? 0)->first();
                $productToCategory[$cartProduct->id] = $bestCategory->id;
            }
        }

        $groupedProducts = $mainCategories->map(function ($category) use ($order, $productToCategory) {
            $products = $order->cartProducts
                ->filter(fn($cartProduct) => ($productToCategory[$cartProduct->id] ?? null) === $category->id)
                ->map(fn($cartProduct) => [
                    'title_ar' => $cartProduct->product->getTranslation('title', 'ar'),
                    'title_en' => $cartProduct->product->getTranslation('title', 'en'),
                    'quantity' => $cartProduct->quantity,
                    'unit_price' => $cartProduct->product->branchProducts->firstWhere('branch_id', $order->branch_id)->price,
                    'total' => $cartProduct->quantity * $cartProduct->product->branchProducts->firstWhere('branch_id', $order->branch_id)->price,
                ]);

            return [
                'category_ar' => $category->getTranslation('title', 'ar'),
                'category_en' => $category->getTranslation('title', 'en'),
                'products' => $products,
            ];
        })->filter(fn($group) => $group['products']->isNotEmpty());

        $invoiceData = [
            'order' => [
                'number' => $order->order_number,
                'created_at' => $order->created_at,
                'subtotal' => $order->subtotal_price,
                'discount' => $order->discount_price,
                'delivery' => $order->delivery_fee,
                'service_fee' => $order->service_fee,
                'tax' => $order->tax_amount,
                'total' => $order->total_price,
            ],
            'customer' => [
                'name' => $order->customer->name,
                'email' => $order->customer->email,
                'phone' => $order->customer->phone,
                'address' => $order->userAddress->title,
            ],
            'app' => [
                'name' => 'In-Cart', // TODO
                'address' => 'City, Country, Address', // TODO
            ],
            'categories' => $groupedProducts->values(),
        ];

        if (!file_exists(storage_path('app/mpdf_tmp'))) {
            mkdir(storage_path('app/mpdf_tmp'), 0755, true);
        }

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf_tmp'),
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'Cairo',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML(View::make('pdf.invoice', $invoiceData)->render());
        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf');
    }
}
