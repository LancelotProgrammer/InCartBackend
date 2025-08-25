<?php

namespace App\Pipes;

use App\Constants\CacheKeys;
use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Models\BranchProduct;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;

class GetHome
{
    public function __invoke(Request $request, Closure $next)
    {
        $result = Cache::remember(CacheKeys::HOME, 3600, function () {
            $statuses = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::STATUS->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $videos = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::VIDEO->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $offers = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::OFFER->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $cards = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::CARD->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $products = DB::table('products')
                ->limit(10)
                ->orderByDesc('id')
                ->get();

            $statusesResult = [];
            foreach ($statuses as $status) {
                $statusesResult[] = match (true) {
                    $status->product_id && $status->category_id => $this->getAdvertisementProduct($status),
                    $status->category_id && !$status->product_id => $this->getCategory($status),
                    default => $this->getImageExternalUrl($status),
                };
            }
            $videosResult = [];
            foreach ($videos as $status) {
                $videosResult[] = $this->getVideoExternalUrl($status);
            }
            $offersResult = [];
            foreach ($offers as $status) {
                $offersResult[] = $this->getAdvertisementOfferProduct($status);
            }
            $cardsResult = [];
            foreach ($cards as $status) {
                $cardsResult[] = match (true) {
                    $status->product_id && $status->category_id => $this->getAdvertisementProduct($status),
                    $status->category_id && !$status->product_id => $this->getCategory($status),
                    default => $this->getImageExternalUrl($status),
                };
            }
            $newProductsResult = [];
            foreach ($products as $product) {
                $newProductsResult[] = $this->getProductDetails($product);
            }

            return [
                'statuses' => $statusesResult,
                'offers' => $offersResult,
                'cards' => $cardsResult,
                'videos' => $videosResult,
                'new_products' => $newProductsResult,
            ];
        });

        return $next($result);
    }

    private function getProductDetails(stdClass $product): array
    {
        $productImage = DB::table('product_file')
            ->join('files', 'product_file.id', '=', 'files.id')
            ->where('product_file.id', '=', $product->id)
            ->first();

        $branchProduct = DB::table('branch_product')
            ->join('products', 'branch_product.id', '=', 'products.id')
            ->branchScope()
            ->publishedScope()
            ->where('branch_product.id', '=', $product->id)
            ->first();

        $productCategory = DB::table('product_category')
            ->join('products', 'product_category.id', '=', 'products.id')
            ->where('product_category.id', '=', $product->id)
            ->first();

        return [
            'id' => $product->id,
            'price' => $branchProduct->price,
            'discount' => $branchProduct->discount,
            'discount_price' => BranchProduct::getDiscountPriceValue($branchProduct),
            'unit' => $branchProduct->unit,
            'expired_at' => $branchProduct->expires_at,
            'limit' => $branchProduct->maximum_order_quantity,
            'image' => $productImage->id,
            'category' => [
                'id' => $productCategory->id,
                'title' => get_translatable_attribute($productCategory->title),
            ]
        ];
    }

    private function getAdvertisementProduct(stdClass $advertisement): array
    {
        $advertisementImage = DB::table('advertisement_file')
            ->join('files', 'advertisement_file.id', '=', 'files.id')
            ->where('advertisement_file.id', '=', $advertisement->id)
            ->first();

        $product = DB::table('products')->where('id', '=', $advertisement->id)->first();

        $productImage = DB::table('product_file')
            ->join('files', 'product_file.id', '=', 'files.id')
            ->where('product_file.id', '=', $product->id)
            ->first();

        $branchProduct = DB::table('branch_product')
            ->join('products', 'branch_product.id', '=', 'products.id')
            ->branchScope()
            ->publishedScope()
            ->where('branch_product.id', '=', $product->id)
            ->first();

        $productImage = DB::table('product_category')
            ->join('products', 'product_category.id', '=', 'products.id')
            ->where('product_category.id', '=', $product->id)
            ->first();

        $productCategory = DB::table('product_category')
            ->join('products', 'product_category.id', '=', 'products.id')
            ->where('product_category.id', '=', $product->id)
            ->first();

        return [
            'id' => $advertisement->id,
            'image' => $advertisementImage->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::PRODUCT->value,
            'created_at' => $advertisement->created_at,
            'product' => [
                'id' => $product->id,
                'price' => $branchProduct->price,
                'discount' => $branchProduct->discount,
                'discount_price' => BranchProduct::getDiscountPriceValue($branchProduct),
                'unit' => $branchProduct->unit,
                'expired_at' => $branchProduct->expires_at,
                'limit' => $branchProduct->maximum_order_quantity,
                'image' => $productImage->id,
                'category' => [
                    'id' => $productCategory->id,
                    'title' => get_translatable_attribute($productCategory->title),
                ]
            ]
        ];
    }

    private function getAdvertisementOfferProduct(stdClass $advertisement): array
    {
        $product = DB::table('products')->where('id', '=', $advertisement->id)->first();

        $productImage = DB::table('product_file')
            ->join('files', 'product_file.id', '=', 'files.id')
            ->where('product_file.id', '=', $product->id)
            ->first();

        $branchProduct = DB::table('branch_product')
            ->join('products', 'branch_product.id', '=', 'products.id')
            ->branchScope()
            ->publishedScope()
            ->where('branch_product.id', '=', $product->id)
            ->first();

        $productCategory = DB::table('product_category')
            ->join('products', 'product_category.id', '=', 'products.id')
            ->where('product_category.id', '=', $product->id)
            ->first();

        return [
            'id' => $product->id,
            'price' => $branchProduct->price,
            'discount' => $branchProduct->discount,
            'discount_price' => BranchProduct::getDiscountPriceValue($branchProduct),
            'unit' => $branchProduct->unit,
            'expired_at' => $branchProduct->expires_at,
            'limit' => $branchProduct->maximum_order_quantity,
            'image' => $productImage->id,
            'category' => [
                'id' => $productCategory->id,
                'title' => get_translatable_attribute($productCategory->title),
            ]
        ];
    }

    private function getCategory(stdClass $advertisement): array
    {
        $advertisementImage = DB::table('advertisement_file')
            ->join('files', 'advertisement_file.id', '=', 'files.id')
            ->where('advertisement_file.id', '=', $advertisement->id)
            ->first();

        $category = DB::table('categories')->where('id', '=', $advertisement->id)->first();

        $categoryImage = DB::table('category_file')
            ->join('files', 'category_file.id', '=', 'files.id')
            ->where('category_file.id', '=', $category->id)
            ->first();

        return [
            'id' => $advertisement->id,
            'image' => $advertisementImage->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::CATEGORY->value,
            'created_at' => $advertisement->created_at,
            'category' => [
                'id' => $category->id,
                'title' => get_translatable_attribute($category->title),
                'image' => $categoryImage->url,
            ]
        ];
    }

    private function getImageExternalUrl(stdClass $advertisement): array
    {
        $advertisementImage = DB::table('advertisement_file')
            ->join('files', 'advertisement_file.id', '=', 'files.id')
            ->where('advertisement_file.id', '=', $advertisement->id)
            ->first();

        return [
            'id' => $advertisement->id,
            'image' => $advertisementImage->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::CATEGORY->value,
            'created_at' => $advertisement->created_at,
            'external' => [
                'url' => $advertisement->url,
            ]
        ];
    }

    private function getVideoExternalUrl(stdClass $advertisement): array
    {
        $advertisementVideo = DB::table('advertisement_file')
            ->join('files', 'advertisement_file.id', '=', 'files.id')
            ->where('advertisement_file.id', '=', $advertisement->id)
            ->first();

        return [
            'id' => $advertisement->id,
            'video' => $advertisementVideo->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::CATEGORY->value,
            'created_at' => $advertisement->created_at,
            'external' => [
                'url' => $advertisement->url,
            ]
        ];
    }
}
