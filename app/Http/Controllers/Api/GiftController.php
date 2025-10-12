<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetGiftDetail;
use App\Pipes\GetGifts;
use App\Pipes\RedeemGift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class GiftController extends Controller
{
    /**
     * @authenticated
     *
     * @group Loyalty Actions
     */
    public function getGifts(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetGifts::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @urlParam id int required The gift ID.
     *
     * @group Loyalty Actions
     */
    public function getGiftDetail(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetGiftDetail::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @urlParam id int required The gift ID.
     *
     * @group Loyalty Actions
     */
    public function redeemGift(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                RedeemGift::class,
            ])
            ->thenReturn());
    }
}
