
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Module_List')}}
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
            <th>{{ translate('name') }}</th>
            <th>{{ translate('module_id') }}</th>
            <th>{{ translate('business_Module_type') }}</th>
            <th>{{ translate('total_stores') }}</th>
            <th>{{ translate('Status') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $addon)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $addon->module_name }}</td>
        <td>{{ $addon->id }}</td>
        <td>
            {{ translate($addon->module_type) }}
        </td>
        <td>
            {{ $addon->stores_count }}
        </td>

        <td>{{ $addon?->status == 1 ? translate('Active') : translate('Inactive') }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
