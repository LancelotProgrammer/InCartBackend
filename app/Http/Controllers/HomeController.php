<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetHome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class HomeController extends Controller
{
    /**
     * @unauthenticated
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
