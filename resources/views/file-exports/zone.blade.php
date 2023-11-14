
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Zone_List')}}
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
            <th>{{ translate('Zone_Name') }}</th>
            <th>{{ translate('Zone_ID') }}</th>
            <th>{{ translate('Total_Stores') }}</th>
            <th>{{ translate('Total_Deliverymen') }}</th>
            <th>{{ translate('Digital_Payment') }}</th>
            <th>{{ translate('Cash_On_Delivery') }}</th>
            <th>{{ translate('Status') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $addon)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $addon->name }}</td>
        <td>{{ $addon->id }}</td>
        <td>
            {{ $addon->stores_count }}
        </td>
        <td>

            {{ $addon->deliverymen_count }}
        </td>
        <td>{{ $addon?->digital_payment == 1 ? translate('Yes') : translate('No') }}</td>
        <td>{{ $addon?->cash_on_delivery == 1 ? translate('Yes') : translate('No') }}</td>
        <td>{{ $addon?->status == 1 ? translate('Active') : translate('Inactive') }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
