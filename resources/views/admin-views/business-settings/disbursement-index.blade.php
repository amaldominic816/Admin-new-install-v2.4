@extends('layouts.admin.app')

@section('title', translate('Disbursement_settings'))


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
    @php($disbursement_type = \App\Models\BusinessSetting::where('key', 'disbursement_type')->first())
    @php($disbursement_type = $disbursement_type ? $disbursement_type->value : 'manual')
    @php($store_disbursement_command = \App\Models\BusinessSetting::where('key', 'store_disbursement_command')->first())
    @php($store_disbursement_command = $store_disbursement_command ? $store_disbursement_command->value : '')
    @php($dm_disbursement_command = \App\Models\BusinessSetting::where('key', 'dm_disbursement_command')->first())
    @php($dm_disbursement_command = $dm_disbursement_command ? $dm_disbursement_command->value : '')
    <!-- Page Header -->

    <!-- End Page Header -->
    <form action="{{ route('admin.business-settings.update-disbursement') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        @if($disbursement_type == 'automated')
                            <div class="mb-3 text-right">
                                <button type="button" class="btn btn--primary" data-toggle="modal" data-target="#myModal">{{ translate('messages.Check_Dependencies') }}</button>
                            </div>
                        @endif
                        <div class="row g-3 mb-2">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize d-flex alig-items-center"><span
                                            class="line--limit-1">{{ translate('Disbursement_Request_Type')}}</span>
                                        <span class="form-label-secondary"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ translate('Choose_Manual_or_Automated_Disbursement_Requests._In_Automated_mode,_withdrawal_requests_for_disbursement_are_generated_automatically;_in_Manual_mode,_stores_need_to_request_withdrawals_manually.') }}"><img
                                                src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                                alt="{{ translate('messages.Disbursement_Request_Type') }}"></span>
                                    </label>
                                    <div class="resturant-type-group border">
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input" type="radio" value="manual"
                                                   name="disbursement_type" id="disbursement_type"
                                                {{ $disbursement_type == 'manual' ? 'checked' : '' }}>
                                            <span class="form-check-label">
                                                    {{ translate('manual') }}
                                                </span>
                                        </label>
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input" type="radio" value="automated"
                                                   name="disbursement_type" id="disbursement_type2"
                                                {{ $disbursement_type == 'automated' ? 'checked' : '' }}>
                                            <span class="form-check-label">
                                                    {{ translate('automated') }}
                                                </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 automated_disbursement_section {{ $disbursement_type == 'manual' ? 'd-none' : '' }}">
                                @php($system_php_path = \App\Models\BusinessSetting::where('key', 'system_php_path')->first())
                                @php($system_php_path = $system_php_path ? $system_php_path->value : '')
                                <div class="form-group lang_form default-form">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('System_PHP_Path')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Default_location_where_the_PHP_executable_is_installed_on_server.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                        </label>
                                    </div>
                                    <input type="text" placeholder="{{translate('Ex:_/usr/bin/php')}}" class="form-control h--45px" min="1" name="system_php_path" value="{{ $system_php_path }}" required>
                                </div>
                            </div>
                            <div class="col-12 automated_disbursement_section {{ $disbursement_type == 'manual' ? 'd-none' : '' }} ">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label">{{translate('Store_Panel')}}</label>
                                        <div class="__bg-F8F9FC-card">
                                            <div class="row">
                                                @php($store_disbursement_time_period = \App\Models\BusinessSetting::where('key', 'store_disbursement_time_period')->first())
                                                @php($store_disbursement_time_period = $store_disbursement_time_period ? $store_disbursement_time_period->value : 1)
                                                <div class='{{ $store_disbursement_time_period=='weekly'?'col-6':'col-12' }}' id="store_time_period_section">
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Create_Disbursements')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_how_the_disbursement_request_will_be_generated:_Monthly,_Weekly_or_Daily.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select name="store_disbursement_time_period" id="store_disbursement_time_period" class="form-control" required>
                                                            <option value="daily" {{ $store_disbursement_time_period=='daily'?'selected':'' }}>
                                                                {{ translate('messages.daily') }}
                                                            </option>
                                                            <option value="weekly" {{ $store_disbursement_time_period=='weekly'?'selected':'' }}>
                                                                {{ translate('messages.weekly') }}
                                                            </option>
                                                            <option value="monthly" {{ $store_disbursement_time_period=='monthly'?'selected':'' }}>
                                                                {{ translate('messages.monthly') }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='col-6 {{ $store_disbursement_time_period=='weekly'?'':'d-none' }}' id="store_week_day_section">
                                                    @php($store_disbursement_week_start = \App\Models\BusinessSetting::where('key', 'store_disbursement_week_start')->first())
                                                    @php($store_disbursement_week_start = $store_disbursement_week_start ? $store_disbursement_week_start->value : 'saturday')
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Week_Start')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_when_the_week_starts_for_the_new_disbursement_request._This_section_will_only_appear_when_weekly_disbursement_is_selected.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select name="store_disbursement_week_start" id="" class="form-control" required>
                                                            <option value="saturday" {{ $store_disbursement_week_start == 'saturday'?'selected':'' }}>
                                                                {{ translate('messages.saturday') }}
                                                            </option>
                                                            <option value="sunday" {{ $store_disbursement_week_start == 'sunday'?'selected':'' }}>
                                                                {{ translate('messages.sunday') }}
                                                            </option>
                                                            <option value="monday" {{ $store_disbursement_week_start == 'monday'?'selected':'' }}>
                                                                {{ translate('messages.monday') }}
                                                            </option>
                                                            <option value="tuesday" {{ $store_disbursement_week_start == 'tuesday'?'selected':'' }}>
                                                                {{ translate('messages.tuesday') }}
                                                            </option>
                                                            <option value="wednesday" {{ $store_disbursement_week_start == 'wednesday'?'selected':'' }}>
                                                                {{ translate('messages.wednesday') }}
                                                            </option>
                                                            <option value="thursday" {{ $store_disbursement_week_start == 'thursday'?'selected':'' }}>
                                                                {{ translate('messages.thursday') }}
                                                            </option>
                                                            <option value="friday" {{ $store_disbursement_week_start == 'friday'?'selected':'' }}>
                                                                {{ translate('messages.friday') }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($store_disbursement_create_time = \App\Models\BusinessSetting::where('key', 'store_disbursement_create_time')->first())
                                                    @php($store_disbursement_create_time = $store_disbursement_create_time ? $store_disbursement_create_time->value : 1)
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Create_Time')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Define_when_the_new_disbursement_request_will_be_generated_automatically.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input type="time" placeholder="{{translate('Ex:_7')}}" class="form-control h--45px" name="store_disbursement_create_time" value="{{ $store_disbursement_create_time }}" required>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($store_disbursement_min_amount = \App\Models\BusinessSetting::where('key', 'store_disbursement_min_amount')->first())
                                                    @php($store_disbursement_min_amount = $store_disbursement_min_amount ? $store_disbursement_min_amount->value : 'saturday')
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Minimum_Amount')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_minimum_amount_to_be_eligible_for_generating_an_auto-disbursement_request.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input type="number" placeholder="{{translate('Ex:_100')}}" class="form-control h--45px" min="1" name="store_disbursement_min_amount" value="{{ $store_disbursement_min_amount }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            @php($store_disbursement_waiting_time = \App\Models\BusinessSetting::where('key', 'store_disbursement_waiting_time')->first())
                                            @php($store_disbursement_waiting_time = $store_disbursement_waiting_time ? $store_disbursement_waiting_time->value : '')
                                            <div class="form-group lang_form default-form">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label text-capitalize m-0">
                                                        {{translate('Days_needed_to_complete_disbursement')}}
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_number_of_days_in_which_the_disbursement_will_be_completed.')}}">
                                                                <i class="tio-info-outined"></i>
                                                            </span>
                                                    </label>
                                                </div>
                                                <input type="number" placeholder="{{translate('Ex:_7')}}" min="1" class="form-control h--45px" name="store_disbursement_waiting_time" value="{{ $store_disbursement_waiting_time }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        @php($dm_disbursement_time_period = \App\Models\BusinessSetting::where('key', 'dm_disbursement_time_period')->first())
                                        @php($dm_disbursement_time_period = $dm_disbursement_time_period ? $dm_disbursement_time_period->value : '')
                                        <label class="form-label">{{translate('Delivery_man')}}</label>
                                        <div class="__bg-F8F9FC-card">
                                            <div class="row">
                                                <div class='{{ $dm_disbursement_time_period=='weekly'?'col-6':'col-12' }}' id="dm_time_period_section">
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Create_Disbursements')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_how_the_disbursement_request_will_be_generated:_Monthly,_Weekly_or_Daily.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select name="dm_disbursement_time_period" id="dm_disbursement_time_period" class="form-control" required>
                                                            <option value="daily" {{ $dm_disbursement_time_period=='daily'?'selected':'' }}>
                                                                {{ translate('messages.daily') }}
                                                            </option>
                                                            <option value="weekly" {{ $dm_disbursement_time_period=='weekly'?'selected':'' }}>
                                                                {{ translate('messages.weekly') }}
                                                            </option>
                                                            <option value="monthly" {{ $dm_disbursement_time_period=='monthly'?'selected':'' }}>
                                                                {{ translate('messages.monthly') }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @php($dm_disbursement_week_start = \App\Models\BusinessSetting::where('key', 'dm_disbursement_week_start')->first())
                                                @php($dm_disbursement_week_start = $dm_disbursement_week_start ? $dm_disbursement_week_start->value : 'saturday')
                                                <div class='col-6 {{ $dm_disbursement_time_period=='weekly'?'':'d-none' }}' id="dm_week_day_section">
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Week_Start')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_when_the_week_starts_for_the_new_disbursement_request._This_section_will_only_appear_when_weekly_disbursement_is_selected.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select name="dm_disbursement_week_start" id="" class="form-control" required>
                                                            <option value="saturday" {{ $dm_disbursement_week_start == 'saturday'?'selected':'' }}>
                                                                {{ translate('messages.saturday') }}
                                                            </option>
                                                            <option value="sunday" {{ $dm_disbursement_week_start == 'sunday'?'selected':'' }}>
                                                                {{ translate('messages.sunday') }}
                                                            </option>
                                                            <option value="monday" {{ $dm_disbursement_week_start == 'monday'?'selected':'' }}>
                                                                {{ translate('messages.monday') }}
                                                            </option>
                                                            <option value="tuesday" {{ $dm_disbursement_week_start == 'tuesday'?'selected':'' }}>
                                                                {{ translate('messages.tuesday') }}
                                                            </option>
                                                            <option value="wednesday" {{ $dm_disbursement_week_start == 'wednesday'?'selected':'' }}>
                                                                {{ translate('messages.wednesday') }}
                                                            </option>
                                                            <option value="thursday" {{ $dm_disbursement_week_start == 'thursday'?'selected':'' }}>
                                                                {{ translate('messages.thursday') }}
                                                            </option>
                                                            <option value="friday" {{ $dm_disbursement_week_start == 'friday'?'selected':'' }}>
                                                                {{ translate('messages.friday') }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($dm_disbursement_create_time = \App\Models\BusinessSetting::where('key', 'dm_disbursement_create_time')->first())
                                                    @php($dm_disbursement_create_time = $dm_disbursement_create_time ? $dm_disbursement_create_time->value : 1)
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Create_Time')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Define_when_the_new_disbursement_request_will_be_generated_automatically.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input type="time" placeholder="{{translate('Ex:_7')}}" class="form-control h--45px" name="dm_disbursement_create_time" value="{{ $dm_disbursement_create_time }}" required>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($dm_disbursement_min_amount = \App\Models\BusinessSetting::where('key', 'dm_disbursement_min_amount')->first())
                                                    @php($dm_disbursement_min_amount = $dm_disbursement_min_amount ? $dm_disbursement_min_amount->value : 'saturday')
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-capitalize m-0">
                                                                {{translate('Minimum_Amount')}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_minimum_amount_to_be_eligible_for_generating_an_auto-disbursement_request.')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input type="number" placeholder="{{translate('Ex:_100')}}" class="form-control h--45px" min="1" name="dm_disbursement_min_amount" value="{{ $dm_disbursement_min_amount }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            @php($dm_disbursement_waiting_time = \App\Models\BusinessSetting::where('key', 'dm_disbursement_waiting_time')->first())
                                            @php($dm_disbursement_waiting_time = $dm_disbursement_waiting_time ? $dm_disbursement_waiting_time->value : '')
                                            <div class="form-group lang_form default-form">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label text-capitalize m-0">
                                                        {{translate('Days_needed_to_complete_disbursement')}}
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_number_of_days_in_which_the_disbursement_will_be_completed.')}}">
                                                                <i class="tio-info-outined"></i>
                                                            </span>
                                                    </label>
                                                </div>
                                                <input type="number" min="1" placeholder="{{translate('Ex:_7')}}" class="form-control h--45px" name="dm_disbursement_waiting_time" value="{{ $dm_disbursement_waiting_time }}" required>
                                            </div>
                                        </div>
                                    </div>
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
    <div class="modal" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center">{{ translate('Cron_Command_for_Disbursement') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                            <span class="text--base">
                                {{translate('In_some_server_configurations,_the_exec_function_in_PHP_may_not_be_enabled,_limiting_your_ability_to_create_cron_jobs_programmatically._A_cron_job_is_a_scheduled_task_that_automates_repetitive_processes_on_your_server._However,_if_the_exec_function_is_disabled,_you_can_manually_set_up_cron_jobs_using_the_following_commands')}}:
                            </span>
                    </div>
                    <label class="form-label text-capitalize">
                        {{translate('Store_Cron_Command')}}
                    </label>
                    <div class="input--group input-group mb-3">
                        <input type="text" value="{{ $store_disbursement_command }}" class="form-control" id="storeDisbursementCommand" readonly>
                        <button class="btn btn-primary copy-btn" onclick="copyToClipboard('storeDisbursementCommand')">{{ translate('Copy') }}</button>
                    </div>
                    <label class="form-label text-capitalize">
                        {{translate('Delivery_Man_Cron_Command')}}
                    </label>
                    <div class="input--group input-group">
                        <input type="text" value="{{ $dm_disbursement_command }}" class="form-control"  id="dmDisbursementCommand" readonly>
                        <button class="btn btn-primary copy-btn" onclick="copyToClipboard('dmDisbursementCommand')">{{ translate('Copy') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@push('script_2')
    @php($flag = session('disbursement_exec'))
    <script>
        $(document).on('ready', function() {
            @if ($disbursement_type == 'manual')
            $('.automated_disbursement_section').hide();
            @endif

            @if (isset($flag) && $flag)
            $('#myModal').modal('show');
            @endif
        });
    </script>

    <script>
        $('input[name="disbursement_type"]').on('change', function(){
            if(this.value == 'manual'){
                $('.automated_disbursement_section').hide();

            }else{
                $('.automated_disbursement_section').show();
                $('.automated_disbursement_section').removeClass('d-none');
            }
        })
        $('#store_disbursement_time_period').on('change', function(){
            if(this.value == 'weekly'){
                $('#store_time_period_section').removeClass('col-12');
                $('#store_time_period_section').addClass('col-6');
                $('#store_week_day_section').removeClass('d-none');
            }else{
                $('#store_week_day_section').addClass('d-none');
                $('#store_time_period_section').removeClass('col-6');
                $('#store_time_period_section').addClass('col-12');
            }
        })
        $('#dm_disbursement_time_period').on('change', function(){
            if(this.value == 'weekly'){
                $('#dm_time_period_section').removeClass('col-12');
                $('#dm_time_period_section').addClass('col-6');
                $('#dm_week_day_section').removeClass('d-none');
            }else{
                $('#dm_week_day_section').addClass('d-none');
                $('#dm_time_period_section').removeClass('col-6');
                $('#dm_time_period_section').addClass('col-12');
            }
        })
    </script>
    <script>
        $('#reset_btn').click(function(){
            location.reload(true);
        })
    </script>
    <script>
        function copyToClipboard(elementId) {
            var commandElement = document.getElementById(elementId);
            // commandElement.select();
            navigator.clipboard.writeText(commandElement.value);
            toastr.success('Copied to clipboard!');
        }
    </script>
@endpush
