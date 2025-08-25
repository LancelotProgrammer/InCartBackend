<?php

namespace App\Http\Controllers;

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
