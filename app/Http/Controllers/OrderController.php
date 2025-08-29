<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AuthorizeUser;
use App\Pipes\Checkout;
use App\Pipes\GetUserPreviousOrders;
use App\Pipes\Order;
use App\Pipes\PaymentGatewayCallback;
use App\Pipes\ValidateUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Pipeline;

class OrderController extends Controller
{
    /**
     * @authenticated
     *
     * @group Order Actions
     */
    public function getUserPreviousOrders(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('get-user-previous-orders'),
                GetUserPreviousOrders::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @bodyParam address_id integer required The user address ID. Example: 1
     * @bodyParam delivery_date date The delivery date. Example: 2025
     * @bodyParam payment_method_id integer required The payment method ID. Example: 1
     * @bodyParam coupon string The city ID. Example: COUPONTEST
     * @bodyParam cart object[] required Cart items. Example: [{"id": 1, "quantity": 10}, {"id": 2, "quantity": 2.5}]
     * @bodyParam cart[].id integer required The product ID. Example: 1
     * @bodyParam cart[].quantity numeric required numeric The product quantity. Example: 1
     * @bodyParam notes string Order notes. Example: Some notes
     *
     * @group Order Actions
     */
    public function order(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('get-user-previous-orders'),
                Order::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @bodyParam order_id integer The city ID. Example: 1
     * @bodyParam payment_method_id integer The city ID. Example: product name in english or arabic
     * @bodyParam token string The city ID. Example: 1
     * @bodyParam metadata object[] metadata key-value. Example: [{"key": 1, "value": 1}, {"key": 2, "value": 2}]
     * @bodyParam metadata[].key string The city ID. Example: 1
     * @bodyParam metadata[].value string The city ID. Example: 1
     *
     * @group Order Actions
     */
    public function checkout(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('get-user-previous-orders'),
                Checkout::class,
            ])
            ->thenReturn());
    }

    public function paymentGatewayCallback(Request $request): JsonResource
    {
        return new JsonResource(Pipeline::send($request)
            ->through([
                PaymentGatewayCallback::class,
            ])
            ->thenReturn());
    }
}
