<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('limited_stock_report') }}</h1></div>
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
            <th>{{translate('item_image')}}</th>
            <th>{{translate('item_name')}}</th>
            <th>{{ translate('current_stock') }}</th>
            <th>{{ translate('category_name') }}</th>
            <th>{{translate('unit')}}</th>
            <th>{{translate('variation')}}</th>
            <th>{{translate('price')}}</th>
            <th>{{translate('store_name')}}</th>
            <th>{{translate('module_name')}}</th>
        </thead>
        <tbody>
        @foreach($data['items'] as $key => $item)
            <tr>
                <td>{{ $key+1}}</td>
                <td></td>
                <td>{{$item['name']}}</td>
                <td>
                    @if ($item->module->module_type != 'food')
                    {{ $item->stock }}
                    @endif
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::get_category_name($item->category_ids) }}
                </td>
                <td>{{ $item?->unit?->unit ?? translate('N/A') }}</td>
                <td>
                    @if ($item->module->module_type == 'food')
                    {{ \App\CentralLogics\Helpers::get_food_variations($item->food_variations) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_food_variations($item->food_variations) }}
                    @else
                    {{ \App\CentralLogics\Helpers::get_attributes($item->choice_options) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_attributes($item->choice_options) }}
                    @endif
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->price) }}
                </td>
                <td>
                    @if($item->store)
                    {{ $item->store->name }}
                    @else
                    {{translate('messages.store_deleted')}}
                    @endif
                </td>
                <td>
                    {{ $item->module->module_name }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
