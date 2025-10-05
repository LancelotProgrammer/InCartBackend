<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\CreateUserAddress;
use App\Pipes\DeleteUserAddress;
use App\Pipes\GetUserAddresses;
use App\Pipes\UpdateUserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class UserAddressController extends Controller
{
    /**
     * @authenticated
     *
     * @group User Addresses Actions
     */
    public function getUserAddresses(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                GetUserAddresses::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @bodyParam title string required Title. Example: title
     * @bodyParam description string required Description. Example: description
     * @bodyParam phone string required Phone. Example: +96654546123
     * @bodyParam type integer required User address type. Example: 1
     * @bodyParam latitude float required Latitude. Example: 3.0
     * @bodyParam longitude float required Longitude. Example: 3.0
     *
     * @group User Addresses Actions
     */
    public function createUserAddress(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                CreateUserAddress::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @bodyParam title string required Title. Example: title
     * @bodyParam description string required Description. Example: description
     * @bodyParam phone string required Phone. Example: +96654546123
     * @bodyParam type integer required User address type. Example: 1
     * @bodyParam latitude float required Latitude. Example: 3.0
     * @bodyParam longitude float required Longitude. Example: 3.0
     *
     * @group User Addresses Actions
     */
    public function updateUserAddress(Request $request): EmptySuccessfulResponseResource
    {
        Pipeline::send($request)
            ->through([
                UpdateUserAddress::class,
            ])
            ->thenReturn();

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @authenticated
     *
     * @group User Addresses Actions
     */
    public function deleteUserAddress(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                DeleteUserAddress::class,
            ])
            ->thenReturn());
    }
}
