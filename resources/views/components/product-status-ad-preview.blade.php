<style>
    .preview-container {
        direction: rtl;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 400px;
        margin: 20px auto;
        background: #000;
        padding: 0;
        border-radius: 0;
        height: 700px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .status-viewer-preview {
        width: 100%;
        height: 100%;
        background: #000;
        position: relative;
        overflow: hidden;
    }

    .status-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), transparent);
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
    }

    .user-info {
        display: flex;
        align-items: center;
        color: white;
    }

    .avatar {
        margin-left: 10px;
    }

    .avatar img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #25D366;
    }

    .product-details .product {
        font-weight: bold;
        font-size: 16px;
    }

    .product-details .category {
        font-size: 16px;
    }

    .product-details .status-time {
        font-size: 12px;
        opacity: 0.8;
    }

    .status-content {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .status-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #000;
    }

    .status-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
        padding: 20px;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .price-section {
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
    }

    .discount-price {
        font-size: 20px;
        font-weight: bold;
    }

    .original-price {
        font-size: 16px;
        text-decoration: line-through;
        opacity: 0.7;
    }

    .status-add-button {
        background: #4CAF50;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .status-add-button:hover {
        background: #388E3C;
    }
</style>

<div class="preview-container">
    <div class="status-viewer-preview">
        <div class="status-header">
            <div class="user-info">
                <div class="avatar">
                    <img src="{!! $imageUrl !!}">
                </div>
                <div class="product-details">
                    <div class="product">{{ $productTile }}</div>
                    <div class="category">{{ $categoryTitle }}</div>
                </div>
            </div>
            <div class="status-time">منذ 30 دقيقة</div>
        </div>

        <div class="status-content">
            <img src="{{ $productImageUrl }}">
        </div>

        <div class="status-footer">
            <div class="price-section">
                @if($hasDiscount)
                    <div class="original-price">{{ number_format($price, 2) }}</div>
                    <div class="discount-price">{{ number_format($discountPrice, 2) }}</div>
                @else
                    <div class="discount-price">{{ number_format($price, 2) }}</div>
                @endif
            </div>
            <button class="status-add-button">+</button>
        </div>
    </div>
</div>