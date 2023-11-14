
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Push_Notification_List')}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Content')  }}: {{ $data['search'] ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>


        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Notification_Title') }}</th>
            <th>{{ translate('Created_At') }}</th>
            <th>{{ translate('Description') }}</th>
            <th>{{ translate('Image') }}</th>
            <th>{{ translate('Zone') }}</th>
            <th>{{ translate('Targeted Users') }}</th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $coupon)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $coupon->title }}</td>
        <td>{{ \Carbon\Carbon::parse($coupon->created_at)->format('d M Y') }}</td>
        <td>{{ $coupon->description }}</td>
            <td></td>
        {{-- <td>{{ $coupon->image ?? translate('N/A') }}</td> --}}
        <td>{{ $coupon?->zone?->name ??  translate('All') }}</td>

        <td>{{ translate($coupon->tergat) }}</td>


            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
