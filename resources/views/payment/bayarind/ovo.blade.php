@extends('taut-payment::layouts.blank')

@section('content')
    <div style="text-align: center; padding: 20px; color: #333;">
        <p>Redirecting to OVO payment...</p>
        <div style="margin: 20px auto; width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        // Check if we're in an iframe
        function isInIframe() {
            try {
                return window.self !== window.top;
            } catch (e) {
                return true;
            }
        }

        // Redirect to parent window if in iframe, otherwise normal redirect
        function redirectToParent() {
            const redirectUrl = '{{ $redirectUrl }}';

            if (isInIframe()) {
                // We're in an iframe - redirect parent window
                window.parent.location.href = redirectUrl;
            } else {
                // We're not in an iframe - normal redirect
                window.location.href = redirectUrl;
            }
        }

        // Auto-redirect after a short delay to show loading message
        setTimeout(redirectToParent, 1500);
    </script>
@endsection
