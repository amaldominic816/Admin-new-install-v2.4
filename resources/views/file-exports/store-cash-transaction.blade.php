
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Store_Cash_Transactions')}}
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
            <th>{{ translate('Transaction_ID') }}</th>
            <th>{{ translate('Transaction_Time') }}</th>
            <th>{{ translate('Balance_Before_Transaction') }}</th>
            <th>{{ translate('Transaction_Amount') }}</th>
            <th>{{ translate('Reference') }}</th>
            <th>{{ translate('Payment_method') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $tr)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $tr->id }}</td>
        <td>{{ $tr?->created_at->format('Y-m-d '.config('timeformat')) ??  translate('N/A') }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->current_balance) }}
        </td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->amount) }}
        </td>
        <td>{{ $tr->ref ??  translate('N/A') }}</td>
        <td>{{ $tr->method ??  translate('N/A') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
