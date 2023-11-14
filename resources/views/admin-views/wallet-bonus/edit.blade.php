@extends('layouts.admin.app')

@section('title',translate('edit_bonus'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.wallet_bonus_update')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.users.customer.wallet.bonus.update',[$bonus['id']])}}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                            @php($language = $language->value ?? null)
                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
                            @if($language)
                                        <ul class="nav nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active"
                                                href="#"
                                                id="default-link">{{translate('messages.default')}}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link"
                                                        href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="lang_form" id="default-form">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label class="input-label" for="default_title">{{translate('messages.Bonus_Title')}} ({{translate('messages.default')}})</label>
                                                        <input type="text" name="title[]" id="default_title" class="form-control" placeholder="{{translate('messages.title')}}" value="{{$bonus?->getRawOriginal('title')}}" oninvalid="document.getElementById('en-link').click()">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label class="input-label" for="default_description">{{translate('messages.Short_Description')}} ({{translate('messages.default')}})</label>
                                                        <input type="text" name="description[]" id="default_description" class="form-control" placeholder="{{translate('messages.description')}}" value="{{$bonus?->getRawOriginal('description')}}" oninvalid="document.getElementById('en-link').click()">
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                        @foreach(json_decode($language) as $lang)
                                            <?php
                                                if(count($bonus['translations'])){
                                                    $translate = [];
                                                    foreach($bonus['translations'] as $t)
                                                    {
                                                        if($t->locale == $lang && $t->key=="title"){
                                                            $translate[$lang]['title'] = $t->value;
                                                        }
                                                        if($t->locale == $lang && $t->key=="description"){
                                                            $translate[$lang]['description'] = $t->value;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <div class="d-none lang_form" id="{{$lang}}-form">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label" for="{{$lang}}_title">{{translate('messages.Bonus_Title')}} ({{strtoupper($lang)}})</label>
                                                            <input type="text" name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{translate('messages.title')}}" value="{{$translate[$lang]['title']??''}}" oninvalid="document.getElementById('en-link').click()">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label" for="{{$lang}}_description">{{translate('messages.Short_Description')}} ({{strtoupper($lang)}})</label>
                                                            <input type="text" name="description[]" id="{{$lang}}_description" class="form-control" placeholder="{{translate('messages.description')}}" value="{{$translate[$lang]['description']??''}}" oninvalid="document.getElementById('en-link').click()">
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                            </div>
                                        @endforeach
                                    @else
                                    <div id="default-form">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Bonus_Title')}} ({{ translate('messages.default') }})</label>
                                            <input type="text" name="title[]" class="form-control" placeholder="{{translate('messages.title')}}" value="{{$bonus['title']}}" maxlength="100">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @endif
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="bonus_type">{{translate('messages.Bonus_Type')}}</label>
                                <select name="bonus_type" id="bonus_type" class="form-control">
                                    <option value="amount" {{$bonus['bonus_type']=='amount'?'selected':''}}>{{translate('messages.amount')}}
                                    </option>
                                    <option value="percentage" {{$bonus['bonus_type']=='percentage'?'selected':''}}>
                                        {{translate('messages.percentage')}} (%)
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="bonus_amount">{{translate('messages.Bonus_Amount')}}
                                    <span    class="{{$bonus['bonus_type']=='amount'? '':'d-none'}}" id='cuttency_symbol'>({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    </span>
                                    <span   class="{{$bonus['bonus_type']=='percentage'? '':'d-none'}}" id="percentage">(%)</span>

                                    <span
                                    class="input-label-secondary text--title" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="{{ translate('Set_the_bonus_amount/percentage_a_customer_will_receive_after_adding_money_to_his_wallet.') }}">
                                    <i class="tio-info-outined"></i>
                                </span>

                                </label>
                                <input type="number" id="bonus_amount" min="1" max="999999999999.99" step="0.01" value="{{$bonus['bonus_amount']}}"
                                       name="bonus_amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="minimum_add_amount">{{translate('messages.Minimum_Add_Money_Amount')}}
                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('Set_the_minimum_add_money_amount_for_a_customer_to_be_eligible_for_the_bonus.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                </label>
                                <input type="number" id="minimum_add_amount" min="1" max="999999999999.99" step="0.01" value="{{$bonus['minimum_add_amount']}}"
                                       name="minimum_add_amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="exampleFormControlInput1">
                                    {{translate('messages.Maximum_Bonus')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    <span
                                    class="input-label-secondary text--title" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="{{ translate('Set_the_maximum_bonus_amount_a_customer_can_receive_for_adding_money_to_his_wallet.') }}">
                                    <i class="tio-info-outined"></i>
                                </span>

                                </label>
                                <input type="number" min="0" max="999999999999.99" step="0.01" value="{{$bonus['maximum_bonus_amount']}}" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" {{$bonus['bonus_type']=='amount'?'readonly="readonly"':''}}>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="date_from">{{translate('messages.start_date')}}</label>
                                <input type="date" name="start_date" class="form-control" id="date_from" placeholder="{{translate('messages.select_date')}}" max="{{date("Y-m-d",strtotime($bonus["end_date"]))}}" value="{{date('Y-m-d',strtotime($bonus['start_date']))}}"                     data-hs-flatpickr-options='{
                                    "dateFormat": "Y-m-d"
                                  }'>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="date_to">{{translate('messages.expire_date')}}</label>
                                <input type="date" name="end_date" class="form-control" placeholder="{{translate('messages.select_date')}}" min="{{date("Y-m-d",strtotime($bonus["start_date"]))}}" id="date_to" value="{{date('Y-m-d',strtotime($bonus['end_date']))}}"
                                       data-hs-flatpickr-options='{
                                     "dateFormat": "Y-m-d"
                                   }'>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            $('#bonus_type').on('change', function() {
                if($('#bonus_type').val() == 'amount')
                {
                    $('#maximum_bonus_amount').attr("readonly","true");
                    $('#maximum_bonus_amount').val(null);
                    $('#percentage').addClass('d-none');
                    $('#cuttency_symbol').removeClass('d-none');
                }
                else
                {
                    $('#maximum_bonus_amount').removeAttr("readonly");
                    $('#percentage').removeClass('d-none');
                    $('#cuttency_symbol').addClass('d-none');
                }
            });
            $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
            $('#date_from').attr('max','{{date("Y-m-d",strtotime($bonus["end_date"]))}}');
            $('#date_to').attr('min','{{date("Y-m-d",strtotime($bonus["start_date"]))}}');

            // INITIALIZATION OF FLATPICKR
            // =======================================================
            $('.js-flatpickr').each(function () {
                $.HSCore.components.HSFlatpickr.init($(this));
            });
        });
        $("#date_from").on("change", function () {
            $('#date_to').attr('min',$(this).val());
        });

        $("#date_to").on("change", function () {
            $('#date_from').attr('max',$(this).val());
        });
    </script>
    <script>
        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("#"+lang+"-form").removeClass('d-none');
            if(lang == 'en')
            {
                $("#from_part_2").removeClass('d-none');
            }
            else
            {
                $("#from_part_2").addClass('d-none');
            }
        })
    </script>
    <script>
        $('#reset_btn').click(function(){
            location.reload(true);
        })

    </script>
@endpush
