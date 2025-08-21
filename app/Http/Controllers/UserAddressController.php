<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AddUserAddress;
use App\Pipes\AuthorizeUser;
use App\Pipes\DeleteUserAddress;
use App\Pipes\GetUserAddresses;
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
        return new SuccessfulResponseResourceWithMetadata(...Pipeline::send($request)
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
     * @group User Addresses Actions
     */
    public function addUserAddress(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('add-user-address'),
                AddUserAddress::class,
            ])
            ->thenReturn());
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
