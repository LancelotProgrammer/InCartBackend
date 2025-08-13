<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function getFavoriteProducts(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function addProduct(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function deleteProduct(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
