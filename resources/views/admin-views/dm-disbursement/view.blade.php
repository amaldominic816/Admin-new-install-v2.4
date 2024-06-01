@extends('layouts.admin.app')

@section('title',translate('messages.disbursement'))

@push('css_or_js')

@endpush

@section('content')


<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('/public/assets/admin/img/report/new/disburstment.png')}}" class="w--22" alt="">
            </span>
            <span>{{ translate('Disbursement_Details') }}</span>
        </h1>
    </div>
    <!-- Reports -->

    <div class="card">
        <div class="card-header flex-wrap justify-content-between gap-3">
            <div class="left">
                <h3 class="m-0 font-bold">{{ $disbursement->title }}
                    @if($disbursement->status=='pending')
                        <label class="badge badge-soft-primary">{{ translate('pending') }}</label>
                    @elseif($disbursement->status=='completed')
                        <label class="badge badge-soft-success">{{ translate('Completed') }}</label>
                    @elseif($disbursement->status=='partially_completed')
                        <label class="badge badge-soft-info">{{ translate('partially_completed') }}</label>
                    @else
                        <label class="badge badge-soft-danger">{{ translate('canceled') }}</label>
                    @endif
                </h3>
                <span>{{ translate('created_at') }} {{ \App\CentralLogics\Helpers::time_date_format($disbursement->created_at) }}</span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex flex-wrap align-items-center mr-2">
                    <span>{{ translate('total_amount') }}</span> <span class="mx-2">:</span> <h3 class="m-0">{{\App\CentralLogics\Helpers::format_currency($disbursement['total_amount'])}}</h3>
                </div>
                <div class="w-16rem">
                    <select name="delivery_man_id" onchange="set_delivery_man_filter('{{ url()->current() }}',this.value)"
                            data-placeholder="{{ translate('messages.select_delivery_man') }}"
                            class="js-select2-custom form-control">

                        <option value="all">{{ translate('messages.all_delivery_man') }}</option>
                        @foreach (\App\Models\DeliveryMan::where('type' ,'zone_wise')->where('earning' ,1)->active()->get(['id','f_name','l_name']) as $dm)
                            <option value="{{ $dm['id'] }}"
                                    {{ isset($delivery_man_id) && is_numeric($delivery_man_id) && ($delivery_man_id  == $dm['id']) ? 'selected' : '' }}>
                                {{ $dm['f_name'].' '.$dm['l_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-16rem">
                    <select name="payment_method_id" onchange="set_payment_method_filter('{{ url()->current() }}',this.value)"
                            data-placeholder="{{ translate('messages.select_payment_method') }}"
                            class="js-select2-custom form-control">

                        <option value="all">{{ translate('messages.all_payment_methods') }}</option>
                        @foreach (\App\Models\WithdrawalMethod::ofStatus(1)->get() as $method)
                            <option value="{{ $method['id'] }}"
                                {{ isset($payment_method_id) && is_numeric($payment_method_id) && ($payment_method_id  == $method['id']) ? 'selected' : '' }}>
                                {{ $method['method_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-header border-0 py-2">
            <div class="search--button-wrapper">
                <h2 class="card-title">
                    {{ translate('Total_Disbursements') }} <span class="badge badge-soft-secondary ml-2" id="countItems">{{ $disbursement_delivery_mans->total() }}</span>
                </h2>
                <form class="search-form">
                    <!-- Search -->
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input class="form-control" value="{{ request()?->search  ?? null }}" placeholder="{{ translate('search_by_delivery_man_info') }}" name="search">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
                <!-- Static Export Button -->
                <div class="hs-unfold ml-3">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                        data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                        <i class="tio-download-to mr-1"></i> {{translate('messages.export')}}
                    </a>
                    <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                        <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                        <a id="export-excel" class="dropdown-item" href="{{route('admin.transactions.dm-disbursement.export', ['id'=>$disbursement->id,'type'=>'excel',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('public/assets/admin')}}/svg/components/excel.svg" alt="Image Description">
                            {{translate('messages.excel')}}
                        </a>
                        <a id="export-csv" class="dropdown-item" href="{{route('admin.transactions.dm-disbursement.export', ['id'=>$disbursement->id,'type'=>'csv',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('public/assets/admin')}}/svg/components/placeholder-csv-format.svg" alt="Image Description">
                            {{translate('messages.csv')}}
                        </a>
                        <a id="export-pdf" class="dropdown-item" href="{{route('admin.transactions.dm-disbursement.export', ['id'=>$disbursement->id,'type'=>'pdf',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('public/assets/admin')}}/svg/components/pdf.svg" alt="Image Description">
                            {{translate('messages.pdf')}}
                        </a>
                    </div>
                </div>
                <!-- Static Export Button -->

                <!-- Action button after check table row -->
                <div id="action-section" class="d--none">
                    <button class="btn btn-danger btn-outline-danger" id="cancel">{{ translate('cancel') }}</button>
                    <button class="btn btn-success" id="complete">{{ translate('complete') }}</button>
                </div>
                <!-- Action button after check table row -->

            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-thead-bordered table-align-middle card-table">
                    <thead>
                        <tr>
                            <th>
                                <label class="form-check form--check mb-14">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </label>
                            </th>
                            <th>{{ translate('sl') }}</th>
                            <th>{{ translate('Delivery_Man_Info') }}</th>
                            <th>{{ translate('Disburse_Amount') }}</th>
                            <th>{{ translate('Payment_method') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th>
                                <div class="text-center">
                                    {{ translate('action') }}
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disbursement_delivery_mans as $key => $dm)
                            <tr>
                                <td>
                                    <label class="form-check form--check mb-14">
                                        <input type="checkbox" name="dm_ids[]" class="form-check-input rest-check" value="{{ $dm->delivery_man_id }}">
                                    </label>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ $key+$disbursement_delivery_mans->firstItem()  }}</span>
                                </td>
                                <td>
                                    <a href="{{route('admin.delivery-man.preview', $dm->delivery_man_id)}}"
                                       class="table-rest-info">
                                        <div class="info">
                                            <span class="d-block text-body">
                                                {{$dm->delivery_man->f_name.' '.$dm->delivery_man->l_name}}
                                            </span>
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    {{\App\CentralLogics\Helpers::format_currency($dm['disbursement_amount'])}}
                                </td>
                                <td>
                                    <div>
                                        {{$dm->withdraw_method->method_name}}
                                    </div>
                                </td>
                                <td>
                                    @if($dm->status=='pending')
                                        <label class="badge badge-soft-primary">{{ translate('pending') }}</label>
                                    @elseif($dm->status=='completed')
                                        <label class="badge badge-soft-success">{{ translate('Completed') }}</label>
                                    @else
                                        <label class="badge badge-soft-danger">{{ translate('canceled') }}</label>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn" data-toggle="modal" data-target="#payment-info-{{$dm->id}}" title="View Details">
                                            <i class="tio-visible"></i>
                                        </a>
                                        @if($dm->status == 'completed')
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn action-btn-section" href="{{route('admin.transactions.dm-disbursement.change-status', ['id'=>$dm->id,'status'=>'pending'])}}" data-toggle="tooltip" title="{{ translate('revert') }}">
                                                <i class="tio-restore"></i>
                                            </a>
                                        @else
                                            @if($dm->status != 'canceled')
                                                <a class="btn btn-sm btn--danger btn-outline-danger action-btn action-btn-section" href="{{route('admin.transactions.dm-disbursement.change-status', ['id'=>$dm->id,'status'=>'canceled'])}}" data-toggle="tooltip" title="{{ translate('cancel') }}">
                                                    <i class="tio-clear"></i>
                                                </a>
                                            @endif
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn action-btn-section" href="{{route('admin.transactions.dm-disbursement.change-status', ['id'=>$dm->id,'status'=>'completed'])}}" title="{{ translate('complete') }}" data-toggle="tooltip">
                                                <i class="tio-done"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <div class="modal fade" id="payment-info-{{$dm->id}}">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header pb-4">
                                                <button type="button" class="payment-modal-close btn-close border-0 outline-0 bg-transparent" data-dismiss="modal">
                                                    <i class="tio-clear"></i>
                                                </button>
                                                <div class="w-100 text-center">
                                                    <h2 class="mb-2">{{ translate('Payment_Information') }}</h2>
                                                    <div>
                                                        <span class="mr-2">{{ translate('Disbursement_ID') }}</span>
                                                        <strong>#{{$dm->disbursement_id}}</strong>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="mr-2">{{ translate('status') }}</span>
                                                        @if($dm->status=='pending')
                                                            <label class="badge badge-soft-primary">{{ translate('pending') }}</label>
                                                        @elseif($dm->status=='completed')
                                                            <label class="badge badge-soft-success">{{ translate('Completed') }}</label>
                                                        @else
                                                            <label class="badge badge-soft-danger">{{ translate('canceled') }}</label>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card shadow--card-2">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-wrap payment-info-modal-info p-xl-4">
                                                            <div class="item">
                                                                <h5>{{ translate('Delivery_Man_Information') }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('name') }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$dm->delivery_man->f_name.' '.$dm->delivery_man->l_name}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('contact') }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$dm?->delivery_man?->phone}}</strong>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="item">
{{--                                                                <h5>{{ translate('Owner_Information') }}</h5>--}}
{{--                                                                <ul class="item-list">--}}
{{--                                                                    <li class="d-flex flex-wrap">--}}
{{--                                                                        <span class="name">{{ translate('name') }}</span>--}}
{{--                                                                        <span>:</span>--}}
{{--                                                                        <strong>{{$dm->delivery_man->f_name}} {{$dm->delivery_man->l_name}}</strong>--}}
{{--                                                                    </li>--}}
{{--                                                                    <li class="d-flex flex-wrap">--}}
{{--                                                                        <span class="name">{{ translate('email') }}</span>--}}
{{--                                                                        <span>:</span>--}}
{{--                                                                        <strong>{{$dm->delivery_man->email}}</strong>--}}
{{--                                                                    </li>--}}
{{--                                                                </ul>--}}
                                                            </div>
                                                            <div class="item w-100">
                                                                <h5>{{ translate('Account_Information') }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('payment_method') }}</span>
                                                                        <strong>{{$dm->withdraw_method->method_name}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('amount') }}</span>
                                                                        <strong>{{\App\CentralLogics\Helpers::format_currency($dm['disbursement_amount'])}}</strong>
                                                                    </li>
                                                                    @forelse(json_decode($dm->withdraw_method->method_fields, true) as $key=> $item)
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{  translate($key) }}</span>
                                                                            <strong>{{$item}}</strong>
                                                                        </li>
                                                                    @empty

                                                                    @endforelse

                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 btn--container justify-content-end">
                                                    @if($dm->status =='pending')
                                                        <a type="button" href="{{route('admin.transactions.dm-disbursement.change-status', ['id'=>$dm->id,'status'=>'canceled'])}}" class="btn btn--reset" >{{ translate('cancel') }}</a>
                                                    @endif
                                                    @if($dm->status != 'completed')
                                                        <a type="button" href="{{route('admin.transactions.dm-disbursement.change-status', ['id'=>$dm->id,'status'=>'completed'])}}" class="btn btn--primary">{{ translate('complete') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (count($disbursement_delivery_mans) === 0)

                    <div class="empty--data">
                         <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                @endif
            </div>
        </div>
        <div class="page-area px-4 pb-3">
            <div class="d-flex align-items-center justify-content-end">
                <div>
                    {!!$disbursement_delivery_mans->links()!!}
                </div>
            </div>
        </div>
    </div>

</div>



@endsection

@push('script_2')
    <script>
        $(document).ready(function() {
            var disbursement_id = sessionStorage.getItem('disbursement_id');
            if(disbursement_id && (disbursement_id != {{$disbursement->id}})){
                sessionStorage.removeItem('selectedValues');
                sessionStorage.removeItem('selectedDmValues');
                sessionStorage.removeItem('disbursement_id');
            }
            var storedValues = sessionStorage.getItem('selectedDmValues');
            // Initialize as an array
            var checkedValues = storedValues ? JSON.parse(storedValues) : [];

            var delivery_man_ids = {{ $dm_ids }};

            if (checkedValues.length > 0) {
                $('#action-section').show();
                $('.action-btn-section').hide();
            } else {
                $('#action-section').hide();
                $('.action-btn-section').show();
            }

            if ((checkedValues.length > 0) && (checkedValues.length == delivery_man_ids.length)) {
                $('#select-all').prop('checked', true);
            }

            $('.rest-check').each(function() {
                var checkboxValue = parseInt($(this).val());
                if (checkedValues.includes(checkboxValue)) {
                    $(this).prop('checked', true);
                }
            });


            $('#select-all').on('click', function() {
                if (this.checked) {
                    $('.rest-check').each(function() {
                        this.checked = true;
                    });
                    checkedValues = delivery_man_ids;
                } else {
                    $('.rest-check').each(function() {
                        this.checked = false;
                    });
                    checkedValues = [];
                }
                saveSelectedValues();
            });

            $('.rest-check').on('click', function() {
                if ($('.rest-check:checked').length == $('.rest-check').length) {
                    $('#select-all').prop('checked', true);
                } else {
                    $('#select-all').prop('checked', false);
                }
                var value = parseInt($(this).val());
                console.log(value);
                if (this.checked) {
                    // Add the value to the array when the checkbox is checked
                    checkedValues.push(value);
                } else {
                    // Remove the value from the array when the checkbox is unchecked
                    var index = checkedValues.indexOf(value);
                    if (index !== -1) {
                        checkedValues.splice(index, 1);
                    }
                }
                saveSelectedValues();
                console.log(checkedValues);
            });

            function saveSelectedValues() {
                if (checkedValues.length > 0) {
                    $('#action-section').show();
                    $('.action-btn-section').hide();
                } else {
                    $('#action-section').hide();
                    $('.action-btn-section').show();
                }
                // Store the selected values in sessionStorage as a JSON string
                sessionStorage.setItem('selectedDmValues', JSON.stringify(checkedValues));
                sessionStorage.setItem('disbursement_id', {{$disbursement->id}});
            }

            $('#complete').on('click', function() {
                $.get({
                    url: '{{ route('admin.transactions.dm-disbursement.status') }}',
                    dataType: 'json',
                    data: {
                        disbursement_id: {{$disbursement->id}},
                        delivery_man_ids: checkedValues,
                        status: 'completed'
                    },
                    beforeSend: function() {
                        $('#loading').show();
                    },
                    success: function(response) {
                        checkedValues = [];
                        saveSelectedValues();
                        if (response.status == 'error') {
                            toastr.error(response.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }else if(response.status == 'success'){
                            toastr.success(response.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            location.reload();
                        }

                    },
                    complete: function() {
                        $('#loading').hide();
                    },
                });
            });
            $('#cancel').on('click', function() {
                $.get({
                    url: '{{ route('admin.transactions.dm-disbursement.status') }}',
                    dataType: 'json',
                    data: {
                        disbursement_id: {{$disbursement->id}},
                        delivery_man_ids: checkedValues,
                        status: 'canceled'
                    },
                    beforeSend: function() {
                        $('#loading').show();
                    },
                    success: function(response) {
                        if (response.status == 'error') {
                            toastr.error(response.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }else if(response.status == 'success'){
                            checkedValues = [];
                            saveSelectedValues();
                            toastr.success(response.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            location.reload();
                        }

                    },
                    complete: function() {
                        $('#loading').hide();
                    },
                });
            });
        });

    </script>
@endpush
