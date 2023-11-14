
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Store_Order_Transactions')}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ translate('Filter_Criteria') }}</th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Content')  }}: {{ $data['search'] ?? translate('N/A') }}
                </th>
                <th> </th>
                </tr>


        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Order_ID') }}</th>
            <th>{{ translate('Order_Time') }}</th>
            <th>{{ translate('Total_order_amount') }}</th>
            <th>{{ translate('Store_Earnings') }}</th>
            <th>{{ translate('Admin_Earnings') }}</th>
            <th>{{ translate('Delivery_Fee') }}</th>
            <th>{{ translate('Vat/Tax') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $tr)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $tr->order_id}}</td>
        <td>{{ $tr->created_at->format('Y-m-d '.config('timeformat')) ??  translate('N/A') }}</td>

        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->order_amount) }}
        </td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->store_amount - $tr->tax) }}
        </td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->admin_commission) }}
        </td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->delivery_charge) }}
        </td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->tax) }}
        </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
