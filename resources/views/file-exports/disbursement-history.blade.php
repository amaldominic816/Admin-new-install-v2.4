
<div class="row">
    @php($address = \App\Models\BusinessSetting::where(['key' => 'address'])->first()->value)
    <table>
        <thead>
            <tr>

                <th>
                    {{ translate('Disbursement_List') }}
                </th>
                <th></th>
                <th></th>
                <th>
                    @if($data['type'] == 'store')
                        {{ translate('Store') }} - {{ $data['store'] }}
                    @else
                        {{ translate('Delivery_man') }} - {{ $data['delivery_man'] }}
                    @endif
                </th>
                <th></th>
                <th>

                </th>
            </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('id') }}</th>
            <th>{{ translate('created_at') }}</th>
            <th>{{ translate('amount') }}</th>
            <th>{{ translate('Payment_method') }}</th>
            <th>{{ translate('status') }}</th>

        </thead>
        <tbody>
        @foreach($data['disbursements'] as $key => $disb)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $disb['disbursement_id'] }}</td>
        <td>{{ \App\CentralLogics\Helpers::time_date_format($disb['created_at']) }}</td>
        <td>
            {{\App\CentralLogics\Helpers::format_currency($disb['disbursement_amount'])}}
        </td>
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
        <td>{{ $disb['status'] }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
</div>
