<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->route()->parameters(), [
            'id' => 'integer|required|exists:orders,id',
        ]);
        if ($validator->fails()) {
            abort(422);
        }

        $order = Order::where('id', $request->id)
            ->where('customer_id', '=', auth()->user()->id)
            ->first();

        return InvoiceService::generateInvoice($order);
    }
}
