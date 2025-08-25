<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AuthorizeUser;
use App\Pipes\GetUserPreviousOrders;
use App\Pipes\ValidateUser;
use Illuminate\Http\Request;
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
                GetUserPreviousOrders::class
            ])
            ->thenReturn());
    }

    public function order(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function checkout(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
