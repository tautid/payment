@extends('taut-payment::layouts.blank')

@section('content')
<div style="
    width: 100%;
    height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background-color: #f8f9fa;
">
    <div style="
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    ">
        @if(!empty($qrData))
            <!-- Generate QR code using API -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data={{ urlencode($qrData) }}"
                 width="350"
                 height="350"
                 style="border: 1px solid #e0e0e0; border-radius: 8px;"
                 alt="ShopeePay QR Code"/>
        @else
            <div style="width: 350px; height: 350px; border: 1px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; background-color: #f5f5f5;">
                <p style="color: #666;">QR Code data not available</p>
            </div>
        @endif
    </div>
</div>
@endsection
