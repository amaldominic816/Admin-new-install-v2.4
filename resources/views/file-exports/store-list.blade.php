<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('Store_List') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>




        <tr>

            <th>{{ translate('Total_Store') }} - {{ $data['data']->count() ?? translate('N/A') }} </th>
            <th></th>
            <th></th>
            <th> {{ translate('Active_Store') }} - {{ $data['data']->where('status',1)->count() ?? translate('N/A') }} </th>
            <th></th>
            <th></th>
            <th> {{ translate('Inactive_Store') }} - {{ $data['data']->where('status',0)->count() ?? translate('N/A') }} </th>
            <th></th>
            <th></th>
            <th> {{ translate('Newly_Joined') }} - {{ $data['data']->where('created_at', '>=', now()->subDays(30)->toDateTimeString())->count() ?? translate('N/A') }} </th>
            <th></th>

        </tr>
            <tr>
                <th>{{ translate('Filter_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('zone' )}} - {{ $data['zone']??translate('all') }}

                    <br>
                    {{ translate('Module' )}} - {{ $data['module']??translate('all') }}

                    <br>
                    {{ translate('Search_Bar_Content')  }}- {{ $data['search'] ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{translate('Store_ID')}}</th>
            <th>{{ translate('Store_Logo') }}</th>
            <th>{{ translate('Store_Name') }}</th>
            <th>{{ translate('Ratings') }}</th>
            <th>  {{ translate('Owner_Information') }}</th>
            <th>   {{ translate('Address') }}</th>
            <th> {{ translate('Total_Items') }}</th>
            <th> {{ translate('Total_Orders') }}</th>
            <th>{{ translate('Featured_?') }}</th>
            <th>{{ translate('Status') }}</th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $store)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{  $store['id']  }}</td>
            <td>&nbsp;</td>
            <td>{{  $store['name']  }}</td>
            <td>
                @php($store_reviews = \App\CentralLogics\StoreLogic::calculate_store_rating($store['rating']))
                {{ number_format($store_reviews['rating'], 1)}}
            </td>
            <td> {{ $store->vendor->f_name .' '  .$store->vendor->l_name   }}
                        <br>
                    {{ $store->vendor->phone  }}
            </td>
            <td> {{ $store->address }} </td>
            <td> {{ $store->items_count }} </td>
            <td>
                {{ $store->orders()->StoreOrder()->count() }}
            </td>
            <td>
                {{ $store->featured == 1 ? translate('Yes') : translate('No') }}
            </td>
            <td>
                {{ $store->status == 1 ? translate('Active') : translate('Inactive') }}
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
