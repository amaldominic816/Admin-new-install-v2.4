@extends('layouts.vendor.app')

@section('title',translate('messages.Product_Gallery'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@php($store_data=\App\CentralLogics\Helpers::get_store_data())
    <div class="content container-fluid"> 
        <!-- Page Header -->
        <div class="page-header">
            <div class="btn--container align-items-center mb-0">
                <div class="d-flex gap-2">
                    <img class="h--50px"
                        src="{{ asset('public/assets/admin/img/group.png') }}" alt="Product_Gallery">
                    <div>
                        <h1 class="page-header-title"> {{translate('messages.Product_Gallery')}}<span class="badge badge-soft-dark ml-2" id="itemCount"></span></h1>
                    <p>{{ translate('search_product_and_use_its_info_to_create_own_product') }}</p>
                    </div>
                </div>
                {{-- @if (($store_data->module->module_type == 'food') && $toggle_veg_non_veg)
                <!-- Veg/NonVeg filter -->
                <div>
                    <select name="category_id" onchange="set_filter('{{url()->full()}}',this.value, 'type')" data-placeholder="{{translate('messages.all')}}" class="form-control max-lg-h-40px">
                        <option value="all" {{$type=='all'?'selected':''}}>{{translate('messages.all')}}</option>
                        <option value="veg" {{$type=='veg'?'selected':''}}>{{translate('messages.veg')}}</option>
                        <option value="non_veg" {{$type=='non_veg'?'selected':''}}>{{translate('messages.non_veg')}}</option>
                    </select>
                </div>
                <!-- End Veg/NonVeg filter -->
                @endif --}}
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card mb-3">
            <!-- Header -->
            <div class="card-body border-0">
                <form id="search-form" class="search-form">
                    @csrf
                    <input type="hidden" value="1" name="product_gallery">
                    <div class="row">
                        <div class="col-11">
                            <input id="datatableSearch" type="search" value="{{  request()?->search ?? null }}" name="search" class="form-control" placeholder="{{translate('messages.ex_search_name')}}" aria-label="{{translate('messages.search_here')}}">
                        </div>
                        <div class="col-1">
                            <button type="submit" class="btn btn--primary">{{ translate('messages.search') }}</button>
                        </div>
                    </div>
                </form>
                {{-- <div class="search--button-wrapper justify-content-end">
                    <form id="search-form" class="search-form">
                    @csrf
                    <input type="hidden" value="1" name="product_gallery">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" type="search" value="{{  request()?->search ?? null }}" name="search" class="form-control" placeholder="{{translate('messages.ex_search_name')}}" aria-label="{{translate('messages.search_here')}}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                    <!-- Unfold -->
                    <div class="hs-unfold mr-2 min--250">
                        <select name="category_id" id="category" onchange="set_filter('{{url()->full()}}',this.value, 'category_id')" data-placeholder="{{translate('messages.select_category')}}" class="js-data-example-ajax form-control">
                            @if($category)
                                <option value="{{$category->id}}" selected>{{$category->name}} ({{$category->position == 0?translate('messages.main'):translate('messages.sub')}})</option>
                            @else
                                <option value="all" selected>{{translate('messages.all_categories')}}</option>
                            @endif
                        </select>
                    </div>
                    <!-- End Unfold -->
                </div> --}}
            </div>
            <!-- End Header -->
        </div>
        <!-- End Card -->
        <div>
            <h2>{{ translate('messages.Product_List') }}</h2>
            <p>{{ translate('search_product_and_use_its_info_to_create_own_product') }}</p>
        </div>

                    <div class="row" id="set-rows">
                        @include('vendor-views.product.partials._gallery', [
                            $items,
                        ])
                    </div>
                @if(count($items) === 0)
                <div class="empty--data">
                    <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif

            <!-- End Table -->
    </div>

@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
          select: {
            style: 'multi',
            classMap: {
              checkAll: '#datatableCheckAll',
              counter: '#datatableCounter',
              counterInfo: '#datatableCounterInfo'
            }
          },
          language: {
            zeroRecords: '<div class="text-center p-4">' +
                '<img class="w-7rem mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">' +

                '</div>'
          }
        });

        $('#datatableSearch').on('mouseup', function (e) {
          var $input = $(this),
            oldValue = $input.val();

          if (oldValue == "") return;

          setTimeout(function(){
            var newValue = $input.val();

            if (newValue == ""){
              // Gotcha
              datatable.search('').draw();
            }
          }, 1);
        });

        $('#toggleColumn_index').change(function (e) {
          datatable.columns(0).visible(e.target.checked)
        })
        $('#toggleColumn_name').change(function (e) {
          datatable.columns(1).visible(e.target.checked)
        })

        $('#toggleColumn_type').change(function (e) {
          datatable.columns(2).visible(e.target.checked)
        })

        $('#toggleColumn_status').change(function (e) {
          datatable.columns(4).visible(e.target.checked)
        })
        $('#toggleColumn_price').change(function (e) {
          datatable.columns(3).visible(e.target.checked)
        })
        $('#toggleColumn_action').change(function (e) {
          datatable.columns(5).visible(e.target.checked)
        })


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#category').select2({
            ajax: {
                url: '{{route("vendor.category.get-all")}}',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        all:true,
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                    results: data
                    };
                },
                __port: function (params, success, failure) {
                    var $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });
    </script>

    <script>
        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('vendor.item.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
