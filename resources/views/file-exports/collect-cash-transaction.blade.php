<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('messages.collect_cash_transactions') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('filter_criteria') }} -</th>
                <th></th>
                <th></th>
                <th> 

                    {{ translate('Search_Bar_Content')  }}- {{ $data['search'] ??translate('N/A') }}

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ translate('messages.sl') }}</th>
                <th>{{ translate('messages.transaction_id') }}</th>
                <th>{{ translate('messages.transaction_time') }}</th>
                <th>{{ translate('messages.collected_amount') }}</th>
                <th>{{ translate('messages.collected_from') }}</th>
                <th>{{ translate('messages.user_type') }}</th>
                <th>{{ translate('messages.phone') }}</th>
                <th>{{ translate('messages.email') }}</th>
                <th>{{ translate('messages.payment_method') }}</th>
                <th>{{ translate('messages.references') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['account_transactions'] as $key => $at)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{$at->id}}</td>
                <td>{{$at->created_at->format('Y-m-d '.config('timeformat'))}}</td>
                <td>{{$at['amount']}}</td>
                <td>
                    @if($at->store)
                    {{ $at->store->name}}
                    @elseif($at->deliveryman)
                    {{ $at->deliveryman->f_name }} {{ $at->deliveryman->l_name }}
                    @else
                        {{translate('messages.not_found')}}
                    @endif
                </td>
                <td>{{translate($at['from_type'])}}</td>
                <td>
                    @if($at->store)
                    {{ $at->store->phone}}
                    @elseif($at->deliveryman)
                    {{ $at->deliveryman->phone }}
                    @else
                        {{translate('messages.not_found')}}
                    @endif
                </td>
                <td>
                    @if($at->store)
                    {{ $at->store->email}}
                    @elseif($at->deliveryman)
                    {{ $at->deliveryman->email }}
                    @else
                        {{translate('messages.not_found')}}
                    @endif
                </td>
                <td>{{translate($at->method)}}</td>
                <td>{{$at['ref']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
