<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class SettingController extends Controller
{
    /**
     * @unauthenticated
     *
     * @group Setting Actions
     */
    public function getSettings(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetSettings::class,
            ])
            ->thenReturn());
    }
}
