<style>
    .missing-preview {
        border: 1px solid #f5c6cb;
        background-color: #f8d7da;
        color: #721c24;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
        font-weight: bold;
        max-width: 400px;
        margin: 20px auto;
        font-family: Arial, sans-serif;
    }
</style>

@php
    use App\Enums\AdvertisementLink;
    use App\Models\Category;
    use App\Models\BranchProduct;
    use App\Models\Product;

    $product = Product::where('id', '=', $get('product_id'))->first();
    if ($product !== null) {
        $productImageUrl = $product->files->first()?->url;
        $productTile = $product->title;
    }

    $category = Category::where('id', '=', $get('category_id'))->first();
    if ($category !== null) {
        $categoryImageUrl = $category->files->first()?->url;
        $categoryTitle = $category->title;
    }

    $branchProduct = BranchProduct::where('product_id', '=', $get('product_id'))->where('branch_id', '=', $get('branch_id'))->first();
    if ($branchProduct !== null) {
        $price = $branchProduct->price;
        $discountPrice = $branchProduct->price - $branchProduct->price * ($branchProduct->discount / 100);
    }

    $image = $get('file');
    if ($image !== null) {
        $imageUrl = $image->temporaryUrl();
    }

@endphp

@if ($get('link') === AdvertisementLink::PRODUCT->value)
    @if ($product !== null && $branchProduct !== null && $category !== null && $image !== null)
        <x-product-status-ad-preview productImageUrl="{{ $productImageUrl }}" productTile="{{ $productTile }}"
            categoryTitle="{{ $categoryTitle }}" imageUrl="{{ $imageUrl }}" discountPrice="{{ $discountPrice }}"
            price="{{ $price }}" />
    @else
        <div class="missing-preview">
            Cannot create preview because some information is missing.
        </div>
    @endif
@elseif ($get('link') === AdvertisementLink::CATEGORY->value)
    @if ($category !== null && $image !== null)
        <x-category-status-ad-preview categoryImageUrl="{{ $categoryImageUrl }}" categoryTitle="{{ $categoryTitle }} "
            imageUrl="{{ $imageUrl }}" />
    @else
        <div class="missing-preview">
            Cannot create preview because some information is missing.
        </div>
    @endif
@elseif ($get('link') === AdvertisementLink::EXTERNAL->value)
    @if ($image !== null)
        <x-external-status-ad-preview imageUrl="{{ $imageUrl }}" />
    @else
        <div class="missing-preview">
            Cannot create preview because some information is missing.
        </div>
    @endif
@else
    <div class="missing-preview">
        Cannot create preview. Link type is not selected.
    </div>
@endif
