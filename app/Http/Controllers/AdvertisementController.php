<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\AuthorizeUser;
use App\Pipes\CreateAdvertisementClick;
use App\Pipes\ValidateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class AdvertisementController extends Controller
{
    /**
     * @unauthenticated
     *
     * @group Advertisement Actions
     */
    public function createAdvertisementClick(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('create-advertisement-click'),
                CreateAdvertisementClick::class,
            ])
            ->thenReturn());
    }
}
