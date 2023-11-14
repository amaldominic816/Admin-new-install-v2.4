
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Attributes_List')}}
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
            <th>{{ translate('Attribute_Name') }}</th>
            <th>{{ translate('ID') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $attribute)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $attribute->name }}</td>
        <td>{{ $attribute->id }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
