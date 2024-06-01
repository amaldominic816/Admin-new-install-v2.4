<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>


    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            background-color: #FCFCFC;
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            padding: 0;
            margin: 0;
        }

        .invoice-wrapper {
            padding: 35px 20px 80px;
            width: 595px;
            margin: 0 auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 27px;
        }

        .logo {
            height: 20px;
            margin-bottom: 10px;
        }

        .logo img {
            height: 20px;
            width: 160px;
            object-fit: contain;
        }

        .invoice-header .title {
            font-size: 18px;
            margin: 0;
            margin-bottom: 8px;
        }

        .invoice-header .date {
            opacity: 0.8;
        }

        .invoice-header .right {
            text-align: right;
        }

        .invoice-body {
            border-radius: 12px;
            border: 1px solid #D7DAE0;
            background: #ffffff;
            flex-grow: 1;
        }

        .invoice-body-top {
            padding: 22px 15px;
            display: flex;
            column-gap: 30px;
            font-weight: 500;
            border-bottom: 1px solid #D7DAE0;
        }

        .invoice-body-top h6 {
            font-size: 16px;
            margin: 0;
            color: #1455AC;
        }

        .invoice-body-top .subtxt {
            margin-bottom: 4px;
            font-weight: 400;
            font-size: 9px;
            opacity: 0.9;
        }

        .ml-auto {
            margin-left: auto;
        }

        .invoice-body-bottom {
            padding: 9px 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table tr td,
        .table tr th {
            border-bottom: 1px solid #D6EBFF;
            padding: 13px;
            text-align: start;
            vertical-align: top;
        }

        .table tr th {
            border-top: 1px solid #D6EBFF;
            background: #F5FBFF;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: 500;
        }

        .table .name {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .text-right {
            text-align: right;
        }

        .info {
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-top: 8px;
        }

        .info li {
            list-style: none;
        }

        .mt-10px {
            margin-top: 10px;
        }

        .text-12 {
            font-size: 12px;
            font-weight: 700;
            color: #222222;
        }

        .border-0 {
            border: none !important;
        }

        .mr-100px {
            margin-right: 100px;
        }

        footer {
            border-top: 0.5px solid #EBEDF2;
            background: #F2F4F7;
            display: flex;
            justify-content: center;
            align-items: center;
            justify-content: center;
            column-gap: 60px;
            padding: 15px;
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            box-sizing: border-box
        }

        footer a {
            color: #000000;
            text-decoration: none;
        }
        .table-2{
            border: none;
            width: 100%;
        }
        .table-2 tr,
        .table-2 tr td {
            border: none;
        }
    </style>


</head>

<body>

<!-- Invoice -->
<div class="invoice-wrapper">
    <div class="invoice-header">
        <table class="table-2">
            <tbody>
            <tr>
                <td>
                    <div class="left">
                        <h4 class="title">{{ translate('Disbursement_Invoice') }}</h4>
                        <div class="date">{{ \App\CentralLogics\Helpers::time_date_format(date("Y-m-d h:i:s",time())) }}</div>
                    </div>
                </td>
                @php($logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first()->value)
                @php($address = \App\Models\BusinessSetting::where(['key' => 'address'])->first()->value)
                <td class="text-right">
                    <div class="right text-right">
                        <div class="logo">
                            <img style="width: 160px;object-fit: contain;object-position: right center" src="{{ asset('storage/app/public/business/' . $logo ?? '') }}" alt="logo">
                        </div>
                        <div>{{ $address }}</div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="invoice-body">
        <div class="invoice-body-top">
            <table class="table-2">
                <tbody>
                <tr>
                    <td>
                        <div>
                            <div class="subtxt">{{ translate('Disbursement_ID') }}</div>
                            <div>{{ $disbursement->id }}</div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="subtxt">{{ translate('created_at') }}</div>
                            <div>{{ \App\CentralLogics\Helpers::time_date_format($disbursement->created_at) }}</div>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="ml-auto text-right">
                            <div class="subtxt">{{ translate('total_amount') }}</div>
                            <div style="font-size: 16px;margin: 0;color: #1455AC;">{{\App\CentralLogics\Helpers::format_currency($disbursement['total_amount'])}}</div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="invoice-body-bottom" style="padding: 9px 12px;">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('Store_Info') }}</th>
                    <th>{{ translate('Payment_method') }}</th>
                    <th>
                        <div class="text-right"> {{ translate('amount') }}</div>
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($disbursements as $key => $disb)
                <tr>
                    <td>
                        <div class="mt-10px">
                            {{ $key+1 }}
                        </div>
                    </td>
                    <td>
                        <div class="name mt-10px">{{ $disb->store->name }}</div>
                    </td>
                    <td>
                        <div class="name">{{$disb->withdraw_method->method_name}}</div>

                        @forelse(json_decode($disb->withdraw_method->method_fields, true) as $key=> $item)
                            <div>
                                <span>{{  translate($key) }}</span>
                                <span>:</span>
                                <span class="name">{{$item}}</span>
                            </div>
                        @empty

                        @endforelse

                    </td>
                    <td>
                        <div class="mt-10px text-right">
                            {{\App\CentralLogics\Helpers::format_currency($disb['disbursement_amount'])}}
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td class="border-0"></td>
                    <td class="border-0"></td>
                    <td class="border-0">
                        <div class="text-right text-12 mr-100px">
                            {{ translate('total') }}
                        </div>
                    </td>
                    <td>
                        <div class="mt-10px text-right">
                            {{\App\CentralLogics\Helpers::format_currency($disbursement['total_amount'])}}
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <br>
        </div>
    </div>

{{--    <table class="table-2" style="margin:0">--}}
{{--        <tbody>--}}
{{--        <tr>--}}
{{--            <td style="border-top: 0.5px solid #EBEDF2;--}}
{{--                    background: #F2F4F7;--}}
{{--                    position: absolute;--}}
{{--                    bottom: 0;--}}
{{--                    left: 0;--}}
{{--                    width: 100%;--}}
{{--                    display: flex;--}}
{{--                    justify-content: center;--}}
{{--                    align-items: center;--}}
{{--                    column-gap: 60px;--}}
{{--                    padding: 15px;--}}
{{--                    box-sizing: border-box; text-align: center">--}}
{{--                <table class="table-2" style="margin:0">--}}
{{--                    <tbody>--}}
{{--                    <tr>--}}
{{--                        <td style="text-align:center">--}}
{{--                            <a style=" color: #000000; text-align:center;--}}
{{--                    text-decoration: none;" href="www.stackfood.inc">www.stackfood.inc</a>--}}
{{--                        </td>--}}
{{--                        <td style="text-align:center">--}}
{{--                            <a style=" color: #000000; text-align:center;--}}
{{--                    text-decoration: none;" href="tel:+91 00000 00000">+91 00000 00000</a>--}}
{{--                        </td>--}}
{{--                        <td style="text-align:center">--}}
{{--                            <a style=" color: #000000; text-align:center;--}}
{{--                    text-decoration: none;" href="mailto:hello@email.com">hello@email.com</a>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                    </tbody>--}}
{{--                </table>--}}
{{--            </td></tr>--}}
{{--        </tbody>--}}
{{--    </table>--}}
</div>
<!-- Invoice -->


</body>

</html>
