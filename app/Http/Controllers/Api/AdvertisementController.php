<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\CreateAdvertisementClick;
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
                CreateAdvertisementClick::class,
            ])
            ->thenReturn());
    }
}
