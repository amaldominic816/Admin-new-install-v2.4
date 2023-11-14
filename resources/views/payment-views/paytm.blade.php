@extends('payment-views.layouts.master')

@push('script')

@endpush

@section('content')
    <center><h1>Please do not refresh this page...</h1></center>
    <form method="post" action="<?php echo \Illuminate\Support\Facades\Config::get('paytm_config.PAYTM_TXN_URL') ?>" id="form">
        <table border="1">
            <tbody>
            @foreach($paramList as $name => $value)
                <input type="hidden" name="{{$name}}" value="{{$value}}">
            @endforeach
            <input type="hidden" name="CHECKSUMHASH" value="{{$checkSum}}">
            </tbody>
        </table>
    </form>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("form").submit();
        });
    </script>
@endsection
