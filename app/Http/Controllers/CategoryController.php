<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategories(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
