<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetHome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class HomeController extends Controller
{
    /**
     * @unauthenticated
     *
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     *
     * @group Home Actions
     */
    public function getHome(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetHome::class,
            ])
            ->thenReturn());
    }
}
