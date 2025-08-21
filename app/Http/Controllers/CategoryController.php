<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\GetCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class CategoryController extends Controller
{
    /**
     * @unauthenticated
     *
     * @group Category Actions
     */
    public function getCategories(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(...Pipeline::send($request)
            ->through([
                GetCategories::class,
            ])
            ->thenReturn());
    }
}
