@extends('layouts.admin.app')

@section('title',translate('messages.bonuses'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/add.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.wallet_bonus_setup')}}
                </span>
            </h1>
            <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{translate('See_how_it_works!')}}</strong>
                <div class="blinkings">
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>

        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        @php($default_lang = str_replace('_', '-', app()->getLocale()))
        <!-- End Page Header -->
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.users.customer.wallet.bonus.store')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-12">
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
                                    <div class="lang_form" id="default-form">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_title">{{ translate('messages.Bonus_Title') }}
                                                        ({{ translate('messages.Default') }})
                                                    </label>
                                                    <input type="text" name="title[]" id="default_title"
                                                        class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"

                                                        oninvalid="document.getElementById('en-link').click()">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_description">{{ translate('messages.Short_Description') }}
                                                        ({{ translate('messages.Default') }})
                                                    </label>
                                                    <input type="text" name="description[]" id="default_description"
                                                        class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"

                                                        oninvalid="document.getElementById('en-link').click()">
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                        @foreach (json_decode($language) as $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="{{ $lang }}_title">{{ translate('messages.Bonus_Title') }}
                                                                ({{ strtoupper($lang) }})
                                                            </label>
                                                            <input type="text" name="title[]" id="{{ $lang }}_title"
                                                                class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"
                                                                oninvalid="document.getElementById('en-link').click()">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="{{ $lang }}_description">{{ translate('messages.Short_Description') }}
                                                                ({{ strtoupper($lang) }})
                                                            </label>
                                                            <input type="text" name="description[]" id="{{ $lang }}_description"
                                                                class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"
                                                                oninvalid="document.getElementById('en-link').click()">
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.Bonus_Title') }} ({{ translate('messages.default') }})</label>
                                                <input type="text" name="title[]" class="form-control"
                                                placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Bonus_Type')}}</label>
                                        <select name="bonus_type" class="form-control" id="bonus_type" required>
                                            <option value="percentage">{{translate('messages.percentage')}} (%)</option>
                                            <option value="amount">{{translate('messages.amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Bonus_Amount')}}
                                            <span  class="d-none" id='cuttency_symbol'>({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            </span>
                                            <span id="percentage">(%)</span>

                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('Set_the_bonus_amount/percentage_a_customer_will_receive_after_adding_money_to_his_wallet.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>


                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ translate('messages.Ex:_100') }}"  name="bonus_amount" id="bonus_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Minimum_Add_Money_Amount')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('Set_the_minimum_add_money_amount_for_a_customer_to_be_eligible_for_the_bonus.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99" placeholder="{{ translate('messages.Ex:_10') }}" name="minimum_add_amount" id="minimum_add_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Maximum_Bonus')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('Set_the_maximum_bonus_amount_a_customer_can_receive_for_adding_money_to_his_wallet.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>

                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ translate('messages.Ex:_1000') }}" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.start_date')}}</label>
                                        <input type="date" name="start_date" class="form-control" id="date_from" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.expire_date')}}</label>
                                        <input type="date" name="end_date" class="form-control" id="date_to" required>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{translate('messages.bonus_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$bonuses->total()}}</span></h5>
                            <form id="dataSearch" class="search-form min--270">
                            @csrf
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{ translate('messages.Ex_:_Search_by_bonus_title') }}" aria-label="{{translate('messages.search_here')}}">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom" id="table-div">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,

                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{translate('sl')}}</th>
                                <th class="border-0">{{translate('messages.bonus_title')}}</th>
                                <th class="border-0">{{translate('messages.bonus_info')}}</th>
                                <th class="border-0">{{translate('messages.bonus_amount')}}</th>
                                <th class="border-0">{{translate('messages.started_on')}}</th>
                                <th class="border-0">{{translate('messages.expires_on')}}</th>
                                <th class="border-0">{{translate('messages.status')}}</th>
                                <th class="border-0 text-center">{{translate('messages.action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($bonuses as $key=>$bonus)
                                <tr>
                                    <td>{{$key+$bonuses->firstItem()}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                    {{Str::limit($bonus['title'],25,'...')}}
                                    </span>
                                    </td>
                                    <td>{{ translate('messages.minimum_add_amount') }} -    {{\App\CentralLogics\Helpers::format_currency($bonus['minimum_add_amount'])}} <br>
                                        {{ translate('messages.maximum_bonus') }} - {{\App\CentralLogics\Helpers::format_currency($bonus['maximum_bonus_amount'])}}</td>
                                    <td>{{$bonus->bonus_type == 'amount'?\App\CentralLogics\Helpers::format_currency($bonus['bonus_amount']): $bonus['bonus_amount'].' (%)'}}</td>
                                    <td>{{ \Carbon\Carbon::parse($bonus->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bonus->end_date)->format('d M Y') }}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="bonusCheckbox{{$bonus->id}}">
                                            <input type="checkbox" onclick="location.href='{{route('admin.users.customer.wallet.bonus.status',[$bonus['id'],$bonus->status?0:1])}}'" class="toggle-switch-input" id="bonusCheckbox{{$bonus->id}}" {{$bonus->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">

                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.customer.wallet.bonus.update',[$bonus['id']])}}"title="{{translate('messages.edit_bonus')}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:" onclick="form_alert('bonus-{{$bonus['id']}}','{{ translate('Want to delete this bonus ?') }}')" title="{{translate('messages.delete_bonus')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.users.customer.wallet.bonus.delete',[$bonus['id']])}}"
                                            method="post" id="bonus-{{$bonus['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if(count($bonuses) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $bonuses->links() !!}
                        </div>
                        @if(count($bonuses) === 0)
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
    <div class="modal fade" id="how-it-works">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{asset('/public/assets/admin/img/image_127.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{translate('Wallet_bonus_is_only_applicable_when_a_customer_add_fund_to_wallet_via_outside_payment_gateway_!')}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{ translate('Customer_will_get_extra_amount_to_his_/_her_wallet_additionally_with_the_amount_he_/_she_added_from_other_payment_gateways._The_bonus_amount_will_be_deduct_from_admin_wallet_&_will_consider_as_admin_expense.') }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="slide-counter"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    $("#date_from").on("change", function () {
        $('#date_to').attr('min',$(this).val());
    });

    $("#date_to").on("change", function () {
        $('#date_from').attr('max',$(this).val());
    });

    $(document).on('ready', function () {
        $('#bonus_type').on('change', function() {
         if($('#bonus_type').val() == 'amount')
            {
                $('#maximum_bonus_amount').attr("readonly","true");
                $('#maximum_bonus_amount').val(null);
                $('#percentage').addClass('d-none');
                $('#cuttency_symbol').removeClass('d-none');
            }
            else
            {
                $('#maximum_bonus_amount').removeAttr("readonly");
                $('#percentage').removeClass('d-none');
                $('#cuttency_symbol').addClass('d-none');
            }
        });

        $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
        $('#date_to').attr('min',(new Date()).toISOString().split('T')[0]);

            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
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

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#dataSearch').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.users.customer.wallet.bonus.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#table-div').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
    <script>
        $('#reset_btn').click(function(){
            $('#module_select').val(null).trigger('change');
            $('#store_id').val(null).trigger('change');
            $('#store_wise').show();
            $('#zone_wise').hide();
        })

    </script>
    <script>
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
