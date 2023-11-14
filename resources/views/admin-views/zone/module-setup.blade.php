@extends('layouts.admin.app')

@section('title', translate('Zone Wise Module Setup'))

@push('css_or_js')
@endpush

@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/edit.png') }}" class="w--26" alt="">
                </span>
                <span>
                   {{ $zone->name }} {{ translate('Zone_Settings') }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <form action="{{ route('admin.zone.module-update', $zone->id) }}" method="post" id="zone_form" class="shadow--card">
            @csrf
            <div class="row g-2">
                <div class="col-12">
                    <div class="d-flex flex-wrap select--all-checkes">
                        <h5 class="input-label m-0 text-capitalize">{{translate('messages.Select_Payment_Method')}} </h5>
                    </div>
                    <span class="badge badge-soft-danger mt-2">{{ translate('NB:_MUST_select_at_least_‘one’_payment_method.') }}</span>
                    <div class="check--item-wrapper mb-1">
                        @php($config=\App\CentralLogics\Helpers::get_business_settings('cash_on_delivery'))
                        @if ($config && $config['status']==1)
                        <div class="check-item">
                            <div class="form-group form-check form--check">
                                <input type="checkbox" name="cash_on_delivery" value="cash_on_delivery" class="form-check-input"
                                       id="cash_on_delivery" {{$zone->cash_on_delivery == 1 ?'checked':''}}>
                                <label class="form-check-label qcont text-dark" for="cash_on_delivery">{{translate('messages.Cash On Delivery')}}</label>
                            </div>
                        </div>
                        @endif
                        @php($digital_payment=\App\CentralLogics\Helpers::get_business_settings('digital_payment'))
                        @if ($digital_payment && $digital_payment['status']==1)
                        <div class="check-item">
                            <div class="form-group form-check form--check">
                                <input type="checkbox" name="digital_payment" value="digital_payment" class="form-check-input"
                                       id="digital_payment" {{$zone->digital_payment == 1 ?'checked':''}}>
                                <label class="form-check-label qcont text-dark" for="digital_payment">{{translate('messages.digital payment')}}</label>
                            </div>
                        </div>
                        @endif
                        @php($offline_payment=\App\CentralLogics\Helpers::get_business_settings('offline_payment_status'))
                        @if ($offline_payment && $offline_payment==1)
                        <div class="check-item">
                            <div class="form-group form-check form--check">
                                <input type="checkbox" name="offline_payment" value="offline_payment" class="form-check-input"
                                       id="offline_payment" {{$zone->offline_payment == 1 ?'checked':''}}>
                                <label class="form-check-label qcont text-dark" for="offline_payment">{{translate('messages.offline payment')}}</label>
                            </div>
                        </div>
                        @endif
                    </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="input-label text-capitalize d-inline-flex alig-items-center"
                                    for="increased_delivery_fee">
                                    <span class="line--limit-1">{{ translate('messages.increase_delivery_charge') }} (%)
                                    <span data-toggle="tooltip" data-placement="right" data-original-title="{{translate('messages.Set_an_additional_delivery_charge_in_percentage_for_any_emergency_situations._This_amount_will_be_added_to_the_delivery_charge.')}}" class="input-label-secondary"><img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('messages.dm_maximum_order_hint') }}"></span>
                                </label>
                                <label class="toggle-switch toggle-switch-sm">
                                    <input type="checkbox" class="toggle-switch-input" name="increased_delivery_fee_status"
                                        id="increased_delivery_fee_status" value="1"
                                        {{ $zone->increased_delivery_fee_status == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <div class="toggle-switch-indicator"></div>
                                        </span>
                                </label>
                            </div>
                            <input type="number" name="increased_delivery_fee" class="form-control"
                                id="increased_delivery_fee"
                                value="{{ $zone->increased_delivery_fee ? $zone->increased_delivery_fee : '' }}" min="0" max="100"
                                step=".001" placeholder="{{ translate('messages.Ex:_100') }}" {{ ($zone->increased_delivery_fee_status == 1) ? ' ' : 'readonly' }}>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="input-label text-capitalize d-inline-flex alig-items-center"
                                    for="increased_delivery_fee">
                                    <span class="line--limit-1">{{ translate('messages.increase_delivery_charge_message') }}
                                        <span data-toggle="tooltip" data-placement="right" data-original-title="{{translate('messages.Customers_will_see_the_delivery_charge_increased_reason_on_the_website_and_customer_app.')}}" class="input-label-secondary"><img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('messages.dm_maximum_order_hint') }}"></span>

                                </label>
                            </div>
                            <input type="text" name="increase_delivery_charge_message" class="form-control"
                                id="increase_delivery_charge_message"
                                value="{{ $zone->increase_delivery_charge_message ? $zone->increase_delivery_charge_message : '' }}"
                                    placeholder="{{ translate('messages.Ex:_Rainy_season') }} " {{ ($zone->increased_delivery_fee_status == 1) ? ' ' : 'readonly' }}>
                        </div>
                    </div>
                </div>


                    <div class="form-group mb-0">
                        <label class="input-label" for="exampleFormControlSelect1">{{ translate('Choose_Business_Module') }}<span
                                class="input-label-secondary"></span></label>
                        <select name="module_id[]" id="choice_modules" class="form-control js-select2-custom"
                            multiple="multiple">
                            @php($modules_array = \App\Models\Module::get()->toArray())
                            @php($modules = \App\Models\Module::get())
                            @php($selected_modules = count($zone->modules) > 0 ? $zone->modules->pluck('id')->toArray() : [])
                            @foreach ($modules as $module)
                                <option value="{{ $module['id'] }}"
                                    {{ in_array($module['id'], $selected_modules) ? 'selected' : '' }}>
                                    {{ $module['module_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="delivery_charge_options" id="delivery_charge_options">
                        <div class="row gy-1" id="mod-label">
                            <div class="col-sm-4">
                                <label for="">{{ translate('messages.Module Name') }}</label>
                            </div>
                            <div class="col-sm-2">
                                <label for="">{{ translate('messages.per_km_delivery_charge') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span class="input-label-secondary text-danger">*</span></label>
                            </div>
                            <div class="col-sm-2">
                                <label for="">{{ translate('messages.Minimum delivery charge') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span class="input-label-secondary text-danger">*</span></label>
                            </div>
                            <div class="col-sm-2">
                                <label for="">{{ translate('messages.Maximum delivery charge') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                            </div>
                            <div class="col-sm-2">
                                <label for="">{{ translate('maximum_cod_order_amount') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                            </div>
                        </div>
                        @if (count($zone->modules) > 0)
                            @foreach ($zone->modules as $module)
                            @if ($module->module_type == 'parcel')
                            <div class="row gy-1 module-row" id="module_{{ $module->id }}">
                                <div class="col-sm-4"><input type="text" class="form-control"
                                        value="{{ $module->module_name }}"
                                        placeholder="{{ translate('messages.choice_title') }}" readonly></div>
                                <div class="col-sm-2"><input type="number" class="form-control"
                                        name="module_data[{{ $module->id }}][per_km_shipping_charge]" step=".01"
                                        min="0" placeholder="{{ translate('Set charge from parcel category') }}"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.You have to set category wise charge from parcel category') }}" readonly></div>
                                <div class="col-sm-2"><input type="number" step=".01" min="0"
                                        class="form-control"
                                        name="module_data[{{ $module->id }}][minimum_shipping_charge]"
                                        placeholder="{{ translate('Set charge from parcel category') }}"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.You have to set category wise charge from parcel category') }}" readonly></div>
                                <div class="col-sm-2"><input type="number" step=".01" min="0"
                                        class="form-control"
                                        name="module_data[{{ $module->id }}][maximum_shipping_charge]"
                                        placeholder="{{ translate('Set charge from parcel category') }}"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.You have to set category wise charge from parcel category') }}" readonly></div>
                                <div class="col-sm-2"><input type="number" step=".01" min="0"
                                    class="form-control"
                                    name="module_data[{{ $module->id }}][maximum_cod_order_amount]"
                                    placeholder="{{ translate('enter_Amount') }}"
                                    title="{{ translate('set_maximum_cod_order_amount') }}"
                                    value="{{ $module->pivot->maximum_cod_order_amount }}" readonly></div>
                            </div>
                            @else
                            <div class="row gy-1 module-row" id="module_{{ $module->id }}">
                                <div class="col-sm-4"><input type="text" class="form-control"
                                        value="{{ $module->module_name }}"
                                        placeholder="{{ translate('messages.choice_title') }}" readonly></div>
                                <div class="col-sm-2"><input type="number" class="form-control"
                                        name="module_data[{{ $module->id }}][per_km_shipping_charge]" step=".01"
                                        min="0" placeholder="{{ translate('messages.enter_Amount') }}"
                                        title="{{ translate('messages.per_km_delivery_charge') }}"
                                        value="{{ $module->pivot->per_km_shipping_charge }}" required></div>
                                <div class="col-sm-2"><input type="number" step=".01" min="0"
                                        class="form-control"
                                        name="module_data[{{ $module->id }}][minimum_shipping_charge]"
                                        placeholder="{{ translate('messages.enter_Amount') }}"
                                        title="{{ translate('messages.Minimum delivery charge') }}"
                                        value="{{ $module->pivot->minimum_shipping_charge }}" required></div>
                                <div class="col-sm-2"><input type="number" step=".01" min="0"
                                        class="form-control"
                                        name="module_data[{{ $module->id }}][maximum_shipping_charge]"
                                        placeholder="{{ translate('messages.enter_Amount') }}"
                                        title="{{ translate('messages.maximum delivery charge') }}"
                                        value="{{ $module->pivot->maximum_shipping_charge }}" ></div>
                                <div class="col-sm-2"><input type="number" step=".01" min="0"
                                        class="form-control"
                                        name="module_data[{{ $module->id }}][maximum_cod_order_amount]"
                                        placeholder="{{ translate('enter_Amount') }}"
                                        title="{{ translate('set_maximum_cod_order_amount') }}"
                                        value="{{ $module->pivot->maximum_cod_order_amount }}"></div>
                            </div>
                            @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="btn--container mt-3 justify-content-end">
                <button type="submit" class="btn btn--primary">{{ translate('messages.update') }}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin') }}/js/tags-input.min.js"></script>
    <script>

        $(document).on('ready', function() {
            $("#increased_delivery_fee_status").on('change', function() {
                if ($("#increased_delivery_fee_status").is(':checked')) {
                    $('#increased_delivery_fee').removeAttr('readonly');
                    $('#increase_delivery_charge_message').removeAttr('readonly');
                } else {
                    $('#increased_delivery_fee').attr('readonly', true);
                    $('#increase_delivery_charge_message').attr('readonly', true);
                    $('#increased_delivery_fee').val('Ex : 0');
                    $('#increase_delivery_charge_message').val('');
                }
            });
        });


        let modules = <?php echo json_encode($modules_array); ?>;
        let mod = {{ count($zone->modules) }};
        if(mod>0){
            $('#mod-label').show();
        }else{
            $('#mod-label').hide();
        }
        $('#choice_modules').on('change', function() {
            $('#mod-label').show();
            var ids = $('.module-row').map(function() {
                return $(this).attr('id').split('_')[1];
            }).get();

            $.each($("#choice_modules option:selected"), function(index, element) {
                console.log($(this).val());
                if (ids.includes($(this).val())) {
                    ids = ids.filter(id => id !== $(this).val());
                } else {
                    let name = $('#choice_modules option[value="' + $(this).val() + '"]').html();
                    var found = modules.find(modul=> modul.id == $(this).val());
                    if (found.module_type == 'parcel'){

                        add_parcel_module($(this).val(), name.trim());
                    }else{

                        add_more_delivery_charge_option($(this).val(), name.trim());
                    }
                }
            });
            console.log(ids)
            if (ids.length > 0) {
                ids.forEach(element => {
                    console.log("module_", 3)
                    $("#module_" + element.trim()).remove();
                });
            }
        });

        function add_more_delivery_charge_option(i, name) {
            let n = name;
            $('#delivery_charge_options').append(
                '<div class="row gy-1 module-row" id="module_' + i +
                '"><div class="col-sm-4"><input type="text" class="form-control" value="' + n +
                '" placeholder="{{ translate('messages.choice_title') }}" readonly></div><div class="col-sm-2"><input type="number" class="form-control" name="module_data[' +
                i +
                '][per_km_shipping_charge]" step=".01" min="0" placeholder="{{ translate('messages.enter_Amount') }}" title="{{ translate('messages.per_km_delivery_charge') }}" required></div><div class="col-sm-2"><input type="number" step=".01" min="0" class="form-control" name="module_data[' +
                i +
                '][minimum_shipping_charge]" placeholder="{{ translate('messages.enter_Amount') }}" title="{{ translate('messages.Minimum delivery charge') }}" required></div><div class="col-sm-2"><input type="number" step=".01" min="0" class="form-control" name="module_data[' +
                i +
                '][maximum_shipping_charge]" placeholder="{{ translate('messages.enter_Amount') }}" title="{{ translate('messages.maximum delivery charge') }}"></div><div class="col-sm-2"><input type="number" step=".01" min="0" class="form-control" name="module_data[' +
                i +
                '][maximum_cod_order_amount]" placeholder="{{ translate('enter_Amount') }}" title="{{ translate('set_maximum_cod_order_amount') }}"></div></div>'
            );
        }
        function add_parcel_module(i, name) {
            let n = name;
            $('#delivery_charge_options').append(
                '<div class="row gy-1 module-row" id="module_' + i +
                '"><div class="col-sm-4"><input type="text" class="form-control" value="' + n +
                '" placeholder="{{ translate('messages.choice_title') }}" readonly></div><div class="col-sm-2"><input type="number" name="module_data[' +
                i +
                '][per_km_shipping_charge]" class="form-control" step=".01" min="0" placeholder="{{ translate('Set charge from parcel category') }}" value="" title="{{ translate('messages.per_km_delivery_charge') }}" readonly></div><div class="col-sm-2"><input type="number" name="module_data[' +
                i +
                '][minimum_shipping_charge]" step=".01" min="0" class="form-control" placeholder="{{ translate('Set charge from parcel category') }}" value="" title="{{ translate('messages.Minimum delivery charge') }}" readonly></div><div class="col-sm-2"><input type="number" name="module_data[' +
                i +
                '][maximum_shipping_charge]" step=".01" min="0" class="form-control" placeholder="{{ translate('Set charge from parcel category') }}" value="" title="{{ translate('messages.maximum delivery charge') }}" readonly></div><div class="col-sm-2"><input type="number" step=".01" min="0" class="form-control" name="module_data[' +
                i +
                '][maximum_cod_order_amount]" placeholder="{{ translate('enter_Amount') }}" title="{{ translate('set_maximum_cod_order_amount') }}" readonly></div></div>'
            );
        }
    </script>


@endpush
