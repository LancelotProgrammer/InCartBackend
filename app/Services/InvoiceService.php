<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceService
{
    public static function generateInvoice(Order $order): Response
    {
        return Pdf::loadView(
            'pdf.invoice',
            [
                'order' => $order,
            ]
        )->stream();
    }
}
