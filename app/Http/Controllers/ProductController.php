<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProducts(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
