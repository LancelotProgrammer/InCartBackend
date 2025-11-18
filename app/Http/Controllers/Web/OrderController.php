<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! request()->user()->hasPermission('view-invoice-order')) {
            Log::warning('OrderController: User is not allowed to view invoice', ['user_id' => request()->user()->id]);
            abort(403);
        }

        $request->merge(['id' => $request->route('id')]);

        $request->validate([
            'id' => 'integer|required|exists:orders,id',
        ]);

        return OrderService::managerInvoice($request->id);
    }
}
