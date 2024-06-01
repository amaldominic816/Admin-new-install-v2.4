
<div class="row">
    @php($address = \App\Models\BusinessSetting::where(['key' => 'address'])->first()->value)
    <table>
        <thead>
            <tr>

                <th>
                    {{ translate('Disbursement_Invoice') }}
                </th>
                <th></th>
                <th></th>
                <th>

                </th>
            </tr>
            <tr>

                <th>
                    {{ \App\CentralLogics\Helpers::time_date_format(date("Y-m-d h:i:s",time())) }}
                </th>
                <th></th>
                <th></th>
                <th>
                    {{ $address  }}
                </th>
            </tr>
            <tr>
                <th>
                    {{ translate('Disbursement_ID')  }}:{{ $data['disbursement']['id']}}
                    <br>

                </th>
                <th></th>
                <th>
                    {{ translate('created_at')  }}
                    <br>
                    {{ \App\CentralLogics\Helpers::time_date_format($data['disbursement']['created_at']) }}
                </th>
                <th>
                    {{ translate('total_amount')  }}
                    <br>
                    {{\App\CentralLogics\Helpers::format_currency($data['disbursement']['total_amount'])}}

                </th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            @if($data['type'] == 'store')

            <th>{{ translate('Store_Info') }}</th>
            @else
            <th>{{ translate('Delivery_Man_Info') }}</th>
            @endif
            <th>{{ translate('Payment_method') }}</th>
            <th>{{ translate('amount') }}</th>
            <th>{{ translate('status') }}</th>

        </thead>
        <tbody>
        @foreach($data['disbursements'] as $key => $disb)
            <tr>
        <td>{{ $loop->index+1}}</td>
        @if($data['type'] == 'store')

        <td>{{ $disb->store->name }}</td>
        @else
            <th>{{$disb->delivery_man->f_name.' '.$disb->delivery_man->l_name}}</th>
        @endif
        <td>
            <div class="name">{{translate('payment_method')}} : {{$disb->withdraw_method->method_name}}</div>
            @forelse(json_decode($disb->withdraw_method->method_fields, true) as $key=> $item)
            <br>
                <div>
                    <span>{{  translate($key) }}</span>
                    <span>:</span>
                    <span class="name">{{$item}}</span>
                </div>

            @empty

            @endforelse
        </td>
        <td>
            {{\App\CentralLogics\Helpers::format_currency($disb['disbursement_amount'])}}
        </td>
        <td>
            @if($disb->status=='pending')
            <label class="badge badge-soft-primary">{{ translate('pending') }}</label>
        @elseif($disb->status=='completed')
            <label class="badge badge-soft-success">{{ translate('Completed') }}</label>
        @else
            <label class="badge badge-soft-danger">{{ translate('canceled') }}</label>
        @endif
        </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
