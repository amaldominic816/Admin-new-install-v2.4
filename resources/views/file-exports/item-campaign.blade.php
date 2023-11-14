<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{ Config::get('module.current_module_type')== 'food' ?  translate('Food_Campaign_List') : translate('Item_Campaign_List') }}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ translate('Filter_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Module')  }}: {{ $module_name }}
                    <br>
                    {{ translate('Search_Bar_Content')  }}: {{ $search ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>


        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Item_Name') }}</th>
            <th>{{ translate('Description') }}</th>
            <th>{{ translate('Category_Name') }}</th>
            <th>{{ translate('Sub_Category_Name') }}</th>
            <th>{{ translate('Item_Unit') }}</th>
            <th>{{ translate('Price') }}</th>
            <th>{{ translate('Available_Variations') }} </th>
            <th>{{ translate('Discount') }} </th>
            <th>{{ translate('Discount_Type') }} </th>
            @if (Config::get('module.current_module_type') != 'food')
            <th>{{ translate('Available_Stock') }} </th>
            @endif


            <th>{{ translate('Start_Date') }} </th>
            <th>{{ translate('End_Date') }} </th>
            <th>{{ translate('Daily_Start_Time') }} </th>
            <th>{{ translate('Daily_End_Time') }} </th>
            <th>{{ translate('Store_Name') }} </th>
        </thead>
        <tbody>
        @foreach($data as $key => $campaign)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $campaign->title }}</td>
        <td>{{ $campaign->description }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::get_category_name($campaign->category_ids) }}
        </td>
        <td>
        {{ \App\CentralLogics\Helpers::get_sub_category_name($campaign->category_ids) ?? translate('N/A')  }}
        </td>

        <td>{{ $campaign?->unit?->unit ?? translate('N/A') }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($campaign->price) }}
        </td>
        <td>
            @if (Config::get('module.current_module_type') == 'food')
            {{ \App\CentralLogics\Helpers::get_food_variations($campaign->food_variations) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_food_variations($campaign->food_variations) }}
            @else
            {{ \App\CentralLogics\Helpers::get_attributes($campaign->choice_options) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_attributes($campaign->choice_options) }}
            @endif
        </td>
        <td>{{ $campaign->discount }}</td>
        <td>{{ $campaign->discount_type }}</td>


        @if (Config::get('module.current_module_type') != 'food')
            <td>{{ $campaign->stock }}</td>
        @endif

        <td>{{ $campaign->start_date->format('d M Y') }}</td>
        <td>{{ $campaign->end_date->format('d M Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->start_time)->format("H:i A") }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->end_time)->format("H:i A") }}</td>
        <td>{{ $campaign?->store?->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
