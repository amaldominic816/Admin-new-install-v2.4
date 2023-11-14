<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('messages.store_withdraw_transactions') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('filter_criteria') }} -</th>
                <th></th>
                <th></th>
                <th> 
                    {{ translate('request_status')  }}- {{  $data['request_status']?translate($data['request_status']):translate('all') }}
                    <br>
                    {{ translate('Search_Bar_Content')  }}- {{ $data['search'] ??translate('N/A') }}

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ translate('messages.sl') }}</th>
                <th>{{ translate('messages.request_time') }}</th>
                <th>{{ translate('messages.requested_amount') }}</th>
                <th>{{ translate('messages.store_name') }}</th>
                <th>{{ translate('messages.owner_name') }}</th>
                <th>{{ translate('messages.phone') }}</th>
                <th>{{ translate('messages.email') }}</th>
                <th>{{ translate('messages.bank_account_no.') }}</th>
                <th>{{ translate('messages.request_status') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['withdraw_requests'] as $key => $wr)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{date('Y-m-d '.config('timeformat'),strtotime($wr->created_at))}}</td>
                <td>{{$wr['amount']}}</td>
                <td>
                    @if($wr->vendor)
                    {{ $wr->vendor->stores[0]->name }}
                    @else
                    {{translate('messages.store deleted!') }}
                    @endif
                </td>
                <td>{{$wr->vendor->f_name}} {{$wr->vendor->l_name}}</td>
                <td>{{$wr->vendor->phone}}</td>
                <td>{{$wr->vendor->email}}</td>
                <td>{{$wr->vendor && $wr->vendor->account_no ? $wr->vendor->account_no : 'No Data found'}}</td>
                <td>
                    @if($wr->approved==0)
                        {{ translate('messages.pending') }}
                    @elseif($wr->approved==1)
                        {{ translate('messages.approved') }}
                    @else
                        {{ translate('messages.denied') }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
