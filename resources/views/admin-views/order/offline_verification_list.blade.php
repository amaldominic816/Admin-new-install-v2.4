@extends('layouts.admin.app')

@section('title',translate('messages.Order List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        @php($parcel_order = Request::is('admin/parcel/orders*'))
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-xl-12 col-md-12 col-sm-12 mb-3 mb-sm-0">
                    <h1 class="page-header-title text-capitalize m-0">
                        <span class="page-header-icon">
                            <img src="{{asset('public/assets/admin/img/fi_273177.svg')}}" class="w--26" alt="">
                        </span>
                        <span>
                        {{translate('messages.Verify_Offline_Payments')}}
                            <span class="badge badge-soft-dark ml-2">{{$orders->total()}}</span>
                        </span>
                    </h1>
                    <span class="badge badge-soft-danger mt-3 mb-3">{{ translate('For_offline_payments_please_verify_if_the_payments_are_safely_received_to_your_account._Customer_id_not_liable_if_you_confirm_and_deliver_the_orders_without_checking_payments_transactions')}} </span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <!-- Nav -->
                        <ul class="nav nav-tabs mb-3 border-0 nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $status ==  'all' ? 'active' : ''}}" href="{{ route('admin.order.offline_verification_list', ['all']) }}"   aria-disabled="true">{{translate('messages.All')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link  {{ $status ==  'pending' ? 'active' : ''}}" href="{{ route('admin.order.offline_verification_list', ['pending']) }}"  aria-disabled="true">{{translate('messages.Pending')}}</a>
                            </li>
                            <li class="nav-item  {{ $status ==  'verified' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ route('admin.order.offline_verification_list', ['verified']) }}"  aria-disabled="true">{{translate('messages.verified')}}</a>
                            </li>
                            <li class="nav-item  {{ $status ==  'denied' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ route('admin.order.offline_verification_list', ['denied']) }}"  aria-disabled="true">{{translate('messages.Denied')}}</a>
                            </li>
                        </ul>
                        <!-- End Nav -->
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-1 border-0">
                <div class="search--button-wrapper justify-content-end">
                    <form class="search-form min--260">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" name="search" class="form-control h--40px"
                                    placeholder="{{ translate('messages.Ex:') }} 10010" value="{{ request()?->search ?? null}}" aria-label="{{translate('messages.search')}}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>

                        </div>
                        <!-- End Search -->
                    </form>
                    <!-- Datatable Info -->
                    <div id="datatableCounterInfo" class="mr-2 mb-2 mb-sm-0 initial-hidden">
                        <div class="d-flex align-items-center">
                                <span class="font-size-sm mr-3">
                                <span id="datatableCounter">0</span>
                                {{translate('messages.selected')}}
                                </span>
                        </div>
                    </div>
                    <!-- End Datatable Info -->

                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle h--40px" href="javascript:;"
                            data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{translate('messages.export')}}
                        </a>

                        <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{translate('messages.options')}}</span>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                            <a id="export-excel" class="dropdown-item" href="javascript:;">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{asset('public/assets/admin')}}/svg/components/excel.svg"
                                        alt="Image Description">
                                {{translate('messages.excel')}}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="javascript:;">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{asset('public/assets/admin')}}/svg/components/placeholder-csv-format.svg"
                                        alt="Image Description">
                                .{{translate('messages.csv')}}
                            </a>

                        </div>
                    </div>

                    <!-- End Unfold -->
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="datatable"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px"
                        data-hs-datatables-options='{
                        "columnDefs": [{
                            "targets": [0],
                            "orderable": false
                        }],
                        "order": [],
                        "info": {
                        "totalQty": "#datatableWithPaginationInfoTotalQty"
                        },
                        "search": "#datatableSearch",
                        "entries": "#datatableEntries",
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging": false
                    }'>
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0">
                            {{translate('messages.sl')}}
                        </th>
                        <th class="table-column-pl-0 border-0">{{translate('messages.order_id')}}</th>
                        <th class="border-0">{{translate('messages.order_date')}}</th>
                        <th class="border-0">{{translate('messages.customer_information')}}</th>
                        <th class="border-0">{{translate('messages.total_amount')}}</th>
                        <th class="text-center border-0">{{translate('messages.Payment_Method')}}</th>
                        <th class="text-center border-0">{{translate('messages.actions')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($orders as $key=>$order)

                        <tr class="status-{{$order['order_status']}} class-all">
                            <td class="">
                                {{$key+$orders->firstItem()}}
                            </td>
                            <td class="table-column-pl-0">
                                <a href="{{route($parcel_order?'admin.parcel.order.details':'admin.order.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                            </td>
                            <td>
                                <div>
                                    <div>
                                        {{date('d M Y',strtotime($order['created_at']))}}
                                    </div>
                                    <div class="d-block text-uppercase">
                                        {{date(config('timeformat'),strtotime($order['created_at']))}}
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($order->customer)
                                    <a class="text-body text-capitalize" href="{{route('admin.customer.view',[$order['user_id']])}}">
                                        <strong>{{$order->customer['f_name'].' '.$order->customer['l_name']}}</strong>
                                        <div>{{$order->customer['phone']}}</div>
                                    </a>
                                @elseif($order->is_guest)
                                    @php($customer_details = json_decode($order['delivery_address'],true))
                                    <strong>{{$customer_details['contact_person_name']}}</strong>
                                    <div>{{$customer_details['contact_person_number']}}</div>
                                @else
                                    <label class="badge badge-danger">{{translate('messages.invalid_customer_data')}}</label>
                                @endif
                            </td>

                            <td>
                                <div class="text-right mw--85px">
                                    <div>
                                        {{\App\CentralLogics\Helpers::format_currency($order['order_amount'])}}
                                    </div>
                                </div>
                            </td>
                            <td class="text-capitalize text-center">
                                {{ json_decode($order->offline_payments->payment_info, true)['method_name'] }}
                            </td>
                            <td>
                                @if ($order?->offline_payments->status == 'pending')
                                    <div class="btn--container justify-content-center">
                                        <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal-{{ $key }}" >{{ translate('messages.Verify_Payment') }}</button>
                                    </div>

                                    @elseif($order?->offline_payments->status == 'verified')
                                    <div class="btn--container justify-content-center">
                                        <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal-{{ $key }}" >{{ translate('messages.verified') }}</button>
                                    </div>
                                    @elseif($order?->offline_payments->status == 'denied')
                                    <div class="btn--container justify-content-center">
                                        <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal-{{ $key }}" >{{ translate('messages.Recheck_Verification') }}</button>
                                    </div>
                                @endif

                            </td>
                        </tr>

                                <!-- End Card -->
        <div class="modal fade" id="verifyViewModal-{{ $key }}" tabindex="-1" aria-labelledby="verifyViewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-end  border-0">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                <div class="modal-body">
                <div class="d-flex align-items-center flex-column gap-3 text-center">
                    <h2>{{translate('Payment_Verification')}}
                        @if ($order?->offline_payments->status == 'verified')
                            <span class="badge badge-soft-success mt-3 mb-3">{{ translate('messages.verified') }}</span>
                        @endif
                    </h2>
                    <p class="text-danger mb-2 mt-2">{{ translate('Please_Check_&_Verify_the_payment_information_weather_it_is_correct_or_not_before_confirm_the_order.') }}</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">{{ translate('messages.customer_information') }}</h4>
                            <div class="d-flex flex-column gap-2">
                                @if($order->customer)
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{translate('Name')}}</span>:
                                    <span class="text-dark"> <a class="text-body text-capitalize" href="{{route('admin.customer.view',[$order['user_id']])}}"> {{$order->customer['f_name'].' '.$order->customer['l_name']}}  </a>  </span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <span>{{translate('Phone')}}</span>:
                                    <span class="text-dark">{{$order->customer['phone']}}  </span>
                                </div>

                                @elseif($order->is_guest)
                                    @php($customer_details = json_decode($order['delivery_address'],true))

                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{translate('Name')}}</span>:
                                        <span class="text-dark"> {{$customer_details['contact_person_name']}}</span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{translate('Phone')}}</span>:
                                        <span class="text-dark">  {{$customer_details['contact_person_number']}}</span>
                                    </div>

                                @else
                                    <label class="badge badge-danger">{{translate('messages.invalid_customer_data')}}</label>
                                @endif

                            </div>

                                <div class="mt-5">
                                    <h4 class="mb-3">{{ translate('messages.Payment_Information') }}</h4>
                                    <div class="row g-3">
                                        @foreach (json_decode($order->offline_payments->payment_info) as $key=>$item)
                                            @if ($key != 'method_id')
                                            <div class="col-sm-6  col-lg-5">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="w-sm-25"> {{translate($key)}}</span>:
                                                    <span class="text-dark text-break">{{ $item }}</span>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span>{{translate('Customer_Note')}}</span>:
                                            <span class="text-dark text-break">{{$order->offline_payments?->customer_note ?? translate('messages.N/A')}} </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($order?->offline_payments->status != 'verified')
                        <div class="btn--container justify-content-end mt-3">
                            @if ($order?->offline_payments->status != 'denied')
                            <button type="button" class="btn btn--danger btn-outline-danger offline_payment_cancelation_note" data-toggle="modal" data-target="#offline_payment_cancelation_note" data-id="{{ $order['id'] }}" class="btn btn--reset">{{translate('Payment_Didn’t_Recerive')}}</button>
                            @elseif ($order?->offline_payments->status == 'denied')
                                <button type="button" onclick="route_alert('{{ route('admin.order.offline_payment', [ 'id' => $order['id'], 'verify' => 'switched_to_cod', ]) }}','{{ translate('messages.Make_the_payment_verified_for_this_order') }}')" onclick="" class="btn btn--success mb-2">{{translate('Switched_to_COD')}}</button>
                            @endif

                            <button type="button" onclick="route_alert('{{ route('admin.order.offline_payment', [ 'id' => $order['id'], 'verify' => 'yes', ]) }}','{{ translate('messages.Make_the_payment_verified_for_this_order') }}')" onclick="" class="btn btn--primary mb-2">{{translate('Yes,_Payment_Received')}}</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- End Table -->
            @if(count($orders) !== 0)
            <hr>
            @endif
            <div class="page-area">
                {!! $orders->appends($_GET)->links() !!}
            </div>
            @if(count($orders) === 0)
            <div class="empty--data">
                <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                <h5>
                    {{translate('no_data_found')}}
                </h5>
            </div>
            @endif
        </div>

            <!-- Modal -->
    <div class="modal fade" id="offline_payment_cancelation_note" tabindex="-1" role="dialog"
    aria-labelledby="offline_payment_cancelation_note_l" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offline_payment_cancelation_note_l">{{ translate('messages.Add_Offline_Payment_Rejection_Note') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.order.offline_payment') }}" method="get">
                    <input type="hidden" name="id" id="myorderId">
                    <input type="text" required class="form-control" name="note" value="{{ old('note') }}"
                        placeholder="{{ translate('transaction_id_mismatched') }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{  translate('close') }}</button>
                <button type="submit" class="btn btn--danger btn-outline-danger">{{ translate('messages.Confirm_Rejection') }} </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- End Modal -->
@endsection

@push('script_2')
    <!-- <script src="{{asset('public/assets/admin')}}/js/bootstrap-select.min.js"></script> -->
    <script>

$(document).on("click", ".offline_payment_cancelation_note", function () {
    var myorderId = $(this).data('id');
     $(".modal-body #myorderId").val(myorderId);
});



        $(document).on('ready', function () {

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });




            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        className: 'd-none'
                    },
                    {
                        extend: 'excel',
                        className: 'd-none',
                        action: function (e, dt, node, config)
                        {
                            window.location.href = '{{route("admin.order.export",['status'=>$status,'file_type'=>'excel','type'=>$parcel_order?'parcel':'order', request()->getQueryString()])}}';
                        }
                    },
                    {
                        extend: 'csv',
                        className: 'd-none',
                        action: function (e, dt, node, config)
                        {
                            window.location.href = '{{route("admin.order.export",['status'=>$status,'file_type'=>'csv','type'=>$parcel_order?'parcel':'order', request()->getQueryString()])}}';
                        }
                    },
                    // {
                    //     extend: 'pdf',
                    //     className: 'd-none'
                    // },
                    {
                        extend: 'print',
                        className: 'd-none'
                    },
                ],
                select: {
                    style: 'multi',
                    selector: 'td:first-child input[type="checkbox"]',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: '<div class="text-center p-4">' +
                        '<img class="w-7rem mb-3" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="Image Description">' +

                        '</div>'
                }
            });

            $('#export-copy').click(function () {
                datatable.button('.buttons-copy').trigger()
            });

            $('#export-excel').click(function () {
                datatable.button('.buttons-excel').trigger()
            });

            $('#export-csv').click(function () {
                datatable.button('.buttons-csv').trigger()
            });

            // $('#export-pdf').click(function () {
            //     datatable.button('.buttons-pdf').trigger()
            // });

            $('#export-print').click(function () {
                datatable.button('.buttons-print').trigger()
            });

            $('#datatableSearch').on('mouseup', function (e) {
                var $input = $(this),
                    oldValue = $input.val();

                if (oldValue == "") return;

                setTimeout(function () {
                    var newValue = $input.val();

                    if (newValue == "") {
                        // Gotcha
                        datatable.search('').draw();
                    }
                }, 1);
            });

            $('#toggleColumn_order').change(function (e) {
                datatable.columns(1).visible(e.target.checked)
            })

            $('#toggleColumn_date').change(function (e) {
                datatable.columns(2).visible(e.target.checked)
            })

            $('#toggleColumn_customer').change(function (e) {
                datatable.columns(3).visible(e.target.checked)
            })

            $('#toggleColumn_total').change(function (e) {
                datatable.columns(5).visible(e.target.checked)
            })
            $('#toggleColumn_Payment_Method').change(function (e) {
                datatable.columns(6).visible(e.target.checked)
            })


            $('#toggleColumn_actions').change(function (e) {
                datatable.columns(7).visible(e.target.checked)
            })
            // INITIALIZATION OF TAGIFY
            // =======================================================
            $('.js-tagify').each(function () {
                var tagify = $.HSCore.components.HSTagify.init($(this));
            });

            $("#date_from").on("change", function () {
                $('#date_to').attr('min',$(this).val());
            });

            $("#date_to").on("change", function () {
                $('#date_from').attr('max',$(this).val());
            });
        });

    </script>

@endpush
