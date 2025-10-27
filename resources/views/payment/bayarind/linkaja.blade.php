@extends('taut-payment::layouts.blank')

@section('content')
    <form id='redirectForm' method='POST' action='{{$redirectUrl}}'>
        <input type='hidden' name='message' value="{{$redirectData}}">
    </form>
    <script>document.getElementById('redirectForm').submit();</script>
@endsection
