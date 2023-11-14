@extends('layouts.admin.app')

@section('title',translate('messages.Update Flash Sale'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.flash_sale_update')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.flash-sale.update',[$flash_sale['id']])}}" method="post">
                    @csrf
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
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="lang_form" id="default-form">
                                                    <div class="form-group">
                                                        <label class="input-label" for="default_title">{{translate('messages.title')}} ({{translate('messages.default')}})</label>
                                                        <input type="text" name="title[]" maxlength="100" id="default_title" class="form-control" placeholder="{{translate('messages.updated_flash_sale')}}" value="{{$flash_sale?->getRawOriginal('title')}}" oninvalid="document.getElementById('en-link').click()">
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                </div>
                                                @foreach(json_decode($language) as $lang)
                                                    <?php
                                                        if(count($flash_sale['translations'])){
                                                            $translate = [];
                                                            foreach($flash_sale['translations'] as $t)
                                                            {
                                                                if($t->locale == $lang && $t->key=="title"){
                                                                    $translate[$lang]['title'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                    ?>
                                                    <div class="d-none lang_form" id="{{$lang}}-form">
                                                        <div class="form-group">
                                                            <label class="input-label" for="{{$lang}}_title">{{translate('messages.title')}} ({{strtoupper($lang)}})</label>
                                                            <input type="text" name="title[]" maxlength="100" id="{{$lang}}_title" class="form-control" placeholder="{{translate('messages.updated_flash_sale')}}" value="{{$translate[$lang]['title']??''}}" oninvalid="document.getElementById('en-link').click()">
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_title">{{ translate('messages.discount_Bearer') }}
                                                    </label>
                                                </div>
                                                <div class="row g-3 __bg-F8F9FC-card">
                                                    <div class="col-sm-6">
                                                        <label class="form-label">{{ translate('admin') }}(%)</label>
                                                    <input type="number" min=".01" step="0.001" max="100" name="admin_discount_percentage"
                                                            value="{{ $flash_sale->admin_discount_percentage }}"
                                                            class="form-control"
                                                            placeholder="{{ translate('Ex_:_50') }}" required>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label class="form-label">{{ translate('messages.store_owner') }}(%)</label>
                                                    <input type="number" min=".01" step="0.001" max="100" name="vendor_discount_percentage"
                                                            value="{{ $flash_sale->vendor_discount_percentage }}"
                                                            class="form-control"
                                                            placeholder="{{ translate('Ex_:_50') }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_title">{{ translate('messages.validity') }}
                                                    </label>
                                                </div>
                                                <div class="row g-3 __bg-F8F9FC-card">
                                                    <div class="col-6">
                                                        <div>
                                                            <label class="input-label" for="title">{{translate('messages.start_date')}}</label>
                                                            <input type="datetime-local" id="from" class="form-control" required="" name="start_date" value="{{ $flash_sale->start_date }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div>
                                                            <label class="input-label" for="title">{{translate('messages.end_date')}}</label>
                                                            <input type="datetime-local" id="to" class="form-control" required="" name="end_date" value="{{ $flash_sale->end_date}}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                    <div class="btn--container justify-content-end mt-5">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
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
            $('#from').attr('min',(new Date()).toISOString().split('T')[0]);
            $('#from').attr('max','{{$flash_sale->end_date}}');
            $('#to').attr('min','{{$flash_sale->start_date}}');
        });
        $("#from").on("change", function () {
            $('#to').attr('min',$(this).val());
        });

        $("#to").on("change", function () {
            $('#from').attr('max',$(this).val());
        });
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
@endpush
