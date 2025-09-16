<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AddProductToFavorites;
use App\Pipes\AuthorizeUser;
use App\Pipes\DeleteProductFromFavorites;
use App\Pipes\GetFavoriteProducts;
use App\Pipes\ValidateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class FavoriteController extends Controller
{
    /**
     * @authenticated
     * @group Favorites Actions
     */
    public function getFavoriteProducts(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('get-favorite-products'),
                GetFavoriteProducts::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Favorites Actions
     *
     * @urlParam id int required The product ID.
     */
    public function addProductToFavorites(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('add-product-to-favorite'),
                AddProductToFavorites::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Favorites Actions
     *
     * @urlParam id int required The product ID.
     */
    public function deleteProductFromFavorites(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('delete-product-from-favorite'),
                DeleteProductFromFavorites::class,
            ])
            ->thenReturn());
    }
}
