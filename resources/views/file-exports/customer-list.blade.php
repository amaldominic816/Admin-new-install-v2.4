<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('customer_list') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Customer_Analytics') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Total_Customer')  }}: {{ $data['customers']->count() }}
                    <br>
                    {{ translate('Active_Customer')  }}: {{ $data['customers']->where('status',1)->count() }}
                    <br>
                    {{ translate('Inactive_Customer')  }}: {{ $data['customers']->where('status',0)->count() }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Conten')  }}: : {{ $data['search'] ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('first_name') }}</th>
            <th>{{ translate('last_name') }}</th>
            <th>{{ translate('phone') }}</th>
            <th>{{ translate('email') }}</th>
            <th>{{ translate('saved_address') }}</th>
            <th>{{ translate('total_orders') }}</th>
            <th>{{ translate('total_wallet_amount') }} </th>
            <th>{{ translate('total_loyality_points') }} </th>
            <th>{{ translate('status') }} </th>
        </thead>
        <tbody>
        @foreach($data['customers'] as $key => $customer)
            <tr>
        <td>{{ $key+1}}</td>
        <td>{{ $customer['f_name'] }}</td>
        <td>{{ $customer['l_name'] }}</td>
        <td>{{ $customer['phone'] }}</td>
        <td>{{ $customer['email'] }}</td>
        <td>
            @foreach($customer->addresses as $address)
            <br>
            {{$address['address']}}
            @endforeach
        </td>
        <td>{{ $customer['order_count'] }}</td>
        <td>{{ $customer['wallet_balance'] }}</td>
        <td>{{ $customer['loyalty_point'] }}</td>
        <td>{{ $customer->status ? translate('messages.Active') : translate('messages.Inactive') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
