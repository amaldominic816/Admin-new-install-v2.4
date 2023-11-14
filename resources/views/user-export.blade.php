<div class="row">
    <div class="col-lg-12 text-center "><h1 >Basic Campaign List</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>Message Analytics</th>
                <th></th>
                <th></th>
                <th> Total Campaign : {{ $data->count() }}

<br>
Dara
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th>SL</th>
            <th>Cmapaign Name</th>
            <th>Description</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Daily Start Time</th>
            <th>Daily End Time</th>
            <th>Total Store Joined </th>
        </thead>
        <tbody>
        @foreach($data as $key => $campaign)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $campaign->title }}</td>
        <td>{{ $campaign->description }}</td>
        <td>{{ $campaign->start_date->format('d M Y') }}</td>
        <td>{{ $campaign->end_date->format('d M Y') }}</td>
        <td>{{   \Carbon\Carbon::parse($campaign->start_time)->format("H:i A") }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->end_time)->format("H:i A") }}</td>
        <td>{{ $campaign->stores->count() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
