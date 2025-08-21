<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
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
    public function getBranches(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(...Pipeline::send($request)
            ->through([
                GetBranches::class,
            ])
            ->thenReturn());
    }
}
