<div id="headerMain" class="d-none">
    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-nav-wrap-content-left  d-xl-none">
                <!-- Navbar Vertical Toggle -->
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                       data-placement="right" title="Collapse"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                       data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                       data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
                <!-- End Navbar Vertical Toggle -->
            </div>

            <!-- Secondary Content -->
            <div class="navbar-nav-wrap-content-right">
                <!-- Navbar -->
                <ul class="navbar-nav align-items-center flex-row">
                    <li class="nav-item max-sm-m-0">
                        <div class="hs-unfold">
                            <div>
                                @php($local = session()->has('vendor_local')?session('vendor_local'):'en')
                                @php($lang = \App\Models\BusinessSetting::where('key', 'system_language')->first())
                                @if ($lang)
                                <div
                                    class="topbar-text dropdown disable-autohide text-capitalize d-flex">
                                    <a class="topbar-link dropdown-toggle d-flex align-items-center title-color"
                                    href="#" data-toggle="dropdown">
                                    @foreach(json_decode($lang['value'],true) as $data)
                                    @if($data['code']==$local)
                                    <i class="tio-globe"></i>
                                                {{-- <img
                                                     width="20"
                                                     src="{{asset('public/assets/admin')}}/img/flags/{{$data['code']}}.png"
                                                     alt="Eng"> --}}
                                                {{$data['code']}}
                                            @endif
                                        @endforeach
                                    </a>
                                    <ul class="dropdown-menu lang-menu">
                                        @foreach(json_decode($lang['value'],true) as $key =>$data)
                                            @if($data['status']==1)
                                                <li>
                                                    <a class="dropdown-item py-1"
                                                       href="{{route('vendor.lang',[$data['code']])}}">
                                                        {{-- <img

                                                            width="20"
                                                            src="{{asset('public/assets/admin')}}/img/flags/{{$data['code']}}.png"
                                                            alt="{{$data['code']}}"/> --}}
                                                        <span class="text-capitalize">{{$data['code']}}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block mr-4">
                        <!-- Notification -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-soft-secondary rounded-circle"
                               href="{{route('vendor.message.list')}}">
                                <i class="tio-messages-outlined"></i>
                                @php($message=\App\Models\Conversation::whereUser(\App\CentralLogics\Helpers::get_loggedin_user()->id)->where('unread_message_count','>','0')->count())
                                @if($message!=0)
                                    <span class="btn-status btn-sm-status btn-status-danger"></span>
                                @endif
                            </a>
                        </div>
                        <!-- End Notification -->
                    </li>



                    <li class="nav-item">
                        <!-- Account -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;"
                               data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>
                                <div class="cmn--media right-dropdown-icon d-flex align-items-center">
                                    <div class="media-body pl-0 pr-2">
                                        <span class="card-title h5 text-right">
                                            {{\App\CentralLogics\Helpers::get_loggedin_user()->f_name}}
                                            {{\App\CentralLogics\Helpers::get_loggedin_user()->l_name}}
                                        </span>
                                        <span class="card-text">{{\App\CentralLogics\Helpers::get_loggedin_user()->email}}</span>
                                    </div>
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img"
                                            onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                            src="{{asset('storage/app/public/vendor')}}/{{\App\CentralLogics\Helpers::get_loggedin_user()->image}}"
                                            alt="Image Description">
                                        <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                    </div>
                                </div>
                            </a>

                            <div id="accountNavbarDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account min--240">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img"
                                                 onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                                 src="{{asset('storage/app/public/vendor')}}/{{\App\CentralLogics\Helpers::get_loggedin_user()->image}}"
                                                 alt="Owner image">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{\App\CentralLogics\Helpers::get_loggedin_user()->f_name}}</span>
                                            <span class="card-text">{{\App\CentralLogics\Helpers::get_loggedin_user()->email}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="{{route('vendor.profile.view')}}">
                                    <span class="text-truncate pr-2" title="Settings">{{translate('messages.settings')}}</span>
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="javascript:" onclick="Swal.fire({
                                    title: '{{ translate('Do you want to logout?') }}',
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonColor: '#FC6A57',
                                    cancelButtonColor: '#363636',
                                    confirmButtonText: '{{ translate('messages.Yes') }}',
                                    cancelButtonText: '{{ translate('messages.cancel') }}',
                                    }).then((result) => {
                                    if (result.value) {
                                        location.href='{{route('logout')}}';
                                    } else{
                                    Swal.fire('{{ translate('messages.cancel') }}', '', 'info')
                                    }
                                    })">
                                    <span class="text-truncate pr-2" title="Sign out">{{translate('messages.sign_out')}}</span>
                                </a>
                            </div>
                        </div>
                        <!-- End Account -->
                    </li>
                </ul>
                <!-- End Navbar -->
            </div>
            <!-- End Secondary Content -->
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>
<?php
$wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
$Payable_Balance = $wallet?->balance  < 0 ? 1: 0;

$cash_in_hand_overflow=  \App\Models\BusinessSetting::where('key' ,'cash_in_hand_overflow_store')->first()?->value;
$cash_in_hand_overflow_store_amount =  \App\Models\BusinessSetting::where('key' ,'cash_in_hand_overflow_store_amount')->first()?->value;
$val=  $cash_in_hand_overflow_store_amount - (($cash_in_hand_overflow_store_amount * 10)/100);
?>

@if ($Payable_Balance == 1 &&  $cash_in_hand_overflow &&  $wallet?->balance < 0 &&  $val <=  abs($wallet?->balance)  &&  $cash_in_hand_overflow_store_amount >= abs($wallet?->balance))
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="rounded mr-1"  width="25" src="{{ asset('/public/assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ translate('Attention_Please') }} </h4>
            {{ translate('The_Cash_in_Hand_amount_is_about_to_exceed_the_limit._Please_pay_the_due_amount._If_the_limit_exceeds,_your_account_will_be_suspended.') }}
        </div>
    </div>
@endif

@if ($Payable_Balance == 1 &&  $cash_in_hand_overflow &&  $wallet?->balance < 0 &&  $cash_in_hand_overflow_store_amount < abs($wallet?->balance))
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="mr-1"  width="25" src="{{ asset('/public/assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ translate('Attention_Please') }} </h4>{{ translate('The_Cash_in_Hand_amount_limit_is_exceeded._Your_account_is_now_suspended._Please_pay_the_due_amount_to_receive_new_order_requests_again.') }}<a href="{{ route('vendor.wallet.index') }}" class="alert-link"> &nbsp; {{ translate('Pay_the_due') }}</a>
        </div>
    </div>
@endif

