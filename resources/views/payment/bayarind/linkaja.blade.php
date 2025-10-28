@extends('taut-payment::layouts.blank')

@section('content')
    <div style="text-align: center; padding: 20px; color: #333;">
        <p>Redirecting to LinkAja payment...</p>
        <div style="margin: 20px auto; width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
    </div>

    <form id='redirectForm' method='POST' action='{{$redirectUrl}}' target='_parent'>
        <input type='hidden' name='message' value="{{$redirectData}}">
    </form>

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

        // Submit form to parent window if in iframe, otherwise normal submission
        function submitToParent() {
            const form = document.getElementById('redirectForm');

            if (isInIframe()) {
                form.target = '_parent';
            } else {
                form.target = '_self';
            }

            form.submit();
        }

        // a short delay to show loading message
        setTimeout(submitToParent, 1500);
    </script>
@endsection
