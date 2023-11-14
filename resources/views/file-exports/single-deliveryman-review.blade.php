<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('delivery_man_review_list') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('delivery_man_info') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('name')  }}- {{ $data['dm']->f_name.' '.$data['dm']->l_name}}
                    <br>
                    {{ translate('phone')  }}- {{ $data['dm']->phone}}
                    <br>
                    {{ translate('email')  }}- {{ $data['dm']->email}}
                    <br>
                    {{ translate('total_rating')  }}- {{ count($data['dm']->rating)}}
                    <br>
                    {{ translate('average_review')  }}- {{count($data['dm']->rating)>0?number_format($data['dm']->rating[0]->average, 1, '.', ' '):0}}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            {{-- <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Content')  }}- {{ $data['search'] ??translate('N/A') }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr> --}}
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{translate('messages.order_id')}}</th>
            <th>{{translate('messages.customer_name')}}</th>
            <th>{{translate('messages.store_name')}}</th>
            <th>{{translate('messages.rating')}}</th>
            <th>{{translate('messages.review')}}</th>
        </thead>
        <tbody>
        @foreach($data['reviews'] as $key => $review)
            <tr>
                <td>{{ $key+1}}</td>
                <td>
                    {{ $review->order_id }}
                </td>
                <td>
                    @if ($review->customer)
                        {{$review->customer?$review->customer->f_name:""}} {{$review->customer?$review->customer->l_name:""}}
                    @else
                        {{translate('messages.customer_not_found')}}
                    @endif
                </td>
                <td>
                    {{$review->order?->store?->name}}
                </td>
                <td>{{ $review->rating }}</td>
                <td>{{ $review->comment }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
