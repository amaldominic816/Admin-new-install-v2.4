
<div class="row g-3">
    <?php

    $disbursement_type = \App\Models\BusinessSetting::where('key' , 'disbursement_type')->first()->value ?? 'manual';
    $min_amount_to_pay_store = \App\Models\BusinessSetting::where('key' , 'min_amount_to_pay_store')->first()->value ?? 0;

    $wallet_earning =  $wallet->total_earning - ($wallet->total_withdrawn + $wallet->pending_withdraw);

    if($wallet->balance > 0 && $wallet->collected_cash > 0 ){
        $adjust_able = true;
    } elseif($wallet->collected_cash != 0 && $wallet_earning !=  0 ){
        $adjust_able = true;
    } elseif($wallet->balance ==  $wallet_earning  ){
        $adjust_able = false;
    }
    else{
        $adjust_able = false;
    }
    ?>

    @if($adjust_able ==  true  || ($disbursement_type ==  'manual' && $wallet->balance > 0) || $wallet->balance < 0 || ( $wallet->collected_cash > 0 && $min_amount_to_pay_store <= $wallet->collected_cash ))
            <?php
            $col_size = true;
            ?>
    @endif



    <!-- Store Wallet Balance -->
    <div class="col-md-12">
        <div class="row g-3">
            <!-- Panding Withdraw Card Example -->
            <div class="col-sm-{{ isset($col_size) == true ? '3' :'4' }}">
                <div class="resturant-card shadow--card-2" >
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->collected_cash)}}</h4>

                    <div class="d-flex gap-1 align-items-center">
                                    <span class="subtitle">{{translate('messages.Cash_in_Hand')}}
                                    </span>

                        <span class="form-label-secondary text-danger d-flex"
                              data-toggle="tooltip" data-placement="right"
                              data-original-title="{{ translate('The_total_amount_you’ve_received_from_the_customer_in_cash_(Cash_on_Delivery)')}}"><img
                                src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                alt="{{ translate('messages.Take_Picture_For_Completing_Delivery') }}"> </span>
                        <img class="resturant-icon" src="{{asset('/public/assets/admin/img/transactions/image_total89.png')}}" alt="public">

                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-sm-{{ isset($col_size)  == true ? '3' :'4' }}">
                <div class="resturant-card shadow--card-2">
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->balance > 0 ? $wallet->balance: 0 )}}</h4>
                    <span class="subtitle">{{translate('messages.withdraw_able_balance')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/transactions/image_w_balance.png')}}" alt="public">
                </div>
            </div>
            <!-- Pending Requests Card Example -->
            <div class="col-sm-{{ isset($col_size) == true ? '6' :'4' }}">
                <div class="resturant-card shadow--card-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>

                            @if ($wallet->balance > 0)
                                <h4 class="title">{{\App\CentralLogics\Helpers::format_currency(abs($wallet_earning))}}</h4>


                                @if( $wallet->balance ==  $wallet_earning )

                                    <span class="subtitle">{{ translate('messages.Withdrawable_Balance') }}</span>
                                @else
                                    <span class="subtitle">{{ translate('messages.Balance') }}
                                            <small>{{translate('Unadjusted')}} </small>
                                        </span>
                                @endif

                            @else
                                <h4 class="title">{{\App\CentralLogics\Helpers::format_currency(abs($wallet->collected_cash))}}</h4>
                                <span class="subtitle">{{  translate('messages.Payable_Balance')}}</span>

                            @endif


                        </div>

                        @if($wallet->balance > 0  )
                            <div class="d-flex gap-2 flex-wrap">
                                @if ($adjust_able ==  true )
                                    <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:" data-toggle="modal" data-target="#Adjust_wallet">{{translate('messages.Adjust_with_wallet')}}

                                        <span class="form-label-secondary d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ translate('Adjust_the_withdrawable_balance_&_unadjusted_balance_with_your_wallet_(Cash_in_Hand)_or_click_‘Request_Withdraw’')}}">
                                        <i class="tio-info-outined"> </i>

                                        </span>

                                    </a>
                                @endif

                                @if ($disbursement_type ==  'manual' )
                                    <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap" href="javascript:" data-toggle="modal" data-target="#balance-modal">{{translate('messages.request_withdraw')}}

                                        <span class="form-label-secondary  d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ translate('As_you_have_more_‘Withdrawable_Balance’_than_‘Cash_in_Hand’,_you_need_to_request_for_withdrawal_from_Admin')}}">
                                            <i class="tio-info-outined"> </i> </span>
                                    </a>
                                @endif
                            </div>
                        @elseif($wallet->balance < 0 ||  $wallet->collected_cash > 0)
                            <div class="d-flex gap-2 flex-wrap">

                                @if ($adjust_able ==  true )
                                    <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:" data-toggle="modal" data-target="#Adjust_wallet">{{translate('messages.Adjust_with_wallet')}}

                                        <span class="form-label-secondary  d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ translate('As_you_have_more_‘Cash_in_Hand’_than_‘Withdrawable_Balance,’_you_need_to_pay_the_Admin')}}"> <i class="tio-info-outined"> </i> </span> </span>
                                    </a>
                                @endif

                                @if ($min_amount_to_pay_store <= $wallet->collected_cash)
                                    <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:" data-toggle="modal" data-target="#payment_model">{{translate('messages.Pay_Now')}}

                                        <span class="form-label-secondary  d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ translate('Adjust_the_payable_&_withdrawable_balance_with_your_wallet_(Cash_in_Hand)_or_click_‘Pay_Now’.')}}"> <i class="tio-info-outined"> </i> </span> </span></a>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="col-md-12">
        <div class="row g-3">
            <!-- Panding Withdraw Card Example -->
            <div class="col-sm-4">
                <div class="resturant-card  bg--3" >
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->pending_withdraw)}}</h4>
                    <span class="subtitle">{{translate('messages.pending_withdraw')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/transactions/image_pending.png')}}" alt="public">
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-sm-4">
                <div class="resturant-card  bg--2">
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->total_withdrawn)}}</h4>
                    <span class="subtitle">{{translate('messages.Total_Withdrawn')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/transactions/image_withdaw.png')}}" alt="public">
                </div>
            </div>


            <!-- Pending Requests Card Example -->
            <div class="col-sm-4">
                <div class="resturant-card  bg--1">
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->total_earning)}}</h4>
                    <span class="subtitle">{{translate('messages.total_earning')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/transactions/image_total89.png')}}" alt="public">
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="balance-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    {{translate('messages.withdraw_request')}}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="btn btn--circle btn-soft-danger text-danger"><i class="tio-clear"></i></span>
                </button>
            </div>

            <form action="{{route('vendor.wallet.withdraw-request')}}" method="post">
                <div class="modal-body">
                    @csrf
                    <div class="">
                        <select class="form-control" id="withdraw_method" name="withdraw_method" required>
                            <option value="" selected disabled>{{translate('Select_Withdraw_Method')}}</option>
                            @foreach($withdrawal_methods as $item)
                                <option value="{{$item['id']}}">{{$item['method_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="" id="method-filed__div">
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="form-label">{{translate('messages.amount')}}:</label>
                        <input type="number" name="amount"  step="0.01"
                               value="{{abs($wallet->balance)}}"
                               class="form-control h--45px" id="" min="1" max="{{abs($wallet->balance)}}">
                    </div>
                </div>
                <div class="modal-footer pt-0 border-0">
                    <button type="button" class="btn btn--reset" data-dismiss="modal">{{translate('messages.cancel')}}</button>
                    <button type="submit" id="submit_button" class="btn btn--primary">{{translate('messages.Submit')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"        aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Note')}}:  </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>
            <div class="modal-body">

                <div class="form-group">
                    {{-- <label for="hiddenValue" class="mb-2">{{ translate('messages.Note') }}</label> --}}
                    <p  id="hiddenValue"> </p>
                </div>
            </div>
            <div class="modal-footer">
                <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
            </div>
        </div>
    </div>
</div>
<!-- Content Row -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">

                <ul class="nav nav-tabs page-header-tabs pb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('store-panel/wallet') ?'active':''}}"  href="{{ route('vendor.wallet.index') }}">{{translate('messages.withdraw_request')}}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link  {{Request::is('store-panel/wallet/wallet-payment-list') ?'active':''}}" href="{{route('vendor.wallet.wallet_payment_list')}}"  aria-disabled="true">{{translate('messages.Payment_history')}}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link  {{Request::is('store-panel/wallet/disbursement-list') ?'active':''}}" href="{{route('vendor.wallet.getDisbursementList')}}"  aria-disabled="true">{{translate('messages.Next_Payouts')}}</a>
                    </li>
                </ul>

            </div>
