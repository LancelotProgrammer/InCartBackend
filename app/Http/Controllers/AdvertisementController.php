<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\GetAdvertisements;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class AdvertisementController extends Controller
{
    /**
     * @unauthenticated
     *
     * @group Advertisement Actions
     */
    public function getAdvertisements(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                GetAdvertisements::class,
            ])
            ->thenReturn());
    }
}
