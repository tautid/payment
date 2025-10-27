@extends('taut-payment::layouts.blank')

@section('content')
    <script>window.location.href = '{{ $redirectUrl }}'</script>;
@endsection
