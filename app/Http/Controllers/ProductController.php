<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\GetProductDetails;
use App\Pipes\GetProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class ProductController extends Controller
{
    /**
     * @unauthenticated
     *
     * @group Product Actions
     */
    public function getProducts(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(...Pipeline::send($request)
            ->through([
                GetProducts::class,
            ])
            ->thenReturn());
    }

    /**
     * @unauthenticated
     *
     * @group Product Actions
     */
    public function getProductDetails(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetProductDetails::class,
            ])
            ->thenReturn());
    }
}
