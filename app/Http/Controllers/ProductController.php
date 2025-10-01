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
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * 
     * @queryParam category_id integer The category ID. Example: 1
     * @queryParam search string The English or Arabic name of the product. Example: name
     * @queryParam page integer The page number. Example: 1
     * @queryParam discounted boolean Discounted products. Example: 1
     *
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
     * @header Authorization Bearer {YOUR_AUTH_KEY}
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
