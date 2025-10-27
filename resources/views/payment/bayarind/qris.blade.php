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
        <img src="{{$qrisUrl}}" width="350" height="350" style="border: 1px solid #e0e0e0; border-radius: 8px;"/>
    </div>
</div>
@endsection
