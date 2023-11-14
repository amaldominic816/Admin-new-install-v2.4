@extends('layouts.admin.app')

@section('title', translate('store_setup'))


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ translate('messages.business_setup') }}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <form action="{{ route('admin.business-settings.update-store') }}" method="post" enctype="multipart/form-data">
            @csrf
            @php($name = \App\Models\BusinessSetting::where('key', 'business_name')->first())

            <div class="row g-3">
                @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
                @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-4 col-sm-6">
                                    @php($canceled_by_store = \App\Models\BusinessSetting::where('key', 'canceled_by_store')->first())
                                    @php($canceled_by_store = $canceled_by_store ? $canceled_by_store->value : 0)
                                    <div class="form-group mb-0">
                                        <label class="input-label text-capitalize d-flex alig-items-center"><span
                                                class="line--limit-1">{{ translate('messages.Can_a_Store_Cancel_Order?') }}
                                            </span><span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('messages.Admin_can_enable/disable_Store’s_order_cancellation_option.') }}">
                                                <i class="tio-info-outined"></i>
                                            </span></label>
                                        <div class="resturant-type-group border">
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="1"
                                                    name="canceled_by_store" id="canceled_by_restaurant"
                                                    {{ $canceled_by_store == 1 ? 'checked' : '' }}>
                                                <span class="form-check-label">
                                                    {{ translate('yes') }}
                                                </span>
                                            </label>
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="0"
                                                    name="canceled_by_store" id="canceled_by_restaurant2"
                                                    {{ $canceled_by_store == 0 ? 'checked' : '' }}>
                                                <span class="form-check-label">
                                                    {{ translate('no') }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    @php($store_self_registration = \App\Models\BusinessSetting::where('key', 'toggle_store_registration')->first())
                                    @php($store_self_registration = $store_self_registration ? $store_self_registration->value : 0)
                                    <div class="form-group mb-0">

                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ translate('messages.store_self_registration') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.A_store_can_send_a_registration_request_through_their_store_or_customer.') }}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.store_self_registration') }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'store_self_registration1','store-self-reg-on.png','store-self-reg-off.png','{{translate('messages.Want_to_enable')}} <strong>{{translate('messages.Store_Self_Registration?')}}</strong>','{{translate('messages.Want_to_disable')}} <strong>{{translate('messages.Store_Self_Registration?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,_Stores_can_do_self-registration_from_the_store_or_customer_app_or_website.')}}</p>`,`<p>{{translate('messages.If_you_disable_this,_the_Store_Self-Registration_feature_will_be_hidden_from_the_store_or_customer_app,_website,_or_admin_landing_page.')}}</p>`)" class="toggle-switch-input" value="1"
                                                name="store_self_registration" id="store_self_registration1"
                                                {{ $store_self_registration == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-4">
                                    @php($product_gallery = \App\Models\BusinessSetting::where('key', 'product_gallery')->first()?->value ?? 0)
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{translate('messages.Product_Gallery') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.If_enabled,can_create_duplicate_products.')}}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.Product_Gallery') }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'product_gallery','store-reg-on.png','store-reg-off.png','<strong>{{translate('messages.Want_to_enable_product_gallery?')}}</strong>','<strong>{{translate('messages.Want_to_disable_product_gallery?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,can_create_duplicate_products')}}</p>`,`<p>{{translate('messages.If_you_disable_this,can_not_create_duplicate_products.')}}</p>`)" class="status toggle-switch-input" value="1"
                                                name="product_gallery" id="product_gallery"
                                                {{ $product_gallery == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4 {{ $product_gallery == 1 ? ' ' : 'd-none' }}  access_all_products">
                                    @php($access_all_products = \App\Models\BusinessSetting::where('key', 'access_all_products')->first()?->value ?? 0)
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{translate('messages.access_all_products') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.If_enabled,can_create_duplicate_products.')}}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.access_all_products') }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'access_all_products','store-reg-on.png','store-reg-off.png','<strong>{{translate('messages.Want_to_enable_access_all_products?')}}</strong>','<strong>{{translate('messages.Want_to_disable_access_all_products?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,_Stores_can_access_all_products_of_other_available_stores')}}</p>`,`<p>{{translate('messages.If_you_disable_this,_Stores_can_not_access_all_products_of_other_stores.')}}</p>`)" class="status toggle-switch-input" value="1"
                                                name="access_all_products" id="access_all_products"
                                                {{ $access_all_products == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    @php($product_approval = \App\Models\BusinessSetting::where('key', 'product_approval')->first()?->value ?? 0)
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{translate('messages.Need_Approval_for_Products') }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('messages.If_enabled,_this_option_to_require_admin_approval_for_products_to_be_displayed_on_the_user_side.')}}"><img
                                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ translate('messages.customer_varification_toggle') }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox" onclick="toogleModal(event,'product_approval','store-reg-on.png','store-reg-off.png','<strong>{{translate('messages.Want_to_enable_product_approval?')}}</strong>','<strong>{{translate('messages.Want_to_disable_product_approval?')}}</strong>',`<p>{{translate('messages.If_you_enable_this,_option_to_require_admin_approval_for_products_to_be_displayed_on_the_user_side')}}</p>`,`<p>{{translate('messages.If_you_disable_this,products_will_to_be_displayed_on_the_user_side_without_admin_approval.')}}</p>`)" class="status toggle-switch-input" value="1"
                                                name="product_approval" id="product_approval"
                                                {{ $product_approval == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @php($product_approval_datas = \App\Models\BusinessSetting::where('key', 'product_approval_datas')->first()?->value ?? '')
                            @php($product_approval_datas =json_decode($product_approval_datas , true))
                            <div class="mt-4 access_product_approval {{  $product_approval == 1 ? ' ' : 'd-none'}}">
                                <label class="mb-2 input-label text-capitalize d-flex alig-items-center" for=""> {{ translate('Need_Approval_When') }}</label>
                                <div class="justify-content-between border form-control">
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox" {{  data_get($product_approval_datas,'Add_new_product',null) == 1 ? 'checked' :'' }} id="inlineCheckbox1" value="1" name="Add_new_product">
                                        <label class=" form-check-label" for="inlineCheckbox1">{{ translate('Add_new_product') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox"  {{  data_get($product_approval_datas,'Update_product_price',null) == 1 ? 'checked' :'' }} id="inlineCheckbox2" value="1" name="Update_product_price">
                                        <label class=" form-check-label" for="inlineCheckbox2">{{ translate('Update_product_price') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox" {{  data_get($product_approval_datas,'Update_product_variation',null) == 1 ? 'checked' :'' }}  id="inlineCheckbox3" value="1" name="Update_product_variation">
                                        <label class=" form-check-label" for="inlineCheckbox3">{{ translate('Update_product_variation') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox"  {{  data_get($product_approval_datas,'Update_anything_in_product_details',null) == 1 ? 'checked' :'' }} id="inlineCheckbox4" value="1" name="Update_anything_in_product_details">
                                        <label class=" form-check-label" for="inlineCheckbox4">{{ translate('Update_anything_in_product_details') }}</label>
                                    </div>
                                </div>
                            </div>


                            <div class="btn--container justify-content-end mt-3">
                                <button type="reset" class="btn btn--reset">{{ translate('messages.reset') }}</button>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                    onclick="{{ env('APP_MODE') != 'demo' ? '' : 'call_demo()' }}"
                                    class="btn btn--primary">{{ translate('save_information') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>

@endsection

@push('script_2')

@endpush
