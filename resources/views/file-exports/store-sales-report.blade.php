<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('store_sales_reports') }}</h1></div>
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
                <th>{{ translate('Analytics') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('gross_sale')  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('order_amount')) }}
                    <br>
                    {{ translate('total_tax')  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('total_tax_amount')) }}
                    <br>
                    {{ translate('total_commission')  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('transaction_sum_admin_commission')+$data['orders']->sum('transaction_sum_delivery_fee_comission')-$data['orders']->sum('transaction_sum_admin_expense')) }}
                    <br>
                    {{ translate('total_store_earning')  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('transaction_sum_store_amount')) }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{translate('product_image')}}</th>
            <th>{{ translate('Product_name') }}</th>
            <th>{{ translate('Available_Variations') }}</th>
            <th>{{ translate('QTY_Sold') }}</th>
            <th>
                {{ translate('Gross_Sale') }}</th>
            <th>
                {{ translate('Discount_Given') }}</th>
        </thead>
        <tbody>
        @foreach($data['items'] as $key => $item)
        <tr>
            <td>{{$key+1}}</td>
            <td></td>
            <td>{{  $item['name']  }}</td>
            <td>
                @if ($item->module->module_type == 'food')
                {{ \App\CentralLogics\Helpers::get_food_variations($item->food_variations) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_food_variations($item->food_variations) }}
                @else
                {{ \App\CentralLogics\Helpers::get_attributes($item->choice_options) == "  "  ? translate('N/A'): \App\CentralLogics\Helpers::get_attributes($item->choice_options) }}
                @endif
            </td>
            <td>
                {{ $item->orders->sum('quantity') }}
            </td>
            <td>
                {{ $item->orders->sum('price') }}
            </td>
            <td>
                {{ $item->orders->sum('discount_on_item') }}
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
