<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('item_report') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('module' )}} - {{ $data['module']?translate($data['module']):translate('all') }}
                    <br>
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
            <th>{{ translate('sl') }}</th>
            <th>{{translate('messages.item_image')}}</th>
            <th>{{translate('messages.item_name')}}</th>
            <th>{{translate('messages.module')}}</th>
            <th>{{translate('messages.store_name')}}</th>
            <th>{{translate('messages.total_order_count')}}</th>
            <th>{{translate('messages.unit_price')}}</th>
            <th>{{translate('messages.total_amount_sold')}}</th>
            <th>{{translate('messages.total_discount_given')}}</th>
            <th>{{translate('messages.average_sale_value')}}</th>
            <th>{{translate('messages.total_ratings_given')}}</th>
            <th>{{translate('messages.average_ratings')}}</th>
        </thead>
        <tbody>
        @foreach($data['items'] as $key => $item)
            <tr>
                <td>{{ $key+1}}</td>
                <td></td>
                <td>{{$item['name']}}</td>
                <td>
                    {{ $item->module->module_name }}
                </td>
                <td>
                    @if($item->store)
                    {{ $item->store->name }}
                    @else
                    {{translate('messages.store_deleted')}}
                    @endif
                </td>
                <td>
                    {{$item->orders_count}}
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->price) }}
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->orders_sum_price) }}
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->orders_sum_discount_on_item) }}
                </td>
                <td>
                    {{ $item->orders_count>0? \App\CentralLogics\Helpers::format_currency(($item->orders_sum_price-$item->orders_sum_discount_on_item)/$item->orders_count):0 }}
                </td>
                <td>{{ $item->rating_count }}</td>
                <td>{{ round($item->avg_rating,1) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
