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

    .preview-container {
        direction: rtl;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 300px;
        margin: 20px auto;
        background: #f5f5f5;
        padding: 20px;
        border-radius: 15px;
    }

    .offer-ad-preview {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 15px;
        position: relative;
        text-align: center;
    }

    .discount-badge {
        position: absolute;
        top: 0px;
        left: 0px;
        background: #ff4444;
        color: white;
        font-size: 18px;
        font-weight: bold;
        padding: 6px 24px;
        border-radius: 15px;
        z-index: 2;
    }

    .image-container {
        margin: 10px auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .center-image {
        width: 250px;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
    }

    .offer-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding: 0 10px;
    }

    .add-button {
        background: #4CAF50;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .add-button:hover {
        background: #388E3C;
    }

    .price-section {
        text-align: left;
    }

    .old-price {
        font-size: 14px;
        color: #999;
        text-decoration: line-through;
        margin-bottom: 2px;
    }

    .new-price {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
</style>

@php
    use App\Models\Product;
    use App\Models\BranchProduct;

    $product = Product::where('id', '=', $get('product_id'))->first();
    $branchProduct = BranchProduct::where('product_id', '=', $get('product_id'))
        ->where('branch_id', '=', $get('branch_id'))
        ->first();

    if ($product !== null && $branchProduct !== null) {
        $productImageUrl = $product->files->first()?->url;
        $productTitle = $product->title;
        $price = $branchProduct->price;
        $discount = $branchProduct->discount;
        $hasDiscount = $discount > 0;
        $discountPrice = $hasDiscount ? $price - ($price * ($discount / 100)) : $price;
    }
@endphp

@if ($product !== null && $branchProduct !== null)
    <div class="preview-container">
        <div class="offer-ad-preview">
            @if($hasDiscount)
                <div class="discount-badge">% {{ $discount }} -</div>
            @endif

            <div class="image-container">
                <img src="{{ $productImageUrl }}" alt="{{ $productTitle }}" class="center-image">
            </div>

            <div class="offer-footer">
                <div class="add-button">+</div>
                <div class="price-section">
                    @if($hasDiscount)
                        <div class="old-price">{{ number_format($price, 2) }}</div>
                        <div class="new-price">{{ number_format($discountPrice, 2) }}</div>
                    @else
                        <div class="new-price">{{ number_format($price, 2) }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    <div class="missing-preview">
        Cannot create preview because some information is missing.
    </div>
@endif