@extends('layouts.admin.app')

@section('title',translate('messages.Recommended_stores'))

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
                    {{translate('messages.Recommended_stores')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                            <form action="{{ route('admin.store.recommended_store_add') }}" method="GET">
                                <div class="row gy-3 align-items-end">
                                    <div class="col-12">
                                        <div class="d-flex gap-2 flex-wrap" >

                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="hidden" id="store_ids" name="selected_store_ids" value="">

                                        <h3 for="name" >{{ translate('Stores')}}</h3>
                                        <div class="w-100 px-2">
                                            <div class="search-form mb-3">
                                                <button type="button" class="btn"></button>
                                                <input type="text" class="js-form-search form-control search-bar-input" onkeyup="search_product()" placeholder="{{translate('Search Stores')}}...">
                                            </div>
                                            <div class="d-flex flex-wrap column-gap-4 row-gap-2 max-h-40vh overflow-y-auto overflow-x-hidden search-result-box" id='hide_class'> </div>

                                        <div class="mb-4 row g-4 selected_store_list" id="hide_class_2" ></div>
                                    </div>

                            <div class="btn--container justify-content-end mt-4">
                                <button type="reset" onclick="remove_all_data()" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{translate('messages.Recommended_Stores_List')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$stores->total()}}</span>
                            </h5>
                            <div class="form-check text-start mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" name="shuffle_store" id="flexCheckDefault" {{ $shuffle_recommended_store == 1 ? 'checked' : '' }} >
                                    <label
                                    onclick="toogleStatusModal(event,'store_shffle','counter-on.png','counter-off.png','{{translate('Want_to_shuffle_the_store_list?')}}','{{translate('Want_to_disable_shuffle_store_list?')}}',`<p>{{translate('If_enabled,_store_recommended_section_will_be_shuffled.’')}}</p>`,`<p>{{translate('If_disabled,_store_recommended_section_will_not_be_shuffled.’')}}</p>`)" id="store_shffle"
                                    class="form-check-label" for="flexCheckDefault">
                                        {{translate('Shuffle_store_when_page_reload?')}}
                                    </label>
                            </div>
                            <form  action="{{ route('admin.store.shuffle_recommended_store',['status' => $shuffle_recommended_store ?? 0]) }}" method="get" id="store_shffle_form">
                            </form>

                            <form  class="search-form">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch_" value="{{ request()?->search ?? null }}" type="search" name="search" class="form-control"
                                            placeholder="{{translate('ex_:_Store_name')}}" aria-label="Search" >
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
                                <th class="border-0">{{translate('messages.Store_Name')}}</th>
                                <th class="border-0">{{translate('messages.Ratings')}}</th>
                                <th class="border-0">{{translate('messages.Total_Products')}}</th>
                                <th class="border-0">{{translate('messages.Total_Orders')}}</th>
                                <th class="border-0">{{translate('messages.status')}}</th>
                                <th class="border-0">{{translate('messages.action')}}</th>
                            </tr>

                            </thead>

                            <tbody id="set-rows">
                            @foreach($stores as $key=>$store)
                                <tr>
                                    <td class="text-center">
                                        <span class="mr-3">
                                            {{$key+$stores->firstItem()}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div>
                                            <a href="{{route('admin.store.view', $store->id)}}" class="table-rest-info" alt="view store">
                                            <img class="img--60 circle" onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                                    src="{{asset('storage/app/public/store')}}/{{$store['logo']}}">
                                                <div class="info"><div class="text--title">
                                                    {{Str::limit($store->name,20,'...')}}
                                                    </div>
                                                    <div class="font-light">
                                                        {{translate('messages.id')}}:{{$store->id}}
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <i class="fs-13 tio-star"></i>
                                        @php
                                        $ratings= \App\CentralLogics\StoreLogic::calculate_store_rating($store['rating'])
                                        @endphp
                                        {{ $ratings['rating'] }}
                                        </td>
                                    <td class="text-center">
                                        {{ $store->items_count }}
                                    </td>
                                    <td class="text-center">
                                        {{ $store->orders_count }}
                                    </td>



                                    <td class="text-center">
                                        <label class="toggle-switch toggle-switch-sm" for="publishCheckbox{{$store->id}}">
                                            <input type="checkbox" onclick="location.href='{{route('admin.store.recommended_store_status',[$store['id'],$store->Store_config->is_recommended?0:1])}}'"class="toggle-switch-input" id="publishCheckbox{{$store->id}}" {{$store->Store_config->is_recommended?'checked':''}}>
                                            <span class="toggle-switch-label mx-auto">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:" onclick="form_alert('item-{{$store['id']}}','{{ translate('Want_to_remove_the_store_from_the_list?') }}')" title="{{translate('messages.delete')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.store.recommended_store_remove',[$store['id']])}}"
                                                    method="post" id="item-{{$store['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($stores) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $stores->links() !!}
                        </div>
                        @if(count($stores) === 0)
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
    <script>
        let selected_store_ids = [];
        function search_product(){
            let name = $(".search-bar-input").val();
            // $("#hide_class").removeClass('d-none');
            if (name.length >0) {
            $("#hide_class").addClass('d-flex');
            $("#hide_class").addClass('search-result-box');
            $("#hide_class").removeClass('d-none');

            $("#hide_class_2").addClass('d-none');

                $.get("{{route('admin.get_all_stores')}}",{name:name},(response)=>{
                    $('.search-result-box').empty().html(response.result);
                })
            }
        }

        function selected_stores(key, remove=false) {
            if(remove == true){
                selected_store_ids = selected_store_ids.filter(function(e) { return e !== key })
            }else{
                selected_store_ids.push(key);
            }

            $("#hide_class").removeClass('d-flex');
            $("#hide_class").removeClass('search-result-box');
            $("#hide_class").addClass('d-none');
            $("#hide_class_2").removeClass('d-none');

            $('#store_ids').val(selected_store_ids);
            $.get("{{route('admin.store.selected_stores')}}",{id:selected_store_ids},(response)=>{
                $('.selected_store_list').empty().html(response.result);
            })
        }

        function remove_all_data(){
            $("#hide_class").removeClass('d-flex');
            $("#hide_class").removeClass('search-result-box');
            $("#hide_class").addClass('d-none');
            $("#hide_class_2").addClass('d-none');
            selected_store_ids = [];
            $('#store_ids').val(null);
        }
    </script>
@endpush
