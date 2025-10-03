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
    if ($product !== null && $branchProduct !== null) {
        $productImageUrl = $product->files->first()?->url;
        $productTitle = $product->title;
        $price = $branchProduct->price;
        $discount = $branchProduct->discount;
        $hasDiscount = $discount > 0;
        $discountPrice = $hasDiscount ? $price - ($price * ($discount / 100)) : $price;
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
            price="{{ $price }}" hasDiscount="{{ $hasDiscount }}" />
    @else
        <x-missing-preview message="Cannot create preview because some information is missing." />
    @endif
@elseif ($get('link') === AdvertisementLink::CATEGORY->value)
    @if ($category !== null && $image !== null)
        <x-category-status-ad-preview categoryImageUrl="{{ $categoryImageUrl }}" categoryTitle="{{ $categoryTitle }} "
            imageUrl="{{ $imageUrl }}" />
    @else
        <x-missing-preview message="Cannot create preview because some information is missing." />
    @endif
@elseif ($get('link') === AdvertisementLink::EXTERNAL->value)
    @if ($image !== null)
        <x-external-status-ad-preview imageUrl="{{ $imageUrl }}" />
    @else
        <x-missing-preview message="Cannot create preview because some information is missing." />
    @endif
@else
    <x-missing-preview message="Cannot create preview. Link type is not selected." />
@endif
