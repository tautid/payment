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
        padding: 30px;
        background: white;
        border-radius: 7px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    ">
        <strong>
            <h1>{{$vaNumber}}</h1>
        </strong>
    </div>
</div>
@endsection
