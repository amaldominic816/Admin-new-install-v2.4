
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate('Store_Wise_Review_List')}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ translate('Store_details') }}</th>
                <th></th>
                <th>
                    {{ translate('Store_Name')  }}- {{ $data['store_name'] ?? translate('All') }}
                    <br>
                    {{ translate('Store_ID')  }}- {{ $data['store_id'] ?? translate('All') }}
                    <br>

                    {{ translate('Rating')  }}- {{ $data['rating']?? translate('All') }}
                    <br>
                    {{ translate('Reviews')  }}- {{ $data['total_reviews'] ?? translate('All') }}
                </th>
                <th> </th>
                </tr>


        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('Item_Name') }}</th>
            <th>{{ translate('Order_ID') }}</th>
            <th>{{ translate('Customer_Name') }}</th>
            <th>{{ translate('Rating') }}</th>
            <th>{{ translate('Review') }}</th>
            <th>{{ translate('Status') }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $review)

            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $review?->item?->name }}</td>
        <td> {{$review->order_id}}</td>
        <td>
            {{$review?->customer ? $review?->customer?->f_name .' '.$review?->customer?->l_name  : translate('messages.Customer_Not_Found')}}
        </td>
        <td> {{$review->rating}}</td>
        <td>{{$review->comment}}</td>
        <td>{{ $review->status == 1 ? translate('messages.active') : translate('messages.inactive') }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
