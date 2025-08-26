<?php

namespace App\Pipes;

use App\Constants\CacheKeys;
use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Models\Advertisement;
use App\Models\BranchProduct;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

class GetHome
{
    public function __invoke(Request $request, Closure $next)
    {
        $result = Cache::remember(CacheKeys::HOME, 3600, function () {
            $statuses = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::STATUS->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $videos = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::VIDEO->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $offers = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::OFFER->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $cards = DB::table('advertisements')
                ->where('type', '=', AdvertisementType::CARD->value)
                ->branchScope()
                ->publishedScope()
                ->limit(10)
                ->orderBy('order')
                ->get();
            $products = DB::table('products')
                ->limit(10)
                ->orderByDesc('id')
                ->get();

            $statusesResult = [];
            foreach ($statuses as $status) {
                $statusLinkType = Advertisement::getLinkValue($status);
                $statusesResult[] = match ($statusLinkType) {
                    AdvertisementLink::PRODUCT->value => $this->getAdvertisementProduct($status),
                    AdvertisementLink::CATEGORY->value => $this->getCategory($status),
                    AdvertisementLink::EXTERNAL->value => $this->getImageExternalUrl($status),
                    default => throw new InvalidArgumentException('Advertisement link is not supported'),
                };
            }

            $videosResult = [];
            foreach ($videos as $video) {
                $videosResult[] = $this->getVideoExternalUrl($video);
            }

            $offersResult = [];
            foreach ($offers as $offer) {
                $offersResult[] = $this->getAdvertisementOfferProduct($offer);
            }

            $cardsResult = [];
            foreach ($cards as $card) {
                $cardLinkType = Advertisement::getLinkValue($card);
                $cardsResult[] = match ($cardLinkType) {
                    AdvertisementLink::PRODUCT->value => $this->getAdvertisementProduct($card),
                    AdvertisementLink::CATEGORY->value => $this->getCategory($card),
                    AdvertisementLink::EXTERNAL->value => $this->getImageExternalUrl($card),
                    default => throw new InvalidArgumentException('Advertisement link is not supported'),
                };
            }

            $newProductsResult = [];
            foreach ($products as $product) {
                $newProductsResult[] = $this->getProductDetails($product);
            }

            return [
                'statuses' => $statusesResult,
                'offers' => $offersResult,
                'cards' => $cardsResult,
                'videos' => $videosResult,
                'new_products' => $newProductsResult,
            ];
        });

        return $next($result);
    }

    private function getAdvertisementProduct(stdClass $advertisement): array
    {
        $advertisementImage = DB::table('advertisement_file')
            ->join('files', 'files.id', '=', 'advertisement_file.file_id')
            ->where('advertisement_file.advertisement_id', $advertisement->id)
            ->select('files.url')
            ->first();

        $product = DB::table('products')
            ->where('id', $advertisement->product_id)
            ->first();

        $productImage = DB::table('product_file')
            ->join('files', 'files.id', '=', 'product_file.file_id')
            ->where('product_file.product_id', $product->id)
            ->select('files.url')
            ->first();

        $branchProduct = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->branchScope()
            ->publishedScope()
            ->where('branch_product.product_id', $product->id)
            ->first();

        $productCategory = DB::table('product_category')
            ->join('categories', 'categories.id', '=', 'product_category.category_id')
            ->where('product_category.product_id', $product->id)
            ->select(['categories.title', 'categories.id'])
            ->first();

        return [
            'id' => $advertisement->id,
            'image' => $advertisementImage->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::PRODUCT->value,
            'created_at' => $advertisement->created_at,
            'product' => [
                'id' => $product->id,
                'title' => get_translatable_attribute($product->title),
                'image' => $productImage->url,
                'price' => $branchProduct->price,
                'discount' => $branchProduct->discount,
                'discount_price' => BranchProduct::getDiscountPriceValue($branchProduct),
                'unit' => $branchProduct->unit,
                'expired_at' => $branchProduct->expires_at,
                'limit' => $branchProduct->maximum_order_quantity,
                'category' => [
                    'id' => $productCategory->id,
                    'title' => get_translatable_attribute($productCategory->title),
                ]
            ]
        ];
    }

    private function getCategory(stdClass $advertisement): array
    {
        $advertisementImage = DB::table('advertisement_file')
            ->join('files', 'advertisement_file.id', '=', 'files.id')
            ->where('advertisement_file.id', '=', $advertisement->id)
            ->first();

        $category = DB::table('categories')->where('id', '=', $advertisement->id)->first();

        $categoryImage = DB::table('category_file')
            ->join('files', 'category_file.id', '=', 'files.id')
            ->where('category_file.id', '=', $category->id)
            ->first();

        return [
            'id' => $advertisement->id,
            'image' => $advertisementImage->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::CATEGORY->value,
            'created_at' => $advertisement->created_at,
            'category' => [
                'id' => $category->id,
                'title' => get_translatable_attribute($category->title),
                'image' => $categoryImage->url,
            ]
        ];
    }

    private function getImageExternalUrl(stdClass $advertisement): array
    {
        $advertisementImage = DB::table('advertisement_file')
            ->join('files', 'files.id', '=', 'advertisement_file.file_id')
            ->where('advertisement_file.advertisement_id', $advertisement->id)
            ->select('files.url')
            ->first();

        return [
            'id' => $advertisement->id,
            'image' => $advertisementImage->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::EXTERNAL->value,
            'created_at' => $advertisement->created_at,
            'external' => [
                'url' => $advertisement->url,
            ]
        ];
    }

    private function getVideoExternalUrl(stdClass $advertisement): array
    {
        $advertisementVideo = DB::table('advertisement_file')
            ->join('files', 'files.id', '=', 'advertisement_file.file_id')
            ->where('advertisement_file.advertisement_id', $advertisement->id)
            ->select('files.url')
            ->first();

        return [
            'id' => $advertisement->id,
            'video' => $advertisementVideo->url,
            'title' => get_translatable_attribute($advertisement->title),
            'type' => AdvertisementLink::EXTERNAL->value,
            'created_at' => $advertisement->created_at,
            'external' => [
                'url' => $advertisement->url,
            ]
        ];
    }

    private function getAdvertisementOfferProduct(stdClass $advertisement): array
    {
        $product = DB::table('products')
            ->where('id', $advertisement->product_id)
            ->first();

        $productImage = DB::table('product_file')
            ->join('files', 'files.id', '=', 'product_file.file_id')
            ->where('product_file.product_id', $product->id)
            ->select('files.url')
            ->first();

        $branchProduct = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->branchScope()
            ->publishedScope()
            ->where('branch_product.product_id', $product->id)
            ->first();

        $productCategory = DB::table('product_category')
            ->join('categories', 'categories.id', '=', 'product_category.category_id')
            ->where('product_category.product_id', $product->id)
            ->select(['categories.title', 'categories.id'])
            ->first();

        return [
            'id' => $product->id,
            'title' => get_translatable_attribute($product->title),
            'image' => $productImage->url,
            'price' => $branchProduct->price,
            'discount' => $branchProduct->discount,
            'discount_price' => BranchProduct::getDiscountPriceValue($branchProduct),
            'unit' => $branchProduct->unit,
            'expired_at' => $branchProduct->expires_at,
            'limit' => $branchProduct->maximum_order_quantity,
            'category' => [
                'id' => $productCategory->id,
                'title' => get_translatable_attribute($productCategory->title),
            ]
        ];
    }

    private function getProductDetails(stdClass $product): array
    {
        $productImage = DB::table('product_file')
            ->join('files', 'files.id', '=', 'product_file.file_id')
            ->where('product_file.product_id', $product->id)
            ->select('files.url')
            ->first();

        $branchProduct = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->branchScope()
            ->publishedScope()
            ->where('branch_product.product_id', $product->id)
            ->first();

        $productCategory = DB::table('product_category')
            ->join('categories', 'categories.id', '=', 'product_category.category_id')
            ->where('product_category.product_id', $product->id)
            ->select(['categories.title', 'categories.id'])
            ->first();

        return [
            'id' => $product->id,
            'title' => get_translatable_attribute($product->title),
            'image' => $productImage->url,
            'price' => $branchProduct->price,
            'discount' => $branchProduct->discount,
            'discount_price' => BranchProduct::getDiscountPriceValue($branchProduct),
            'unit' => $branchProduct->unit,
            'expired_at' => $branchProduct->expires_at,
            'limit' => $branchProduct->maximum_order_quantity,
            'category' => [
                'id' => $productCategory->id,
                'title' => get_translatable_attribute($productCategory->title),
            ]
        ];
    }
}
