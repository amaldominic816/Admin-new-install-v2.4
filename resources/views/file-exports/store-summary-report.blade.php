<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('store_summary_reports') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
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
                    {{ translate('new_registered_store')  }}- {{ $data['new_stores'] ??translate('N/A') }}
                    <br>
                    {{ translate('total_orders')  }}- {{ $data['orders'] ??translate('N/A') }}
                    <br>
                    {{ translate('total_order_amount')  }}- {{ $data['total_order_amount'] ??translate('N/A') }}
                    <br>
                    {{ translate('completed_orders')  }}- {{ $data['total_delivered'] ??translate('N/A') }}
                    <br>
                    {{ translate('incomplete_orders')  }}- {{ $data['total_ongoing'] ??translate('N/A') }}
                    <br>
                    {{ translate('canceled_orders')  }}- {{ $data['total_canceled'] ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ translate('Payment_Statistics') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('cash_payments')  }} - {{ $data['cash_payments'] ??translate('N/A') }}
                    <br>
                    {{ translate('digital_payments')  }} - {{ $data['digital_payments'] ??translate('N/A') }}
                    <br>
                    {{ translate('wallet_payments')  }} - {{ $data['wallet_payments'] ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{translate('store_name')}}</th>
            <th>{{translate('Total Order')}}</th>
            <th>{{translate('Total Delivered Order')}}</th>
            <th>{{translate('Total Amount')}}</th>
            <th>{{translate('Completion Rate')}}</th>
            <th>{{translate('Ongoing Rate')}}</th>
            <th>{{translate('Cancelation Rate')}}</th>
            <th>{{translate('Total_Refund_requests')}}</th>
            <th>{{translate('Pending_Refund_requests')}}</th>
        </thead>
        <tbody>
        @foreach($data['stores'] as $key => $store)
        @php($delivered = $store->orders->where('order_status', 'delivered')->count())
        @php($canceled = $store->orders->where('order_status', 'canceled')->count())
        @php($refunded = $store->orders->where('order_status', 'refunded')->count())
        @php($refund_requested = $store->orders->whereNotNull('refund_requested')->count())
        <tr>
            <td>{{$key+1}}</td>
            <td>
                {{  $store->name  }}
            </td>
            <td>
                {{ $store->orders->count() }}
            </td>
            <td>
                {{ $delivered }}
            </td>
            <td>
                {{\App\CentralLogics\Helpers::number_format_short($store->orders->where('order_status','delivered')->sum('order_amount'))}}
            </td>
            <td>
                {{ ($store->orders->count() > 0 && $delivered > 0)? number_format((100*$delivered)/$store->orders->count(), config('round_up_to_digit')): 0 }}%
            </td>
            <td>
                {{ ($store->orders->count() > 0 && $delivered > 0)? number_format((100*($store->orders->count()-($delivered+$canceled)))/$store->orders->count(), config('round_up_to_digit')): 0 }}%
            </td>
            <td>
                {{ ($store->orders->count() > 0 && $canceled > 0)? number_format((100*$canceled)/$store->orders->count(), config('round_up_to_digit')): 0 }}%
            </td>
            <td>
                {{ $refunded }}
            </td>
            <td>
                {{ $refund_requested }}
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
