<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function getPackages(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function getPackageProducts(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function addProduct(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function deleteProduct(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
