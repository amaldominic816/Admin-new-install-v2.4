<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('Basic_Campaign_List') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Message_Analytics') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Total_Campaign')  }}: {{ $data->count() }}
                    <br>
                    {{ translate('Currently_Running')  }}: {{ $data->where('status',1)->count() }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Content')  }}: : {{ $search ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Campaign_Name') }}</th>
            <th>{{ translate('Description') }}</th>
            <th>{{ translate('Start_Date') }}</th>
            <th>{{ translate('End_Date') }}</th>
            <th>{{ translate('Daily_Start_Time') }}</th>
            <th>{{ translate('Daily_End_Time') }}</th>
            <th>{{ translate('Total_Store_Joined') }} </th>
        </thead>
        <tbody>
        @foreach($data as $key => $campaign)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $campaign->title }}</td>
        <td>{{ $campaign->description }}</td>
        <td>{{ $campaign->start_date->format('d M Y') }}</td>
        <td>{{ $campaign->end_date->format('d M Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->start_time)->format("H:i A") }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->end_time)->format("H:i A") }}</td>
        <td>{{ $campaign->stores->count() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
