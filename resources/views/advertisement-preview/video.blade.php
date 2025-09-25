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
        max-width: 500px;
        margin: 20px auto;
        background: #f5f5f5;
        padding: 20px;
        border-radius: 10px;
    }

    .video-ad-preview {
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .video-header {
        padding: 15px;
        background: #333;
        color: white;
        display: flex;
        justify-content: space-between;
    }

    .video-player {
        position: relative;
    }

    .video-placeholder {
        height: 250px;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .video-placeholder img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .play-button {
        width: 60px;
        height: 60px;
        background: #ff0000;
        border-radius: 50%;
        cursor: pointer;
        position: absolute;
        transition: transform 0.2s ease;
    }

    .play-button::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 55%;
        transform: translate(-50%, -50%);
        border-left: 15px solid white;
        border-top: 10px solid transparent;
        border-bottom: 10px solid transparent;
    }

    .play-button:hover {
        transform: scale(1.1);
        background: #cc0000;
    }

    .video-info {
        padding: 15px;
    }

    .video-stats {
        display: flex;
        gap: 15px;
        margin-top: 10px;
        font-size: 14px;
        color: #666;
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
        <div class="video-ad-preview">
            <div class="video-player">
                <div class="video-placeholder">
                    <img src="{{ $imageUrl }}" alt="عرض خاص">
                    <div class="play-button"></div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="missing-preview">
        Cannot create preview because some information is missing.
    </div>
@endif