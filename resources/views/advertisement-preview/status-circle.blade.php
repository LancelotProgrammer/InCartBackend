<style>
    .preview-container {
        direction: rtl;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 400px;
        margin: 20px auto;
        background: #f5f5f5;
        padding: 20px;
        border-radius: 15px;
    }

    .simple-status-preview {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .status-circle {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        border: 3px solid #007b00;
        padding: 3px;
        overflow: hidden;
    }

    .status-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
</style>

@php
    $image = $get('file');
    if ($image !== null) {
        $imageUrl = $image->temporaryUrl();
    }
@endphp

@if ($image !== null)
    <div class="preview-container">
        <div class="simple-status-preview">
            <div class="status-circle">
                <img src="{{ $imageUrl }}" alt="عرض خاص">
            </div>
        </div>
    </div>
@else
    <div class="missing-preview">
        Cannot create preview because some information is missing.
    </div>
@endif