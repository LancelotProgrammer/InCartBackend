<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!request()->user()->hasPermission('view-invoice-order'))
        {
            abort(403);
        }

        $validator = Validator::make($request->route()->parameters(), [
            'id' => 'integer|required|exists:orders,id',
        ]);
        if ($validator->fails()) {
            abort(422);
        }

        return Pdf::loadView(
            'pdf.invoice',
            [
                'order' => Order::where('id', $request->id)->first(),
            ]
        )->stream();
    }
}
