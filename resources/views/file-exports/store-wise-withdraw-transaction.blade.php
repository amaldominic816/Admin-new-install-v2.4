
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Store_Withdraw_Transactions')}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ translate('Filter_Criteria') }}</th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Content')  }}: {{ $data['search'] ?? translate('N/A') }}
                </th>
                <th> </th>
                </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Request_Created_At') }}</th>
            <th>{{ translate('Requested_Amount') }}</th>
            <th>{{ translate('Status') }}</th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $tr)
            <tr>
                <td>{{ $loop->index+1}}</td>
                <td>{{ $tr?->created_at->format('Y-m-d '.config('timeformat')) ??  translate('N/A') }}</td>
                <td> {{ \App\CentralLogics\Helpers::format_currency($tr->amount) }}</td>
                <td>
                    @if($tr->approved==0)
                    {{ translate('messages.pending') }}
                    @elseif($tr->approved==1)
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
