<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\GetHome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class HomeController extends Controller
{
    /**
     * @unauthenticated
     *
     * @group Favorites Actions
     */
    public function getHome(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(...Pipeline::send($request)
            ->through([
                GetHome::class,
            ])
            ->thenReturn());
    }
}
