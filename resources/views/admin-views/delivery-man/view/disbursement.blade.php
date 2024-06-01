@extends('layouts.admin.app')

@section('title',translate('messages.Delivery Man Preview'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title text-break">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/delivery-man.png')}}" class="w--26" alt="">
                </span>
                <span>{{$dm['f_name'].' '.$dm['l_name']}}</span>
            </h1>
            <div class="row">
                <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                    <!-- Nav -->
                    <ul class="nav nav-tabs nav--tabs border-0">
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.users.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'info'])}}"  aria-disabled="true">{{translate('messages.info')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.users.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'transaction'])}}"  aria-disabled="true">{{translate('messages.transaction')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.users.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'conversation'])}}"  aria-disabled="true">{{translate('messages.conversations')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{route('admin.users.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'disbursement'])}}"  aria-disabled="true">{{translate('messages.disbursements')}}</a>
                        </li>
                    </ul>
                    <!-- End Nav -->
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3 mb-lg-5 mt-2">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">
                        {{ translate('Total_Disbursements') }} <span class="badge badge-soft-secondary ml-2" id="countItems">{{ $disbursements->total() }}</span>
                    </h5>
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input class="form-control" value="{{ request()?->search  ?? null }}" placeholder="{{ translate('search_by_disbursement_id') }}" name="search">
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
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.users.delivery-man.disbursement-export', ['id'=>$dm->id,'type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('public/assets/admin')}}/svg/components/excel.svg" alt="Image Description">
                                {{translate('messages.excel')}}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.users.delivery-man.disbursement-export', ['id'=>$dm->id,'type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('public/assets/admin')}}/svg/components/placeholder-csv-format.svg" alt="Image Description">
                                {{translate('messages.csv')}}
                            </a>
                        </div>
                    </div>
                    <!-- Static Export Button -->

                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-thead-bordered table-align-middle card-table">
                        <thead>
                        <tr>
                            <th>{{ translate('sl') }}</th>
                            <th>{{ translate('id') }}</th>
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
                        @foreach($disbursements as $key => $disbursement)
                            <tr>
                                <td>
                                    <span class="font-weight-bold">{{ $key+$disbursements->firstItem() }}</span>
                                </td>
                                <td>
                                    #{{ $disbursement->disbursement_id }}
                                </td>
                                <td>
                                    {{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}
                                </td>
                                <td>
                                    <div>
                                        {{$disbursement->withdraw_method->method_name}}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft-primary">{{$disbursement->status}}</span>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn" data-toggle="modal" data-target="#payment-info-{{$disbursement->id}}" title="View Details">
                                            <i class="tio-visible"></i>
                                        </a>
                                    </div>
                                </td>
                                <div class="modal fade" id="payment-info-{{$disbursement->id}}">
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
                                                        <strong>#{{$disbursement->disbursement_id}}</strong>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="mr-2">{{ translate('status') }}</span>
                                                        <span class="badge badge-soft-primary">{{$disbursement->status}}</span>
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
                                                                        <strong>{{$disbursement->delivery_man->f_name.' '.$disbursement->delivery_man->l_name}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('contact') }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$disbursement?->delivery_man?->phone}}</strong>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="item">
                                                                {{--                                                                <h5>{{ translate('Owner_Information') }}</h5>--}}
                                                                {{--                                                                <ul class="item-list">--}}
                                                                {{--                                                                    <li class="d-flex flex-wrap">--}}
                                                                {{--                                                                        <span class="name">{{ translate('name') }}</span>--}}
                                                                {{--                                                                        <span>:</span>--}}
                                                                {{--                                                                        <strong>{{$disbursement->restaurant->vendor->f_name}} {{$disbursement->restaurant->vendor->l_name}}</strong>--}}
                                                                {{--                                                                    </li>--}}
                                                                {{--                                                                    <li class="d-flex flex-wrap">--}}
                                                                {{--                                                                        <span class="name">{{ translate('email') }}</span>--}}
                                                                {{--                                                                        <span>:</span>--}}
                                                                {{--                                                                        <strong>{{$disbursement->restaurant->vendor->email}}</strong>--}}
                                                                {{--                                                                    </li>--}}
                                                                {{--                                                                </ul>--}}
                                                            </div>
                                                            <div class="item w-100">
                                                                <h5>{{ translate('Account_Information') }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('payment_method') }}</span>
                                                                        <strong>{{$disbursement->withdraw_method->method_name}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('amount') }}</span>
                                                                        <strong>{{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}</strong>
                                                                    </li>
                                                                    @forelse(json_decode($disbursement->withdraw_method->method_fields, true) as $key=> $item)
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
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if (count($disbursements) === 0)
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
                        {!!$disbursements->links()!!}
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script_2')
    <script>
        function request_alert(url, message) {
            Swal.fire({
                title: '{{ translate('Are_you_sure?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('no') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
@endpush
