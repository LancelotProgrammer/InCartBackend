<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetBranches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class BranchController extends Controller
{
    /**
     * @authenticated
     *
     * @group Branch Actions
     *
     * @queryParam city_id integer The city ID. Example: 1
     */
    public function getBranches(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetBranches::class,
            ])
            ->thenReturn());
    }
}
