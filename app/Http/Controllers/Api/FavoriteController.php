<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AddProductToFavorites;
use App\Pipes\DeleteProductFromFavorites;
use App\Pipes\GetFavoriteProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class FavoriteController extends Controller
{
    /**
     * @authenticated
     *
     * @group Favorites Actions
     */
    public function getFavoriteProducts(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                GetFavoriteProducts::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @group Favorites Actions
     *
     * @urlParam id int required The product ID.
     */
    public function addProductToFavorites(Request $request): EmptySuccessfulResponseResource
    {
        Pipeline::send($request)
            ->through([
                AddProductToFavorites::class,
            ])
            ->thenReturn();

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @authenticated
     *
     * @group Favorites Actions
     *
     * @urlParam id int required The product ID.
     */
    public function deleteProductFromFavorites(Request $request): EmptySuccessfulResponseResource
    {
        Pipeline::send($request)
            ->through([
                DeleteProductFromFavorites::class,
            ])
            ->thenReturn();

        return new EmptySuccessfulResponseResource;
    }
}
