<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{ Config::get('module.current_module_type')== 'food' ?  translate('Food_List') : translate('Item_List') }}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ translate('Filter_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Store')  }}: {{ $data['store'] ??  translate('All') }}
                    <br>
                    {{ translate('Module')  }}: {{$data['module_name'] ??translate('N/A') }}
                    <br>
                    {{ translate('category')  }}: {{$data['category'] ??translate('N/A') }}
                    <br>
                    {{ translate('Search_Bar_Content')  }}: {{ $data['search'] ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>


        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Image') }}</th>
            <th>{{ translate('Item_Name') }}</th>
            <th>{{ translate('Description') }}</th>
            <th>{{ translate('Category_Name') }}</th>
            <th>{{ translate('Sub_Category_Name') }}</th>
            @if (Config::get('module.current_module_type') == 'food')
            <th>{{ translate('Food_Type') }}</th>
            @else
            <th>{{ translate('Available_Stock') }} </th>
            @endif
            <th>{{ translate('Price') }}</th>
            <th>{{ translate('Available_Variations') }} </th>


            @if (Config::get('module.current_module_type') == 'food')
            <th>{{ translate('Available_Addons') }} </th>
            @else
            <th>{{ translate('Item_Unit') }}</th>
            @endif
            <th>{{ translate('Discount') }} </th>
            <th>{{ translate('Discount_Type') }} </th>


            <th>{{ translate('Available_From') }} </th>
            <th>{{ translate('Available_Till') }} </th>
            <th>{{ translate('Store_Name') }} </th>
            <th>{{ translate('Tags') }} </th>
            <th>{{ translate('Status') }} </th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $item)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td> &nbsp;</td>
        <td>{{ $item->name }}</td>
        <td>{{ $item->description }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::get_category_name($item->category_ids) }}
        </td>
        <td>
        {{ \App\CentralLogics\Helpers::get_sub_category_name($item->category_ids) ?? translate('N/A')  }}
        </td>
        @if (Config::get('module.current_module_type') == 'food')
        <td> {{ $item->veg == 1? translate('Veg') : translate('Non_Veg')  }}</td>
        @else
        <td>{{ $item->stock }}</td>
        @endif
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($item->price) }}
        </td>
        <td>
            @if (Config::get('module.current_module_type') == 'food')
            {{ \App\CentralLogics\Helpers::get_food_variations($item->food_variations) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_food_variations($item->food_variations) }}
            @else
            {{ \App\CentralLogics\Helpers::get_attributes($item->choice_options) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_attributes($item->choice_options) }}
            @endif
        </td>


        <td>
            @if (Config::get('module.current_module_type') == 'food')
            {{ \App\CentralLogics\Helpers::get_addon_data($item->add_ons) == 0  ? translate('N/A'): \App\CentralLogics\Helpers::get_addon_data($item->add_ons) }}
            @else
            {{ $item?->unit?->unit ?? translate('N/A') }}
            @endif

        </td>
        <td>{{ $item->discount == 0 ? translate('N/A') : $item->discount}}</td>
        <td>{{ $item->discount_type }}</td>


        <td>{{ Config::get('module.current_module_type') != 'grocery'?\Carbon\Carbon::parse($item->available_time_starts)->format("H:i A") : translate('N/A') }}</td>
        <td>{{ Config::get('module.current_module_type') != 'grocery'?\Carbon\Carbon::parse($item->available_time_ends)->format("H:i A") : translate('N/A') }}</td>
        <td>{{ $item?->store?->name }}</td>

        @if ( isset($data['table']) && $data['table'] == 'TempProduct')
        <td>
            @php($tagids=json_decode($item?->tag_ids) ?? [])
            @php( $tags =\App\Models\Tag::whereIn('id',$tagids )->get('tag'))
            @forelse($tags as $c) {{$c->tag.','}} @empty {{  translate('N/A') }} @endforelse
        </td>
        <td> {{ $item->is_rejected == 1? translate('Rejected') : translate('Pending')  }}</td>

        @else

        <td>
                @forelse ($item->tags as $c) {{ $c->tag . ',' }} @empty {{  translate('N/A') }} @endforelse
        </td>
        <td> {{ $item->status == 1? translate('Active') : translate('Inactive')  }}</td>


        @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
