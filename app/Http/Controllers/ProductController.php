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
     * @queryParam category_id integer The city ID. Example: 1
     * @queryParam search string The city ID. Example: product name in english or arabic
     * @queryParam page integer The city ID. Example: 1
     * @group Product Actions
     */
    public function getProducts(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
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
