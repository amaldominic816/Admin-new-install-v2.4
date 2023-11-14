<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('delivery_man_list') }}</h1></div>
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
                    {{ translate('total_delivery_man')  }}- {{ $data['delivery_men']->count() }}
                    <br>
                    {{ translate('active_delivery_man')  }}- {{ $data['delivery_men']->where('status',1)->count()}}
                    <br>
                    {{ translate('inactive_delivery_man')  }}- {{ $data['delivery_men']->where('status',0)->count() }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{translate('image')}}</th>
            <th>{{ translate('first_name') }}</th>
            <th>{{ translate('last_name') }}</th>
            <th>{{ translate('phone') }}</th>
            <th>{{ translate('email') }}</th>
            <th>{{ translate('delivery_man_type') }}</th>
            <th>{{ translate('total_completed') }}</th>
            <th>{{ translate('total_running_orders') }}</th>
            <th>{{ translate('status') }}</th>
            <th>{{ translate('zone') }}</th>
            <th>{{ translate('vehicle_type') }}</th>
            <th>{{ translate('identity_type') }}</th>
            <th>{{ translate('identity_number') }}</th>
        </thead>
        <tbody>
        @foreach($data['delivery_men'] as $key => $item)
        <tr>
            <td>{{$key+1}}</td>
            <td></td>
            <td>{{  $item['f_name']  }}</td>
            <td>{{  $item['l_name']  }}</td>
            <td>{{  $item['phone']  }}</td>
            <td>{{  $item['email']  }}</td>
            <td>{{ $item->earning?translate('messages.freelancer'):translate('messages.salary_based') }}</td>
            <td>{{ $item['order_count'] }}</td>
            <td>{{ $item['current_orders'] }}</td>
            <td>{{ $item->active?translate('messages.online'):translate('messages.offline') }}</td>
            <td>{{ $item->zone?$item->zone->name:'' }}</td>
            <td>{{ $item->vehicle?$item->vehicle->type:'' }}</td>
            <td>{{ translate($item->identity_type) }}</td>
            <td>{{ $item->identity_number }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
