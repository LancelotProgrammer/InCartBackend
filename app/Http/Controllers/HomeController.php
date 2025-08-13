<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function getHome(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
