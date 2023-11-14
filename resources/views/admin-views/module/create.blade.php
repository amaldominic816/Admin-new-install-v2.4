@extends('layouts.admin.app')

@section('title',translate('messages.business_modules'))

@push('css_or_js')
<link rel="stylesheet" href="{{asset('public/assets/admin/css/radio-image.css')}}">

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('/public/assets/admin/img/module.png')}}" alt="">
            </span>
            <span>
                {{translate('Add_New_Business_Module')}}
            </span>
        </h1>
        <div class="mt-2 d-flex">
            <span>{{ translate('*Set_up_your_New_Business_Module_type_theme_icon_&_thumbnail.') }}</span>
            <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1 p--10">
                <div class="blinkings active">
                    <i class="tio-info-outined"></i>
                    <div class="business-notes">
                        <h6><img src="{{asset('/public/assets/admin/img/notes.png')}}" alt=""> {{translate('Note')}}</h6>
                        <div>
                        {{translate('messages.Don’t_forget_to_click_the_‘Add_Module’_button_below_to_save_the_new_business_module.')}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="card">
        <div class="card-body">
            <form action="{{route('admin.business-settings.module.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                @php($language = $language->value ?? null)
                @php($default_lang = str_replace('_', '-', app()->getLocale()))
                @if($language)
                <ul class="nav nav-tabs mb-4 border-0">
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
                @endif
                @if ($language)
                <div class="lang_form p-1 mb-2" id="default-form">
                    <div class="form-group">
                        <label class="input-label text-capitalize d-flex" for="exampleFormControlInput1">{{translate('Business_Module_name')}} ({{ translate('messages.default') }})</label>
                        <input type="text" name="module_name[]" class="form-control" maxlength="191" oninvalid="document.getElementById('en-link').click()" placeholder="{{ translate('messages.Ex:_Grocery,eCommerce,Pharmacy,etc.') }}">
                    </div>
                    <div class="form-group">
                        <label class="input-label d-flex" for="module_type">{{ translate('Business_Module_description')}} ({{ translate('messages.default') }})<span class="form-label-secondary text-danger d-flex"
                            data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.Write_a_short_description_of_your_new_business_module_within_100_words_(550_characters)') }}"><img
                                src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                alt="{{ translate('messages.veg_non_veg') }}"></span></label>
                        <textarea class="ckeditor form-control" name="description[]"></textarea>
                    </div>
                </div>

                <input type="hidden" name="lang[]" value="default">
                @foreach(json_decode($language) as $lang)
                <div class="d-none lang_form p-1 mb-2" id="{{$lang}}-form">
                    <div class="form-group">
                        <label class="input-label text-capitalize d-flex" for="exampleFormControlInput1">{{translate('Business_Module_name')}} ({{strtoupper($lang)}})</label>
                        <input type="text" name="module_name[]" class="form-control" maxlength="191" oninvalid="document.getElementById('en-link').click()" placeholder="{{ translate('messages.Ex:_Grocery,eCommerce,Pharmacy,etc.') }}">
                    </div>
                    <div class="form-group">
                        <label class="input-label d-flex" for="module_type">{{ translate('Business_Module_description')}} ({{strtoupper($lang)}})<span class="form-label-secondary text-danger d-flex"
                            data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.Write_a_short_description_of_your_new_business_module_within_100_words_(550_characters)')}}"><img
                                src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                alt="{{ translate('messages.veg_non_veg') }}"></span></label>
                        <textarea class="ckeditor form-control" name="description[]"></textarea>
                    </div>
                </div>

                <input type="hidden" name="lang[]" value="{{$lang}}">
                @endforeach
                @else
                <div class="form-group">
                    <label class="input-label" for="exampleFormControlInput1">{{translate('Business_Module_name')}}</label>
                    <input type="text" name="module_name" class="form-control" placeholder="{{translate('messages.new_category')}}" value="{{old('name')}}" maxlength="191"  placeholder="{{ translate('messages.Ex:_business_Module Name') }}">
                </div>
                <div class="form-group">
                    <label class="input-label" for="module_type">{{ translate('Business_Module_description')}}</label>
                    <textarea class="ckeditor form-control" name="description"></textarea>
                </div>
                <input type="hidden" name="lang[]" value="default">
                @endif
                <div class="row mt-2">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="input-label" for="module_type">{{translate('messages.business_module_type')}}</label>
                            <select name="module_type" id="module_type" class="form-control text-capitalize" onchange="modulChange(this.value)">
                                <option disabled selected>{{translate('messages.select_business_module_type')}}</option>
                                @foreach (config('module.module_type') as $key)
                                <option class="" value="{{$key}}">{{translate($key)}}</option>
                                @endforeach
                            </select>
                            <small class="text-danger">{{translate('messages.business_module_type_change_warning')}}</small>
                            <div class="card mt-1 initial-hidden" id="module_des_card">
                                <div class="card-body" id="module_description"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card h-100 module-logo-card mb-3">
                    <div class="card-body">
                        <div class="row h-100">
                            <div class="col-sm-6 mb-4 mb-sm-0">
                                <div class="form-group m-0 h-100 d-flex flex-column justify-content-center">
                                    <label class="form-label mb-0">
                                        {{translate('messages.icon')}}
                                        <small class="text-danger">* ( {{translate('messages.ratio')}} 1:1)</small>
                                    </label>
                                    <center class="my-auto py-3">
                                        <img class="initial--15" id="viewer" src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}" alt="image" />
                                    </center>
                                    <div class="custom-file">
                                        <input type="file" name="icon" id="customFileEg1" class="custom-file-input" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                        <label class="custom-file-label" for="customFileEg1">{{translate('messages.choose_file')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group m-0 h-100 d-flex flex-column justify-content-center">
                                    <label class="form-label mb-0">
                                        {{translate('messages.thumbnail')}}
                                        <small class="text-danger">* ( {{translate('messages.ratio')}} 1:1)</small>
                                    </label>
                                    <center class="my-auto py-3">
                                        <img class="initial--15" id="viewer2" src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}" alt="image" />
                                    </center>
                                    <div class="custom-file">
                                        <input type="file" name="thumbnail" id="customFileEg2" class="custom-file-input" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                        <label class="custom-file-label" for="customFileEg2">{{translate('messages.choose_file')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end">
                    <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                    <button type="submit" class="btn btn--primary">{{translate('messages.Add_Module')}}</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@push('script_2')
<script>
    function modulChange(id) {
        $.get({
            url: "{{url('/')}}/admin/module/type/?module_type=" + id,
            dataType: 'json',
            success: function(data) {
                if(data.data.description.length)
                {
                    $('#module_des_card').show();
                    $('#module_description').html(data.data.description);
                }
                else
                {
                    $('#module_des_card').hide();
                }
                if(id=='parcel')
                {
                    $('#module_theme').hide();
                    $('#zone_check').hide();
                }
                else{
                    $('#module_theme').show();
                    $('#zone_check').show();
                }
            },
        });
    }

    function readURL(input, id) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#' + id).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#customFileEg1").change(function() {
        readURL(this, 'viewer');
    });

    $("#customFileEg2").change(function() {
        readURL(this, 'viewer2');
    });
</script>
<script>
    $(".lang_link").click(function(e) {
        e.preventDefault();
        $(".lang_link").removeClass('active');
        $(".lang_form").addClass('d-none');
        $(this).addClass('active');

        let form_id = this.id;
        let lang = form_id.substring(0, form_id.length - 5);
        console.log(lang);
        $("#" + lang + "-form").removeClass('d-none');
        if (lang == '{{$default_lang}}') {
            $(".from_part_2").removeClass('d-none');
        } else {
            $(".from_part_2").addClass('d-none');
        }
    });
</script>
<script src="//cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.ckeditor').ckeditor();
    });
</script>
<script>
        $('#reset_btn').click(function(){
            $('#viewer').attr('src','{{asset('public/assets/admin/img/400x400/img2.jpg')}}');
            $('#viewer2').attr('src','{{asset('public/assets/admin/img/400x400/img2.jpg')}}');
        })
</script>
@endpush
