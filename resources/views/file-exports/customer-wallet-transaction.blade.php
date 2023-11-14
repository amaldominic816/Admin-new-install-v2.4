<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('Wallet_transaction_history') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th>
                    @if ($data['from'])
                    <br>
                    {{ translate('from' )}} - {{ $data['from']?Carbon\Carbon::parse($data['from'])->format('d M Y'):'' }}
                    @endif
                    @if ($data['to'])
                    <br>
                    {{ translate('to' )}} - {{ $data['to']?Carbon\Carbon::parse($data['to'])->format('d M Y'):'' }}
                    @endif
                    <br>
                    {{ translate('transaction_type')  }}- {{  $data['transaction_type']?translate($data['transaction_type']):translate('messages.All') }}
                    <br>
                    {{ translate('customers')  }}- {{  $data['customer']??translate('messages.All') }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
                @php
                $credit = $data['data'][0]->total_credit;
                $debit = $data['data'][0]->total_debit;
                $balance = $credit - $debit;
            @endphp
            <tr>
                <th>{{ translate('Analytics') }}</th>
                <th></th>
                <th>
                    {{ translate('messages.debit')  }}- {{\App\CentralLogics\Helpers::format_currency($debit)}}
                    <br>
                    {{ translate('messages.credit')  }}- {{\App\CentralLogics\Helpers::format_currency($credit)}}
                    <br>
                    {{ translate('messages.balance')  }}- {{\App\CentralLogics\Helpers::format_currency($balance)}}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{translate('messages.transaction_id')}}</th>
            <th>{{translate('messages.transaction_date')}}</th>
            <th>{{translate('messages.customer')}}</th>
            <th>{{translate('messages.credit')}}</th>
            <th>{{translate('messages.debit')}}</th>
            <th>{{translate('messages.balance')}}</th>
            <th>{{translate('messages.transaction_type')}}</th>
            <th>{{translate('messages.reference')}}</th>
        </thead>
        <tbody>
        @foreach($data['transactions'] as $key => $wt)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{$wt->transaction_id}}</td>
            <td>
                {{date('d-m-Y',strtotime($wt['created_at']))}}
            </td>
            <td>{{ $wt->user?$wt->user->f_name.' '.$wt->user->l_name:translate('messages.not_found') }}</td>
            <td>{{$wt->credit}}</td>
            <td>{{$wt->debit}}</td>
            <td>{{$wt->balance}}</td>
            <td>
                {{ translate('messages.'.$wt->transaction_type)}}
            </td>
            <td>{{$wt->reference}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
