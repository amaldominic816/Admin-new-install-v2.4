@extends('layouts.vendor.app')

@section('title',translate('messages.flash_sales'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/condition.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.flash_sale_product_setup')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row g-3">


            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{translate('messages.flash_sale_product_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$items->total()}}</span>
                            </h5>
                            <form  class="search-form">
                                <!-- Search -->

                                <div class="input-group input--group">
                                    <input id="datatableSearch_" value="{{ request()?->search ?? null }}" type="search" name="search" class="form-control"
                                            placeholder="{{translate('ex_:_name')}}" aria-label="Search" >
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th class="border-0">{{translate('sl')}}</th>
                                <th class="border-0">{{translate('messages.product')}}</th>
                                <th class="border-0">{{translate('messages.Current_Stock')}}</th>
                                <th class="border-0">{{translate('messages.Flash_sale_Qty')}}</th>
                                <th class="border-0">{{translate('messages.Qty_Sold')}}</th>
                                <th class="border-0">{{translate('messages.Discount')}}</th>
                                <th class="border-0">{{translate('messages.Sold_Amount')}}</th>
                                <th class="border-0">{{translate('messages.status')}}</th>
                            </tr>

                            </thead>

                            <tbody id="set-rows">
                            @foreach($items as $key=>$item)
                                <tr>
                                    <td class="text-center">
                                        <span class="mr-3">
                                            {{$key+$items->firstItem()}}
                                        </span>
                                    </td>

                                    <?php
                                    $t2= Carbon\Carbon::parse($item->flashSale->end_date) ;
                                    ?>
        
      
                                    <td class="text-center">
                                        <a class="media align-items-center" href="{{route('vendor.item.view',[$item['item_id']])}}">
                                            <img class="avatar avatar-lg mr-3" src="{{asset('storage/app/public/product')}}/{{$item->item['image']}}"
                                                    onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'" alt="{{$item->item->name}} image">
                                            <div class="media-body">
                                                <h5 class="text-hover-primary mb-0">{{Str::limit($item->item['name'],20,'...')}}</h5>
                                            </div>
                                        </a>
                                    </td>

                                    <td class="text-center">
                                        {{ $item['available_stock'] }}
                                    </td>
                                    <td class="text-center">
                                        {{ $item['stock'] }}
                                    </td>
                                    <td class="text-center">
                                        {{ $item['sold'] }}
                                    </td>
                                    <td class="text-center">
                                        @if($item->discount_type == 'percent')
                                        {{$item['discount']}} %
                                        @else
                                        {{\App\CentralLogics\Helpers::format_currency($item['discount'])}}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{\App\CentralLogics\Helpers::format_currency($item['price'] * $item['sold'])}}

                                    </td>
                                    <td class="text-center">
                                        @if($item['status'] == 0 || $item->flashSale->is_publish == 0)
                                        <span class="badge badge-soft-info">{{ translate('off')}}</span>
                                        @elseif($item->flashSale->is_publish == 1 && $t2->gte(now())  )
                                        <span class="badge badge-soft-success"> {{ translate('running')}} </span>
                                        @else
                                        <span class="badge badge-soft-danger">{{ translate('expired')}}</span>
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($items) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $items->links() !!}
                        </div>
                        @if(count($items) === 0)
                        <div class="empty--data">
                            <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')

@endpush
