<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('messages.delivery_man_payments') }}</h1></div>
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
                <th>{{ translate('messages.provided_st') }}</th>
                <th>{{ translate('messages.payment_amount') }}</th>
                <th>{{ translate('messages.delivery_man_name') }}</th>
                <th>{{ translate('messages.phone') }}</th>
                <th>{{ translate('messages.payment_method') }}</th>
                <th>{{ translate('messages.references') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['dm_earnings'] as $key => $at)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{$at->id}}</td>
                <td>{{$at->created_at->format('Y-m-d '.config('timeformat'))}}</td>
                <td>{{$at['amount']}}</td>
                <td>
                    @if($at->delivery_man)
                    {{$at->delivery_man->f_name.' '.$at->delivery_man->l_name}}
                    @else
                    {{translate('messages.deliveryman_deleted')}}
                    @endif 
                </td>
                <td>
                    @if($at->delivery_man)
                    {{$at->delivery_man->phone}}
                    @else
                    {{translate('messages.deliveryman_deleted')}}
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
