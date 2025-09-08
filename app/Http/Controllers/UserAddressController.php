<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AuthorizeUser;
use App\Pipes\CreateUserAddress;
use App\Pipes\DeleteUserAddress;
use App\Pipes\GetUserAddresses;
use App\Pipes\UpdateUserAddress;
use App\Pipes\ValidateUser;
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
                ValidateUser::class,
                new AuthorizeUser('get-user-address'),
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
     * @bodyParam longitude float required Longitude. Example: 3.0
     * @bodyParam latitude float required Latitude. Example: 3.0
     * 
     * @group User Addresses Actions
     */
    public function createUserAddress(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('add-user-address'),
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
     * @bodyParam longitude float required Longitude. Example: 3.0
     * @bodyParam latitude float required Latitude. Example: 3.0
     * 
     * @group User Addresses Actions
     */
    public function updateUserAddress(Request $request): EmptySuccessfulResponseResource
    {
        Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('update-user-address'),
                UpdateUserAddress::class,
            ])
            ->thenReturn();

        return new EmptySuccessfulResponseResource();
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
                ValidateUser::class,
                new AuthorizeUser('delete-user-address'),
                DeleteUserAddress::class,
            ])
            ->thenReturn());
    }
}
