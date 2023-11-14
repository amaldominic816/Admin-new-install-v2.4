
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Store_Order_List')}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ translate('Store_Details') }}</th>
                <th></th>
                <th>
                    {{ translate('Store_Name')  }}: {{ $data['store'] ?? translate('N/A') }}
                    <br>
                    {{ translate('Zone')  }}: {{ $data['zone'] ?? translate('N/A') }}
                    <br>
                    {{ translate('Total_Order')  }}: {{ $data['data']->count() ?? translate('N/A') }}
                </th>
                <th> </th>
            </tr>


            <tr>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Scheduled_Order')  }}: {{ $data['data']->where('scheduled', '1')->count() ?? translate('N/A') }}
                </th>
                <th>
                    {{ translate('Pending_Order')  }}: {{ $data['data']->where('order_status' ,'pending')->count() ?? translate('N/A') }}
                </th>
                <th>
                    {{ translate('Delivered_Order')  }}: {{ $data['data']->where('order_status' ,'delivered')->count() ?? translate('N/A') }}
                </th>
                <th>
                    {{ translate('Canceled_Order')  }}: {{ $data['data']->where('order_status' ,'canceled')->count() ?? translate('N/A') }}
                </th>
                <th>
                    {{ translate('Refunded_Order')  }}: {{ $data['data']->where('order_status' ,'refunded')->count() ?? translate('N/A') }}
                </th>
                <th> </th>
            </tr>


        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Order_ID') }}</th>
            <th>{{ translate('Order_Date') }}</th>
            <th>{{ translate('Customer_Name') }}</th>
            <th>{{ translate('Store_Name') }}</th>
            <th>{{ translate('Total_Items') }}</th>
            <th>{{ translate('Item_Price') }}</th>
            <th>{{ translate('Item_Discount') }}</th>
            <th>{{ translate('Coupon_Discount') }}</th>
            <th>{{ translate('Discounted_Amount') }}</th>
            <th>{{ translate('Vat/Tax') }}</th>
            <th>{{ translate('Total_Amount') }}</th>
            <th>{{ translate('Payment_Status') }}</th>
            <th>{{ translate('Order_Status') }}</th>
            <th>{{ translate('Order_Type') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $order)
            <tr>
                <td>{{ $loop->index+1}}</td>
                <td>{{ $order->id}}</td>
                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d '.config('timeformat')) ??  translate('N/A') }}</td>
                <td>{{  $order?->customer ?  $order?->customer?->f_name.' '.$order?->customer?->l_name  : translate('not_found')  }}</td>
                <td>{{ $order?->store?->name }}</td>
                <td>{{$order->details->count() }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']-$order['dm_tips']-$order['total_tax_amount']-$order['delivery_charge']+$order['coupon_discount_amount'] + $order['store_discount_amount']) }}
                </td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order->details->sum('discount_on_item')) }} </td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']) }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{translate($order->payment_status)}}</td>
                <td> {{ translate($order->order_status)}}</td>
                <td> {{ translate($order->order_type)}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
