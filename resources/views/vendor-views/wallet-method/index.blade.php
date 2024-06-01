@extends('layouts.vendor.app')

@section('title',translate('messages.store_wallet'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{asset('/public/assets/admin/img/image_90.png')}}" alt="public">
                        </div>
                        <span>
                            {{translate('messages.disbursement_method_setup')}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h3 class="card-title">
                        {{ translate('disbursement_methods') }}<span class="badge badge-soft-secondary"  id="countfoods">{{ $vendor_withdrawal_methods->total() }}</span>
                    </h3>
                    <form >
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{ translate('Ex : Search by name') }}"  value="{{ request()?->search ?? null }}" aria-label="Search">

                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>
                </div>
                &nbsp;
                <div class="p--10px">
                    <a class="btn btn--primary btn-outline-primary w-100" href="javascript:" data-toggle="modal" data-target="#balance-modal">{{translate('messages.add_new_method')}}</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatable"
                           class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false
                        }'>
                        <thead class="thead-light">
                        <tr>
                            <th>{{ translate('messages.sl') }}</th>
                            <th>{{translate('messages.payment_method_name')}}</th>
                            <th>{{translate('messages.payment_info')}}</th>
                            <th>{{translate('messages.default')}}</th>
                            <th class="w-100px text-center">{{translate('messages.action')}}</th>
                        </tr>
                        </thead>
                        <tbody id="set-rows">
                        @foreach($vendor_withdrawal_methods as $k=>$e)
                            <tr>
                                <th scope="row">{{$k+$vendor_withdrawal_methods->firstItem()}}</th>
                                <td class="text-capitalize text-break text-hover-primary">{{$e['method_name']}}</td>
                                <td>
                                    <div class="col-md-8 mt-2">
                                        @forelse(json_decode($e->method_fields, true) as $key=> $item)
                                            <h5 class="text-capitalize "> {{  translate($key) }}: {{$item}}</h5>
                                        @empty
                                            <h5 class="text-capitalize"> {{translate('messages.No_Data_found')}}</h5>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <div>
                                            <label class="toggle-switch toggle-switch-sm mr-2" data-toggle="tooltip" data-placement="top" title="{{ translate('messages.make_default_method') }}" for="statusCheckbox{{$e->id}}">
                                                <input type="checkbox" onclick="location.href='{{route('vendor.wallet-method.default',[$e['id'],$e->is_default?0:1])}}'"class="toggle-switch-input" id="statusCheckbox{{$e->id}}" {{$e->is_default?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td>

                                    @if (auth('vendor_employee')->id()  != $e['id'])
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:"
                                               onclick="form_alert('employee-{{$e['id']}}','{{translate('messages.Want_to_delete_this_method_info')}}')" title="{{translate('messages.delete_method')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('vendor.wallet-method.delete',[$e['id']])}}"
                                                  method="post" id="employee-{{$e['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if(count($vendor_withdrawal_methods) === 0)
                        <div class="empty--data">
                            <img src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <div class="page-area">
                    <table>
                        <tfoot>
                        {!! $vendor_withdrawal_methods->links() !!}
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- Card -->


        <div class="modal fade" id="balance-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            {{translate('messages.add_method')}}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="btn btn--circle btn-soft-danger text-danger"><ti class="tio-clear"></ti></span>
                        </button>
                    </div>
                    <form action="{{route('vendor.wallet-method.store')}}" method="post">
                        <div class="modal-body">
                            @csrf
                            <div class="">
                                <select class="form-control" id="withdraw_method" name="withdraw_method" required>
                                    <option value="" selected disabled>{{translate('Select_Disburse_Method')}}</option>
                                    @foreach($withdrawal_methods as $item)
                                        <option value="{{$item['id']}}">{{$item['method_name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="" id="method-filed__div">
                            </div>
                        </div>
                        <div class="modal-footer pt-0 border-0">
                            <button type="button" class="btn btn--reset" data-dismiss="modal">{{translate('messages.cancel')}}</button>
                            <button type="submit" id="submit_button" disabled class="btn btn--primary">{{translate('messages.Submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



    </div>








@endsection
@push('script_2')
    <script>
        $('#withdraw_method').on('change', function () {
            $('#submit_button').attr("disabled","true");
            var method_id = this.value;

            // Set header if need any otherwise remove setup part
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('vendor.wallet.method-list')}}" + "?method_id=" + method_id,
                data: {},
                processData: false,
                contentType: false,
                type: 'get',
                success: function (response) {
                    $('#submit_button').removeAttr('disabled');
                    let method_fields = response.content.method_fields;
                    $("#method-filed__div").html("");
                    method_fields.forEach((element, index) => {
                        $("#method-filed__div").append(`
                    <div class="form-group mt-2">
                        <label for="wr_num" class="fz-16 c1 mb-2">${element.input_name.replaceAll('_', ' ').toUpperCase()}</label>
                        <input type="${element.input_type == 'phone' ? 'number' : element.input_type  }" class="form-control" name="${element.input_name}" placeholder="${element.placeholder}" ${element.is_required === 1 ? 'required' : ''}>
                    </div>
                `);
                    })

                },
                error: function () {

                }
            });
        });
    </script>

    <script>


        function showMyModal(data) {
            $(".modal-body #hiddenValue").html(data);
            $('#exampleModal').modal('show');
        }

    </script>
@endpush
