<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('messages.customer_order_list') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('customer_information') }} -</th>
                <th></th>
                <th></th>
                <th> 
                    {{ translate('customer_id' )}} : {{ translate($data['customer_id']) }}
                    <br>
                    {{ translate('name' )}} : {{ $data['customer_name'] }}
                    <br>
                    {{ translate('phone' )}} : {{ $data['customer_phone'] }}
                    <br>
                    {{ translate('email' )}} : {{ $data['customer_email'] }}
                    <br>
                    {{ translate('total_orders' )}} : {{ $data['orders']->count() }}

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ translate('messages.sl') }}</th>
                <th>{{ translate('messages.order_id') }}</th>
                <th>{{ translate('messages.store_name') }}</th>
                <th>{{ translate('messages.item_price') }}</th>
                <th>{{ translate('messages.item_discount') }}</th>
                <th>{{ translate('messages.coupon_discount') }}</th>
                <th>{{ translate('messages.discounted_amount') }}</th>
                <th>{{ translate('messages.tax') }}</th>
                <th>{{ translate('messages.total_amount') }}</th>
                <th>{{ translate('messages.payment_status') }}</th>
                <th>{{ translate('messages.order_status') }}</th>
                <th>{{ translate('messages.order_type') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['orders'] as $key => $order)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $order->id }}</td>
                <td>
                    @if($order->store)
                        {{$order->store->name}}
                    @else
                        {{ translate('messages.not_found') }}
                    @endif
                </td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']-$order['dm_tips']-$order['total_tax_amount']-$order['delivery_charge']+$order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order->details->sum('discount_on_item')) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{ translate($order->payment_status) }}</td>
                <td>{{ translate($order->order_status) }}</td>
                <td>{{ translate($order->order_type) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
