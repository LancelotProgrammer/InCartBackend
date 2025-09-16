<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AddProductToPackage;
use App\Pipes\AuthorizeUser;
use App\Pipes\CreatePackage;
use App\Pipes\DeletePackage;
use App\Pipes\DeleteProductFromPackage;
use App\Pipes\GetPackageProducts;
use App\Pipes\GetPackages;
use App\Pipes\UpdatePackage;
use App\Pipes\ValidateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class PackageController extends Controller
{
    /**
     * @authenticated
     * @group Packages Actions
     *
     * @queryParam product_id int Optional. Filter to check if product exists in packages.
     */
    public function getPackages(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(...Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('get-packages'),
                GetPackages::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Packages Actions
     *
     * @bodyParam title string required The package name.
     * @bodyParam description string optional The package description.
     */
    public function createPackage(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('create-package'),
                CreatePackage::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Packages Actions
     *
     * @urlParam id int required The package ID.
     * @bodyParam title string optional The package name.
     * @bodyParam description string optional The package description.
     */
    public function updatePackage(Request $request, int $id): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('update-package'),
                UpdatePackage::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Packages Actions
     *
     * @urlParam id int required The package ID.
     */
    public function deletePackage(Request $request, int $id): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('delete-package'),
                DeletePackage::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Packages Actions
     *
     * @urlParam id int required The package ID.
     */
    public function getPackageProducts(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('get-package-products'),
                GetPackageProducts::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Packages Actions
     *
     * @urlParam package_id int required The package ID.
     * @urlParam product_id int required The product ID.
     */
    public function addProductToPackage(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('add-product-to-package'),
                AddProductToPackage::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     * @group Packages Actions
     *
     * @urlParam package_id int required The package ID.
     * @urlParam product_id int required The product ID.
     */
    public function deleteProductFromPackage(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                ValidateUser::class,
                new AuthorizeUser('delete-product-from-package'),
                DeleteProductFromPackage::class,
            ])
            ->thenReturn());
    }
}
