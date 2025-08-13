<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function getBranches(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
