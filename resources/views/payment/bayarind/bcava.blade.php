@extends('taut-payment::layouts.blank')

@section('content')
<div id="vaNumber" onclick="copyVANumber()" style="
    border-radius: 4px;
    padding: 15px 20px;
    text-align: center;
    cursor: pointer;
    user-select: none;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    max-width: 100%;
    width: auto;
">
    <h3 style="
        margin: 0;
        color: #333;
        font-size: clamp(14px, 4vw, 18px);
        font-weight: bold;
        word-break: break-all;
        line-height: 1.3;
    ">{{$vaNumber}}</h3>
</div>

<script>
function copyVANumber() {
    const text = "{{$vaNumber}}";

    // Method 1: Modern clipboard API (preferred)
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            // Optional: Show feedback
            showCopyFeedback();
        }).catch(function(err) {
            // Fallback if clipboard API fails
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Method 2: Fallback for older browsers
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const input = document.createElement('textarea');
    input.value = text;
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.focus();
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);

    // Optional: Show feedback
    showCopyFeedback();
}

function showCopyFeedback() {
    // Create tooltip in the center
    const tooltip = document.createElement('div');
    tooltip.textContent = 'Copied!';
    tooltip.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        z-index: 9999;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;

    document.body.appendChild(tooltip);

    // Show tooltip
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);

    // Remove tooltip
    setTimeout(() => {
        tooltip.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(tooltip);
        }, 300);
    }, 1500);
}

// Alternative: If you want to trigger a custom copy event
function triggerCopyEvent() {
    const text = "{{$vaNumber}}";
    const copyEvent = new CustomEvent('copy', {
        detail: [{ text: text }]
    });
    document.dispatchEvent(copyEvent);
}

// Your existing copy event listener (if you want to use it)
document.addEventListener("copy", function(e) {
    if (e.detail && e.detail[0] && e.detail[0].text) {
        let text = e.detail[0].text;

        const input = document.createElement('textarea');
        input.value = text;
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.focus();
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
    }
});
</script>
@endsection
