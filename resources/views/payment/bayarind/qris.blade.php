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
    <img src="{{$qrisUrl}}" style="
        width: 100%;
        max-width: min(300px, 80vw, 80vh);
        height: auto;
        aspect-ratio: 1;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        object-fit: contain;
    "/>
</div>
@endsection
