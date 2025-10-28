@extends('taut-payment::layouts.blank')

@section('content')
<div style="
    text-align: center;
    padding: 15px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-width: 100%;
    width: auto;
">
    <h3 style="margin: 0 0 15px 0; color: #FF5722; font-size: clamp(14px, 4vw, 18px);">ShopeePay</h3>

    @if(!empty($qrData))
        <!-- Generate QR code using API with responsive sizing -->
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ urlencode($qrData) }}"
             style="
                width: 100%;
                max-width: min(300px, 80vw, 80vh);
                height: auto;
                aspect-ratio: 1;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                object-fit: contain;
             "
             alt="ShopeePay QR Code"/>
    @else
        <div style="
            width: 100%;
            max-width: min(300px, 80vw, 80vh);
            aspect-ratio: 1;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            margin: 0 auto;
        ">
            <p style="color: #666; font-size: clamp(12px, 3vw, 14px); margin: 0;">QR Code data not available</p>
        </div>
    @endif

    <p style="margin: 15px 0 0 0; color: #666; font-size: clamp(11px, 2.5vw, 13px);">
        Scan with ShopeePay app to pay
    </p>
</div>
@endsection
