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
                            {{translate('messages.store_wallet')}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <?php
        $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        if(isset($wallet)==false){
            \Illuminate\Support\Facades\DB::table('store_wallets')->insert([
                'vendor_id'=>\App\CentralLogics\Helpers::get_vendor_id(),
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
            $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        }
        ?>
        @include('vendor-views.wallet.partials._balance_data',['wallet'=>$wallet])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatable"
                       class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true,
                                    "paging":false
                                }' >
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('messages.sl') }}</th>
                        <th>{{translate('messages.amount')}}</th>
                        <th>{{translate('messages.request_time')}}</th>
                        <th>{{translate('messages.disbursement_method')}}</th>
                        <th>{{translate('messages.Transaction_Type')}}</th>
                        <th>{{translate('messages.status')}}</th>
                        <th >{{translate('messages.note')}}</th>
                        <th class="w-5px">{{ translate('messages.Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($withdraw_req as $k=>$wr)

                        <tr>
                            <td scope="row">{{$k+$withdraw_req->firstItem()}}</td>
                            <td> {{ \App\CentralLogics\Helpers::format_currency($wr['amount'])}}</td>

                            <td>
                                <span class="d-block">{{ \App\CentralLogics\Helpers::time_date_format($wr['created_at'])}}</span>
                            </td>
                            <td>
                                @if($wr->method)

                                    <a href="#" data-toggle="modal" data-target="#exampleModal1-{{ $wr->id }}">
                                        {{translate($wr->method->method_name)}}</a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="exampleModal1-{{ $wr->id }}" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel"        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.disbursement_method_details')}}  </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>

                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        @foreach(json_decode($wr->withdrawal_method_fields, true) as $key=>$method_field)
                                                            <label class="mt-2"  for="{{$key}}">{{ translate($key)}}</label>
                                                            <input type="text" class="form-control" readonly value="{{ $method_field }}" id="{{$key}}">
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @else
                                    {{ translate('Default_method') }}
                                @endif

                            </td>
                            <td>
                                @if ($wr->type ==  'adjustment' )
                                    {{ translate('Wallet_Adjustment') }}
                                @elseif ($wr->type == 'manual' )
                                    {{ translate('Withdraw_Request') }}
                                @elseif ($wr->type == 'disbursement' )
                                    {{ translate('disbursement') }}
                                @else
                                    {{ translate($wr->type) }}
                                @endif
                            </td>
                            <td>
                                @if($wr->approved==0)
                                    <label class="badge badge-soft-info">{{translate('messages.pending')}}</label>
                                @elseif($wr->approved==1)
                                    <label class="badge badge-soft-success">{{translate('messages.approved')}}</label>
                                @else
                                    <label class="badge badge-soft-danger">{{translate('messages.denied')}}</label>
                                @endif
                            </td>


                            <td >
                                @if($wr->transaction_note )
                                    @if($wr->transaction_note == 'Store_wallet_adjustment_partial' )
                                        {!!     Str::limit(translate('Adjusted_Amount_Partially'), 20,
                                     '<a  href="#" onClick="javascript:showMyModal(\''.translate('Adjusted_Amount_Partially').'\')" >...Read more.</a>'
                                     ) !!}
                                    @elseif($wr->transaction_note == 'Store_wallet_adjustment_full' )
                                        {!!     Str::limit(translate('Adjusted_Amount'), 20,
                                   '<a  href="#" onClick="javascript:showMyModal(\''.translate('Adjusted_Amount').'\')" >...Read more.</a>'
                                   ) !!}

                                    @else
                                        {!!
                                   Str::limit(translate($wr->transaction_note), 20,
                                   '<a  href="#" onClick="javascript:showMyModal(\''.translate($wr->transaction_note).'\')" >...Read more.</a>'
                                   )  !!}
                                    @endif

                                @else
                                    {{ translate('messages.N/A') }}
                                @endif
                            </td>




                            <td>

                                @if($wr->approved==0)
                                    {{-- <a href="{{route('vendor.withdraw.close',[$wr['id']])}}"
                                        class="btn btn-outline-danger btn--danger action-btn">
                                        {{translate('messages.Delete')}}
                                    </a> --}}
                                    <a class="btn btn-outline-danger btn--danger action-btn" href="javascript:" onclick="form_alert('withdraw-{{$wr['id']}}','{{ translate('Want to delete this  ?') }}')" title="{{translate('messages.delete')}}"><i class="tio-delete-outlined"></i>
                                    </a>

                                    <form action="{{route('vendor.wallet.close-request',[$wr['id']])}}"
                                          method="post" id="withdraw-{{$wr['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                @else
                                    <label>{{translate('messages.complete')}}</label>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($withdraw_req) === 0)
                    <div class="empty--data">
                        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-footer pt-0 border-0">
            {{$withdraw_req->links()}}
        </div>
    </div>
    </div>
    </div>
    </div>


    <div class="modal fade" id="payment_model" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Pay_Via_Online')}}  </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <form action="{{ route('vendor.wallet.make_payment') }}" method="POST" class="needs-validation">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" value="{{ \App\CentralLogics\Helpers::get_store_id() }}" name="store_id"/>
                        <input type="hidden" value="{{ abs($wallet->collected_cash) }}" name="amount"/>
                        <h5 class="mb-5 ">{{ translate('Pay_Via_Online') }} &nbsp; <small>({{ translate('Faster_&_secure_way_to_pay_bill') }})</small></h5>
                        <div class="row g-3">
                            @forelse ($data as $item)
                                <div class="col-sm-6">
                                    <div class="d-flex gap-3 align-items-center">
                                        <input type="radio" required id="{{$item['gateway'] }}" name="payment_gateway" value="{{$item['gateway'] }}">
                                        <label for="{{$item['gateway'] }}" class="d-flex align-items-center gap-3 mb-0">
                                            <img height="24" src="{{ asset('storage/app/public/payment_modules/gateway_image/'. $item['gateway_image']) }}" alt="">
                                            {{ $item['gateway_title'] }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <h1>{{ translate('no_payment_gateway_found') }}</h1>
                            @endforelse
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
                        <button type="submit" class="btn btn-primary">{{ translate('Proceed') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="Adjust_wallet" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Adjust_Wallet')}}  </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <form action="{{ route('vendor.wallet.make_wallet_adjustment') }}" method="POST" class="needs-validation">
                    <div class="modal-body">
                        @csrf
                        <h5 class="mb-5 ">{{ translate('This_will_adjust_the_collected_cash_on_your_earning') }} </h5>
                    </div>

                    <div class="modal-footer">
                        <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
                        <button type="submit" class="btn btn-primary">{{ translate('Proceed') }}</button>
                    </div>
                </form>
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
                        <label for="wr_num" class="fz-16 c1 mb-2">${element.input_name.replaceAll('_', ' ')}</label>
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
