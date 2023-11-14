<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('store_order_reports') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('zone' )}} - {{ $data['zone']??translate('all') }}
                    <br>
                    {{ translate('store' )}} - {{ $data['store']??translate('all') }}
                    @if ($data['from'])
                    <br>
                    {{ translate('from' )}} - {{ $data['from']?Carbon\Carbon::parse($data['from'])->format('d M Y'):'' }}
                    @endif
                    @if ($data['to'])
                    <br>
                    {{ translate('to' )}} - {{ $data['to']?Carbon\Carbon::parse($data['to'])->format('d M Y'):'' }}
                    @endif
                    <br>
                    {{ translate('filter')  }}- {{  translate($data['filter']) }}
                    <br>
                    {{ translate('Search_Bar_Content')  }}- {{ $data['search'] ??translate('N/A') }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ translate('Analytics') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('total_orders')  }}- {{ $data['total_orders'] }}
                    <br>
                    {{ translate('total_order_amount')  }}- {{ $data['total_order_amount'] }}
                    <br>
                    {{ translate('canceled_order')  }}- {{ $data['total_canceled_count'] }}
                    <br>
                    {{ translate('completed_orders')  }}- {{ $data['total_delivered_count'] }}
                    <br>
                    {{ translate('incomplete_orders')  }}- {{ $data['total_ongoing_count'] }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('messages.order_id') }}</th>
            <th>{{ translate('messages.order_date') }}</th>
            <th>{{ translate('messages.customer_name') }}</th>
            <th>{{ translate('messages.store_name') }}</th>
            <th>{{ translate('messages.total_amount') }}</th>
            <th>{{ translate('messages.payment_status') }}</th>
            <th>{{ translate('messages.discounted_amount') }}</th>
            <th>{{ translate('messages.tax') }}</th>
            <th>{{ translate('messages.delivery_charge') }}</th>
        </thead>
        <tbody>
            @foreach($data['orders'] as $key => $order)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $order->id }}</td>
                <td><div>
                    {{ date('d M Y', strtotime($order['created_at'])) }}
                </div>
                <br>
                <div>
                    {{ date(config('timeformat'), strtotime($order['created_at'])) }}
                </div></td>
                <td>
                    @if ($order->customer)
                        {{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}
                    @else
                        {{ translate('not_found') }}
                    @endif
                </td>
                <td>
                    @if($order->store)
                        {{$order->store->name}}
                    @else
                        {{ translate('messages.not_found') }}
                    @endif
                </td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{ translate($order->payment_status) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['delivery_charge']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
