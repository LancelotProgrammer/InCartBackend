<style>
    .missing-preview {
        border: 1px solid #ffeeba;
        background-color: #fff3cd;
        color: #856404;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
        font-weight: bold;
        max-width: 400px;
        margin: 20px auto;
        font-family: Arial, sans-serif;
    }
</style>

<div
    class="missing-preview flex flex-col items-center justify-center text-center p-6 border-2 border-dashed rounded-lg">
    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.516 
            11.614c.75 1.34-.213 3.01-1.742 3.01H3.483c-1.53 
            0-2.492-1.67-1.742-3.01L8.257 3.1zM11 13a1 
            1 0 10-2 0 1 1 0 002 0zm-1-2a.75.75 0 
            01-.75-.75V7a.75.75 0 011.5 0v3.25A.75.75 
            0 0110 11z" clip-rule="evenodd" />
    </svg>
    <p class="font-semibold">Preview not available</p>
    <p class="text-sm">{{ $message }}</p>
</div>