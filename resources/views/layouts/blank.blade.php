<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Process</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Prevents scrollbars in iframe */
        }

        /* Iframe responsive adjustments */
        @media screen {
            body {
                min-height: 100vh;
                min-height: 100dvh; /* Dynamic viewport height for mobile */
            }
        }

        /* Make content fit any iframe size */
        .iframe-responsive {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
            min-height: 200px; /* Minimum usable height */
        }

        /* Responsive text scaling */
        @media (max-width: 480px) {
            .iframe-responsive {
                padding: 5px;
            }
        }

        @media (max-height: 300px) {
            .iframe-responsive {
                padding: 5px;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="iframe-responsive">
        @yield('content')
    </div>
</body>
</html>
