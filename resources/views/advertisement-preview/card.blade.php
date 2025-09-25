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
        max-width: 400px;
        margin: 20px auto;
        background: #f5f5f5;
        padding: 20px;
        border-radius: 15px;
    }

    .card-ad-preview {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 15px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .card-image {
        flex-shrink: 0;
    }

    .card-image img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
    }

    .card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 10px;
    }

    .card-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
        margin: 0;
    }
    
    .card-button {
        background: #007bff;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        text-align: center;
        transition: background 0.3s ease;
        align-self: flex-start;
    }

    .card-button:hover {
        background: #0056b3;
    }
</style>

@php
    use Illuminate\Support\Facades\App;

    $image = $get('file');
    if ($image !== null) {
        $imageUrl = $image->temporaryUrl();
    }
    $title = $get('title');
    $description = $get('description');

    $locale = App::getLocale();
@endphp

@if ($image !== null && $title !== null && $description !== null)
    <div class="preview-container">
        <div class="card-ad-preview">
            <div class="card-image">
                <img src="{{ $imageUrl }}" alt="عرض خاص">
            </div>
            <div class="card-content">
                <div class="card-title">{{ $title[$locale] ?? $title['en'] ?? '' }}</div>
                <div class="card-button">Buy Now</div>
            </div>
        </div>
    </div>
@else
    <div class="missing-preview">
        Cannot create preview because some information is missing.
    </div>
@endif