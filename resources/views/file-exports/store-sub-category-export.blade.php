
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Category_List')}}
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
            <th>{{ translate('Category_ID') }}</th>
            <th>{{ translate('Main_Category') }}</th>
            <th>{{ translate('Sub_Category') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $category)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $category->id }}</td>
        <td> {{$category->parent?$category->parent['name']:translate('messages.category_deleted')}}
            <td>{{ $category->name }}</td>


            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
