@extends('layouts.admin.app')

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
                    {{translate('messages.flash_sale_setup')}}
                </span>
            </h1>
        </div>
        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        @php($default_lang = str_replace('_', '-', app()->getLocale()))
        <!-- End Page Header -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.flash-sale.store')}}" method="post">
                            @csrf
                            @if ($language)
                                    <ul class="nav nav-tabs mb-3 border-0">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active"
                                            href="#"
                                            id="default-link">{{translate('messages.default')}}</a>
                                        </li>
                                        @foreach (json_decode($language) as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link"
                                                    href="#"
                                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="row">
                                        <div class="col-12">

                                            <div class="lang_form" id="default-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_title">{{ translate('messages.title') }}
                                                        ({{translate('messages.default')}})
                                                    </label>
                                                    <input type="text" name="title[]" id="default_title"
                                                        class="form-control" maxlength="100" placeholder="{{ translate('messages.ex_:_new_flash_sale') }}"

                                                        oninvalid="document.getElementById('en-link').click()">
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            </div>
                                        @foreach (json_decode($language) as $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_title">{{ translate('messages.title') }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" maxlength="100" name="title[]" id="{{ $lang }}_title"
                                                        class="form-control" placeholder="{{ translate('messages.ex_:_new_flash_sale') }}"
                                                        oninvalid="document.getElementById('en-link').click()">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="default_title">{{ translate('messages.discount_Bearer') }}
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Define_the_cost_amount_you_want_to_bear_for_this_Flash_Sale.The_total_bear_amount_should_be_100.') }}">
                                                        <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="row g-3 __bg-F8F9FC-card">
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('admin') }}(%)</label>
                                                <input type="number"  min=".01" step="0.001" max="100" name="admin_discount_percentage"
                                                        value=""
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_50') }}" required>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('messages.store_owner') }}(%)</label>
                                                <input type="number"  min=".01" step="0.001" max="100" name="vendor_discount_percentage"
                                                        value=""
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_50') }}" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="default_title">{{ translate('messages.validity') }}
                                                </label>
                                            </div>
                                            <div class="row g-3 __bg-F8F9FC-card">
                                                <div class="col-6">
                                                    <div>
                                                        <label class="input-label" for="title">{{translate('messages.start_date')}}</label>
                                                        <input type="datetime-local" id="from" class="form-control" required="" name="start_date">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div>
                                                        <label class="input-label" for="title">{{translate('messages.end_date')}}</label>
                                                        <input type="datetime-local" id="to" class="form-control" required="" name="end_date">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            @endif
                            <div class="btn--container justify-content-end mt-5">
                                <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
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
                                {{translate('messages.flash_sale_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$flash_sales->total()}}</span>
                            </h5>
                            <form  class="search-form">
                                <!-- Search -->

                                <div class="input-group input--group">
                                    <input id="datatableSearch_" value="{{ request()?->search ?? null }}" type="search" name="search" class="form-control"
                                            placeholder="{{translate('ex_:_flash_sale_title')}}" aria-label="Search" >
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
                                <th class="border-0">{{translate('messages.title')}}</th>
                                <th class="border-0">{{translate('messages.duration')}}</th>
                                <th class="border-0">{{translate('messages.active_products')}}</th>
                                <th class="border-0">{{translate('messages.publish')}}</th>
                                <th class="border-0">{{translate('messages.action')}}</th>
                            </tr>

                            </thead>

                            <tbody id="set-rows">
                            @foreach($flash_sales as $key=>$flash_sale)
                                <tr>
                                    <td class="text-center">
                                        <span class="mr-3">
                                            {{$key+$flash_sales->firstItem()}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-size-sm text-body mr-3">
                                            {{Str::limit($flash_sale['title'],20,'...')}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="bg-gradient-light text-dark">{{$flash_sale->start_date?$flash_sale->start_date->format('d/M/Y'). ' - ' .$flash_sale->end_date->format('d/M/Y'): 'N/A'}}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-size-sm text-body mr-3">
                                            {{ $flash_sale->activeProducts->count()}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch toggle-switch-sm" for="is_publish-{{$flash_sale['id']}}">
                                            <input type="checkbox" class="toggle-switch-input" onclick="toogleStatusModal(event,'is_publish-{{$flash_sale['id']}}','zone-is_publish-on.png','zone-is_publish-off.png','{{translate('Want_to_publish_this_flash_sale?')}}','{{translate('Want_to_hide_this_flash_sale?')}}',`<p>{{translate('If_you_publish_this_flash_sale,_Customers_can_see_all_stores_&_products_available_under_this_flash_sale_from_the_Customer_App_&_Website._other_flash_sales_will_be_turned_off.')}}</p>`,`<p>{{translate('If_you_hide_this_flash_sale,_Customers_Will_NOT_see_all_stores_&_products_available_under_this_flash_sale_from_the_Customer_App_&_Website.')}}</p>`)" id="is_publish-{{$flash_sale['id']}}" {{$flash_sale->is_publish?'checked':''}}>
                                            <span class="toggle-switch-label mx-auto">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form action="{{route('admin.flash-sale.publish',[$flash_sale['id'],$flash_sale->is_publish?0:1])}}" method="get" id="is_publish-{{$flash_sale['id']}}_form">
                                        </form>
                                        {{-- <label class="toggle-switch toggle-switch-sm" for="publishCheckbox{{$flash_sale->id}}">
                                            <input type="checkbox" onclick="location.href='{{route('admin.flash-sale.publish',[$flash_sale['id'],$flash_sale->is_publish?0:1])}}'"class="toggle-switch-input" id="publishCheckbox{{$flash_sale->id}}" {{$flash_sale->is_publish?'checked':''}}>
                                            <span class="toggle-switch-label mx-auto">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label> --}}
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn p-2 btn--primary btn-outline-primary" href="{{route('admin.flash-sale.add-product',[$flash_sale['id']])}}" title="{{translate('messages.add-product')}}"><i class="tio-add"></i>{{ translate('messages.Add_new_product') }}
                                            </a>
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.flash-sale.edit',[$flash_sale['id']])}}" title="{{translate('messages.edit')}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:" onclick="form_alert('flash_sale-{{$flash_sale['id']}}','{{ translate('Want to delete this flash_sale ?') }}')" title="{{translate('messages.delete')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.flash-sale.delete',[$flash_sale['id']])}}"
                                                    method="post" id="flash_sale-{{$flash_sale['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($flash_sales) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $flash_sales->links() !!}
                        </div>
                        @if(count($flash_sales) === 0)
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
        $("#from").on("change", function () {
            $('#to').attr('min',$(this).val());
        });

        $("#to").on("change", function () {
            $('#from').attr('max',$(this).val());
        });
        $(document).on('ready', function () {
            $('#from').attr('min',(new Date()).toISOString().split('T')[0]);
            $('#to').attr('min',(new Date()).toISOString().split('T')[0]);
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

            $(".lang_link").click(function(e){
                e.preventDefault();
                $(".lang_link").removeClass('active');
                $(".lang_form").addClass('d-none');
                $(this).addClass('active');

                let form_id = this.id;
                let lang = form_id.substring(0, form_id.length - 5);
                console.log(lang);
                $("#"+lang+"-form").removeClass('d-none');
                if(lang == '{{$default_lang}}')
                {
                    $("#from_part_2").removeClass('d-none');
                }
                else
                {
                    $("#from_part_2").addClass('d-none');
                }
            })
        </script>
@endpush
