@extends('layouts.admin.app')

@section('title', translate('messages.delivery_man_settings'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('business_setup')}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <!-- Page Header -->

        <!-- End Page Header -->
        <form action="{{ route('admin.business-settings.update-dm') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-4">
                                    @php($dm_tips_status = \App\Models\BusinessSetting::where('key', 'dm_tips_status')->first())
                                    @php($dm_tips_status = $dm_tips_status ? $dm_tips_status->value : 'deliveryman')
                                    <div class="form-group mb-0">

                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ translate('messages.Tips_for_Deliveryman') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.Customers_can_give_tips_to_deliverymen_during_checkout_from_the_Customer_App_&_Website._Admin_has_no_commission_on_it.') }}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.dm_tips_model_hint') }}"> * </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'dm_tips_status','dm-tips-on.png','dm-tips-off.png','{{translate('messages.Want_to_enable')}} <strong>{{translate('messages.Tips_for_Deliveryman_feature?')}}</strong>','{{translate('messages.Want_to_disable')}} <strong>{{translate('messages.Tips_for_Deliveryman_feature?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,_Customers_can_give_tips_to_a_deliveryman_during_checkout.')}}</p>`,`<p>{{translate('messages.If_you_disable_this,_the_Tips_for_Deliveryman_feature_will_be_hidden_from_the_Customer_App_and_Website.')}}</p>`)" class="toggle-switch-input" value="1"
                                                name="dm_tips_status" id="dm_tips_status"
                                                {{ $dm_tips_status == '1' ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    @php($show_dm_earning = \App\Models\BusinessSetting::where('key', 'show_dm_earning')->first())
                                    @php($show_dm_earning = $show_dm_earning ? $show_dm_earning->value : 0)
                                    <div class="form-group mb-0">

                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ translate('Show Earnings in App') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.With_this_feature,_Deliverymen_can_see_their_earnings_on_a_specific_order_while_accepting_it.') }}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.customer_varification_toggle') }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'show_dm_earning','show-earning-in-apps-on.png','show-earning-in-apps-off.png','{{translate('messages.Want_to_enable')}} <strong>{{translate('messages.Show_Earnings_in_App?')}}</strong>','{{translate('messages.Want_to_disable')}} <strong>{{translate('messages.Show_Earnings_in_App?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,_Deliverymen_can_see_their_earning_per_order_request_from_the_Order_Details_page_in_the_Deliveryman_App.')}}</p>`,`<p>{{translate('messages.If_you_disable_this,_the_feature_will_be_hidden_from_the_Deliveryman_App.')}}</p>`)"  class="toggle-switch-input" value="1"
                                                name="show_dm_earning" id="show_dm_earning"
                                                {{ $show_dm_earning == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    @php($dm_self_registration = \App\Models\BusinessSetting::where('key', 'toggle_dm_registration')->first())
                                    {{-- {{ dd($dm_self_registration) }} --}}
                                    @php($dm_self_registration = $dm_self_registration ? $dm_self_registration->value : 0)
                                    <div class="form-group mb-0">

                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ translate('messages.dm_self_registration') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.With_this_feature,_deliverymen_can_register_themselves_from_the_Customer_App,_Website_or_Deliveryman_App_or_Admin_Landing_Page._The_admin_will_receive_an_email_notification_and_can_accept_or_reject_the_request.') }}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.dm_self_registration') }}"> * </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'dm_self_registration1','dm-self-reg-on.png','dm-self-reg-off.png','{{translate('messages.Want_to_enable')}} <strong>{{translate('messages.Deliveryman_Self_Registration?')}}</strong>','{{translate('messages.Want_to_disable')}} <strong>{{translate('messages.Deliveryman_Self_Registration?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,_users_can_register_as_Deliverymen_from_the_Customer_App,_Website_or_Deliveryman_App_or_Admin_Landing_Page.')}}</p>`,`<p>{{translate('messages.If_you_disable_this,_this_feature_will_be_hidden_from_the_Customer_App,_Website_or_Deliveryman_App_or_Admin_Landing_Page.')}}</p>`)" class="toggle-switch-input" value="1"
                                                name="dm_self_registration" id="dm_self_registration1"
                                                {{ $dm_self_registration == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    @php($dm_maximum_orders = \App\Models\BusinessSetting::where('key', 'dm_maximum_orders')->first())
                                    <div class="form-group mb-0">
                                        <label class="form-label text-capitalize"
                                            for="dm_maximum_orders">
                                            <div class="d-flex align-items-center">
                                                <span class="line--limit-1 flex-grow">{{ translate('Maximum Assigned Order Limit') }} </span> <small
                                                class="text-danger d-flex align-items-center mt-1"> *<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.Set_the_maximum_order_limit_a_Deliveryman_can_take_at_a_time.') }}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.dm_maximum_order_hint') }}"></span>
                                                </small>
                                            </div>
                                        </label>
                                        <input type="number" name="dm_maximum_orders" class="form-control"
                                            id="dm_maximum_orders" min="1"
                                            value="{{ $dm_maximum_orders ? $dm_maximum_orders->value : 1 }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    @php($canceled_by_deliveryman = \App\Models\BusinessSetting::where('key', 'canceled_by_deliveryman')->first())
                                    @php($canceled_by_deliveryman = $canceled_by_deliveryman ? $canceled_by_deliveryman->value : 0)
                                    <div class="form-group mb-0">
                                        <label class="input-label text-capitalize d-flex alig-items-center"><span
                                                class="line--limit-1">{{ translate('messages.Can_A_Deliveryman_Cancel_Order?') }}</span>
                                            <span class="form-label-secondary"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Admin_can_enable/disable_Deliveryman’s_order_cancellation_option_in_the_respective_app.') }}"><img
                                                src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                alt="{{ translate('messages.dm_cancel_order_hint') }}"></span></label>
                                        <div class="resturant-type-group border">
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="1"
                                                    name="canceled_by_deliveryman" id="canceled_by_deliveryman"
                                                    {{ $canceled_by_deliveryman == 1 ? 'checked' : '' }}>
                                                <span class="form-check-label">
                                                    {{ translate('yes') }}
                                                </span>
                                            </label>
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="0"
                                                    name="canceled_by_deliveryman" id="canceled_by_deliveryman2"
                                                    {{ $canceled_by_deliveryman == 0 ? 'checked' : '' }}>
                                                <span class="form-check-label">
                                                    {{ translate('no') }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4 mt-5">
                                    @php($dm_picture_upload_status = \App\Models\BusinessSetting::where('key', 'dm_picture_upload_status')->first())
                                    @php($dm_picture_upload_status = $dm_picture_upload_status ? $dm_picture_upload_status->value : 0)

                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ translate('messages.Take_Picture_For_Completing_Delivery') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.If_enabled,_deliverymen_will_see_an_option_to_take_pictures_of_the_delivered_products_when_he_swipes_the_delivery_confirmation_slide.') }}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.dm_picture_upload_status') }}"> * </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'dm_picture_upload_status','dm-self-reg-on.png','dm-self-reg-off.png','{{translate('messages.Want_to_enable')}} <strong>{{translate('messages.picture_upload_before_complete?')}}</strong>','{{translate('messages.Want_to_disable')}} <strong>{{translate('messages.picture_upload_before_complete?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,_delivery_man_can_upload_order_proof_before_order_delivery.')}}</p>`,`<p>{{translate('messages.If_you_disable_this,_this_feature_will_be_hidden_from_the_delivery_man_app.')}}</p>`)" class="toggle-switch-input" value="1"
                                                name="dm_picture_upload_status" id="dm_picture_upload_status"
                                                {{ $dm_picture_upload_status == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>




                                <div class="col-sm-6 col-lg-4">
                                    @php($cash_in_hand_overflow = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_delivery_man')->first())
                                    @php($cash_in_hand_overflow = $cash_in_hand_overflow ? $cash_in_hand_overflow->value : 0)
                                    <div class="form-label  mb-0 ">
                                        <label>&nbsp;</label>
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ translate('messages.Cash_In_Hand_Overflow') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                      data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('If_enabled,_delivery_men_will_be_automatically_suspended_by_the_system_when_their_‘Cash_in_Hand’_limit_is_exceeded.') }}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.cash_in_hand_overflow') }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'cash_in_hand_overflow','show-earning-in-apps-on.png','show-earning-in-apps-off.png','{{translate('Want_to_enable')}} <strong>{{translate('Cash_In_Hand_Overflow')}}</strong> ?','{{translate('Want_to_disable')}} <strong>{{translate('Cash_In_Hand_Overflow')}}</strong>  ?',`<p>{{translate('If_enabled,_delivery_men_have_to_provide_collected_cash_by_them_self')}}</p>`,`<p>{{translate('If_disabled,_delivery_men_do_not_have_to_provide_collected_cash_by_them_self')}}</p>`)" class="toggle-switch-input" value="1"
                                                   name="cash_in_hand_overflow_delivery_man" id="cash_in_hand_overflow"
                                                {{ $cash_in_hand_overflow == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    @php($dm_max_cash_in_hand = \App\Models\BusinessSetting::where('key', 'dm_max_cash_in_hand')->first())
                                    <div class="form-label mb-0">
                                        <label class="d-flex text-capitalize"
                                               for="dm_max_cash_in_hand">
                                            <span class="line--limit-1">
                                                {{translate('Delivery_Man_Maximum_Cash_in_Hand')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            </span>
                                            <span data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Deliveryman_can_not_accept_any_orders_when_the_Cash_In_Hand_limit_exceeds_and_must_deposit_the_amount_to_the_admin_before_accepting_new_orders')}}" class="input-label-secondary"><img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('messages.dm_maximum_order_hint') }}"></span>
                                        </label>
                                        <input type="number" name="dm_max_cash_in_hand" class="form-control"
                                               id="dm_max_cash_in_hand" min="0" step=".001"
                                               value="{{ $dm_max_cash_in_hand ? $dm_max_cash_in_hand->value : '' }}" {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                    </div>
                                </div>



                                <div class="col-sm-6 col-lg-4">
                                    @php($min_amount_to_pay_dm = \App\Models\BusinessSetting::where('key', 'min_amount_to_pay_dm')->first())
                                    <div class="form-label mb-0">
                                        <label class="text-capitalize"
                                               for="min_amount_to_pay_dm">
                                            <span>
                                                {{ translate('Minimum_Amount_To_Pay') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})

                                            </span>

                                            <span class="form-label-secondary"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ translate('Enter_the_minimum_cash_amount_delivery_men_can_pay') }}"><img
                                                    src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ translate('messages.dm_cancel_order_hint') }}"></span>
                                        </label>
                                        <input type="number" name="min_amount_to_pay_dm" class="form-control"
                                               id="min_amount_to_pay_dm" min="0" step=".001"
                                               value="{{ $min_amount_to_pay_dm ? $min_amount_to_pay_dm->value : '' }}"  {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                    </div>
                                </div>





                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{ translate('messages.reset') }}</button>
                                <button type="submit" id="submit" class="btn btn--primary">{{ translate('messages.save_information') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('script_2')
    <script>
        $(document).on('ready', function() {
            @if (isset($data['wallet_status']) && $data['wallet_status'] != 1)
                $('.wallet-section').hide();
            @endif
            @if (isset($data['loyalty_point_status']) && $data['loyalty_point_status'] != 1)
                $('.loyalty-point-section').hide();
            @endif
            @if (isset($data['ref_earning_status']) && $data['ref_earning_status'] != 1)
                $('.referrer-earning').hide();
            @endif

            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));
            $('#column1_search').on('keyup', function() {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function() {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });
        });
    </script>

    <script>
        function section_visibility(id) {
            console.log($('#' + id).data('section'));
            if ($('#' + id).is(':checked')) {
                console.log('checked');
                $('.' + $('#' + id).data('section')).show();
            } else {
                console.log('unchecked');
                $('.' + $('#' + id).data('section')).hide();
            }
        }
        $('#add_fund').on('submit', function(e) {

            e.preventDefault();
            var formData = new FormData(this);

            Swal.fire({
                title: '{{ translate('messages.are_you_sure') }}',
                text: '{{ translate('messages.you_want_to_add_fund') }}' + $('#amount').val() +
                    ' {{ \App\CentralLogics\Helpers::currency_code() . ' ' . translate('messages.to') }} ' + $(
                        '#customer option:selected').text() + '{{ translate('messages.to_wallet') }}',
                type: 'info',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: 'primary',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.send') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: '{{ route('admin.customer.wallet.add-fund') }}',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(data) {
                            if (data.errors) {
                                for (var i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                toastr.success(
                                    '{{ translate('messages.fund_added_successfully') }}', {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                            }
                        }
                    });
                }
            })
        })
    </script>
        <script>
            $('#reset_btn').click(function(){
                location.reload(true);
            })
        </script>
@endpush
