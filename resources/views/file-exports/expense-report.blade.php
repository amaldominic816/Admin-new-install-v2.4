<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('expense_reports') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    @if(isset($data['module']))
                    {{ translate('module' )}} - {{ $data['module']?translate($data['module']):translate('all') }}
                    <br>
                    @endif

                    {{ translate('zone' )}} - {{ $data['zone']??translate('all') }}
                    <br>
                    {{ translate('store' )}} - {{ $data['store']??translate('all') }}
                    @if (!isset($data['type']) )
                    <br>
                    {{ translate('customer' )}} - {{ $data['customer']??translate('all') }}
                    @endif
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
            <th>{{translate('messages.order_id')}}</th>
            <th>{{translate('Date & Time')}}</th>
            <th>{{ translate('Expense Type') }}</th>
            <th>{{ translate('Customer Name') }}</th>
            <th>{{translate('expense amount')}}</th>
        </thead>
        <tbody>
        @foreach($data['expenses'] as $key => $exp)
            <tr>
                <td>{{ $key+1}}</td>
                <td>
                    @if ($exp->order)
                    {{ $exp['order_id'] }}
                    @endif
                </td>
                <td>
                    {{date('Y-m-d '.config('timeformat'),strtotime($exp->created_at))}}
                </td>
                <td>{{translate("messages.{$exp['type']}")}}</td>
                <td>
                    @if (isset($exp->order->customer))
                    {{ $exp->order->customer->f_name.' '.$exp->order->customer->l_name }}
                    @elseif ($exp['type'] == 'add_fund_bonus')
                    {{ $exp->user->f_name.' '.$exp->user->l_name }}
                    @else
                    {{translate('messages.invalid_customer')}}
                    @endif
                </td>
                <td>{{\App\CentralLogics\Helpers::format_currency($exp['amount'])}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
