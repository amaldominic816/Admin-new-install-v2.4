@extends('layouts.admin.app')

@section('title',translate('messages.banner'))

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('public/assets/admin/img/3rd-party.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{translate('messages.Other_Promotional_Content_Setup')}}
            </span>
        </h1>
    </div>
    <div class="mb-4 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.other-banners.partial.parcel-links')
        </div>
    </div>
    @php($content1_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'content1_title')->first())
    @php($content1_subtitle = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'content1_subtitle')->first())
    @php($content2_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'content2_title')->first())
    @php($content2_subtitle = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'content2_subtitle')->first())
    @php($content3_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'content3_title')->first())
    @php($content3_subtitle = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'content3_subtitle')->first())
    @php($section_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'section_title')->first())
    @php($banner_type = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'banner_type')->first())
    @php($banner_video = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'banner_video')->first())
    @php($banner_image = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id',Config::get('module.current_module_id'))->where('type','video_banner_content')->where('key', 'banner_image')->first())
        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
        @php($language = $language->value ?? null)
        @php($default_lang = str_replace('_', '-', app()->getLocale()))
        @if ($language)
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item">
                    <a class="nav-link lang_link active" href="#"
                        id="default-link">{{ translate('messages.default') }}</a>
                </li>
                @foreach (json_decode($language) as $lang)
                    <li class="nav-item">
                        <a class="nav-link lang_link" href="#"
                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <form action="{{ route('admin.promotional-banner.video-image-store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card mb-3">
                        <h5 class="card-title p-3">
                            <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                            <span>{{ translate('Video_/_Image') }}</span>
                        </h5>
                        <div class="card-body">
                            <div class="row g-4">
                                @if ($language)
                                    <div class="col-md-6 lang_form default-form">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">{{ translate('Section_Title') }}
                                                    ({{ translate('messages.default') }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_20_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="20" name="section_title[]" value="{{ $section_title?->getRawOriginal('value') }}" class="form-control"
                                                    placeholder="{{ translate('Ex:Enter_section_title') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                    @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (isset($section_title->translations) && count($section_title->translations)) {
                                        $section_title_translate = [];
                                        foreach ($section_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'section_title') {
                                                $section_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                        <div class="col-md-6 d-none lang_form" id="{{ $lang }}-form1">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">{{ translate('Section_Title') }}
                                                        ({{ strtoupper($lang) }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_20_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="20" name="section_title[]" value="{{ $section_title_translate[$lang]['value'] ?? '' }}" class="form-control"
                                                        placeholder="{{ translate('Ex:Enter_section_title') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    @endforeach
                                @endif
                                <div class="col-sm-12 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label text-capitalize d-flex alig-items-center"><span
                                            class="line--limit-1">{{ translate('Upload_Content') }}
                                        </span>
                                        </label>
                                        <div class="resturant-type-group border">
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="video" name="banner_type" {{ $banner_type ? ($banner_type->value == 'video' ? 'checked' : '') : '' }}>
                                                <span class="form-check-label">
                                                    {{translate('video')}}
                                                </span>
                                            </label>
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="image" name="banner_type" {{ $banner_type ? ($banner_type->value == 'image' ? 'checked' : '') : '' }}>
                                                <span class="form-check-label">
                                                    {{translate('image')}}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 {{ $banner_type ? ($banner_type->value == 'image' ? '' : 'd-none') : '' }}" id="image">
                                    <label class="__upload-img aspect-615-350 d-block position-relative">
                                        <div class="img">
                                            <img src="{{asset('storage/app/public/promotional_banner')}}/{{$banner_image?->value}}"
                                            onerror='this.src="{{ asset('/public/assets/admin/img/upload-placeholder.png') }}"' alt="">
                                        </div>

                                        <div class="">
                                                <input type="file" name="banner_image"  hidden>
                                            </div>
                                            @if (isset($banner_image?->value))
                                            <span id="banner_image" class="remove_image_button"
                                            onclick="toogleStatusModal(event,'banner_image','mail-success','mail-warning','{{translate('Important!')}}','{{translate('Warning!')}}',`<p>{{translate('Are_you_sure_you_want_to_remove_this_image')}}</p>`,`<p>{{translate('Are_you_sure_you_want_to_remove_this_image.')}}</p>`)"
                                            >
                                            <i class="tio-clear"></i></span>
                                            @endif
                                    </label>
                                    <div class="text-center mt-5">
                                        <h3 class="form-label d-block mt-2">
                                        {{translate('Image_Size_Min_615_x_350_px')}}
                                    </h3>
                                    <p>{{translate('image_format_:_jpg_,_png_,_jpeg_|_maximum_size:_2_MB')}}</p>

                                    </div>
                                </div>
                                <div class="col-12 {{ $banner_type ? ($banner_type->value == 'video' ? '' : 'd-none') : 'd-none' }}" id="video">
                                    <label class="form-label">{{ translate('Video_Link') }}</label>
                                    <input type="url" name="banner_video" value="{{ $banner_video?->value }}" class="form-control"
                                        placeholder="{{ translate('messages.Enter_URL') }}">
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-3">
                                <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                                <button type="submit" class="btn btn--primary mb-2">{{ translate('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
                <form action="{{ route('admin.promotional-banner.video-content-store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h5 class="card-title mb-3">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{ translate('Video_/_Image_Content') }}</span>
                    </h5>
                    <div class="card mb-3">
                        <div class="card-body">
                            @if ($language)
                                <div class="lang_form default-form">
                                    <div class="form-group">
                                        <label class="form-label">{{ translate('content-1') }}</label>
                                        <div class="row g-3 __bg-F8F9FC-card">
                                            <div class="col-sm-6">
                                                <label class="form-label">{{ translate('Title') }}
                                                    ({{ translate('messages.default') }})<span class="form-label-secondary"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_80_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="80" name="content1_title[]"
                                                    value="{{ $content1_title?->getRawOriginal('value') }}" class="form-control"
                                                    placeholder="{{ translate('Ex_:_Enter_Title') }}">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">{{ translate('messages.Sub Title') }}
                                                    ({{ translate('messages.default') }})<span class="form-label-secondary"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_240_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="240" name="content1_subtitle[]"
                                                    value="{{ $content1_subtitle?->getRawOriginal('value') }}" class="form-control"
                                                    placeholder="{{ translate('Ex_:_Enter_Subtitle') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">{{ translate('content-2') }}</label>
                                        <div class="row g-3 __bg-F8F9FC-card">
                                            <div class="col-sm-6">
                                                <label class="form-label">{{ translate('Title') }}
                                                    ({{ translate('messages.default') }})<span class="form-label-secondary"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_80_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="80" name="content2_title[]"
                                                    value="{{ $content2_title?->getRawOriginal('value') }}" class="form-control"
                                                    placeholder="{{ translate('Ex_:_Enter_Title') }}">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">{{ translate('messages.Sub Title') }}
                                                    ({{ translate('messages.default') }})<span class="form-label-secondary"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_240_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="240" name="content2_subtitle[]"
                                                    value="{{ $content2_subtitle?->getRawOriginal('value') }}" class="form-control"
                                                    placeholder="{{ translate('Ex_:_Enter_Subtitle') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">{{ translate('content-3') }}</label>
                                        <div class="row g-3 __bg-F8F9FC-card">
                                            <div class="col-sm-6">
                                                <label class="form-label">{{ translate('Title') }}
                                                    ({{ translate('messages.default') }})<span class="form-label-secondary"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_80_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="80" name="content3_title[]"
                                                    value="{{ $content3_title?->getRawOriginal('value') }}" class="form-control"
                                                    placeholder="{{ translate('Ex_:_Enter_Title') }}">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">{{ translate('messages.Sub Title') }}
                                                    ({{ translate('messages.default') }})<span class="form-label-secondary"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('Write_the_title_within_240_characters') }}">
                                                        <img src="{{ asset('public/assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input type="text" maxlength="240" name="content3_subtitle[]"
                                                    value="{{ $content3_subtitle?->getRawOriginal('value') }}" class="form-control"
                                                    placeholder="{{ translate('Ex_:_Enter_Subtitle') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (isset($content1_title->translations) && count($content1_title->translations)) {
                                        $content1_title_translate = [];
                                        foreach ($content1_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'content1_title') {
                                                $content1_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($content2_title->translations) && count($content2_title->translations)) {
                                        $content2_title_translate = [];
                                        foreach ($content2_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'content2_title') {
                                                $content2_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($content3_title->translations) && count($content3_title->translations)) {
                                        $content3_title_translate = [];
                                        foreach ($content3_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'content3_title') {
                                                $content3_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($content1_subtitle->translations) && count($content1_subtitle->translations)) {
                                        $content1_subtitle_translate = [];
                                        foreach ($content1_subtitle->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'content1_subtitle') {
                                                $content1_subtitle_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($content2_subtitle->translations) && count($content2_subtitle->translations)) {
                                        $content2_subtitle_translate = [];
                                        foreach ($content2_subtitle->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'content2_subtitle') {
                                                $content2_subtitle_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($content3_subtitle->translations) && count($content3_subtitle->translations)) {
                                        $content3_subtitle_translate = [];
                                        foreach ($content3_subtitle->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'content3_subtitle') {
                                                $content3_subtitle_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="d-none lang_form" id="{{ $lang }}-form">
                                        <div class="form-group">
                                            <label class="form-label">{{ translate('content-1') }}</label>
                                            <div class="row g-3 __bg-F8F9FC-card">
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('Title') }}
                                                        ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_80_characters') }}">
                                                        <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span></label>
                                                <input type="text"  maxlength="80" name="content1_title[]"
                                                        value="{{ $content1_title_translate[$lang]['value'] ?? '' }}"
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_Enter_Title') }}">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('messages.Sub Title') }}
                                                        ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_240_characters') }}">
                                                        <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span></label>
                                                <input type="text"  maxlength="240" name="content1_subtitle[]"
                                                        value="{{ $content1_subtitle_translate[$lang]['value'] ?? '' }}"
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_Enter_Subtitle') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">{{ translate('content-2') }}</label>
                                            <div class="row g-3 __bg-F8F9FC-card">
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('Title') }}
                                                        ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_80_characters') }}">
                                                        <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span></label>
                                                <input type="text"  maxlength="80" name="content2_title[]"
                                                        value="{{ $content2_title_translate[$lang]['value'] ?? '' }}"
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_Enter_Title') }}">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('messages.Sub Title') }}
                                                        ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_240_characters') }}">
                                                        <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span></label>
                                                <input type="text"  maxlength="240" name="content2_subtitle[]"
                                                        value="{{ $content2_subtitle_translate[$lang]['value'] ?? '' }}"
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_Enter_Subtitle') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">{{ translate('content-3') }}</label>
                                            <div class="row g-3 __bg-F8F9FC-card">
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('Title') }}
                                                        ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_80_characters') }}">
                                                        <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span></label>
                                                <input type="text"  maxlength="80" name="content3_title[]"
                                                        value="{{ $content3_title_translate[$lang]['value'] ?? '' }}"
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_Enter_Title') }}">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ translate('messages.Sub Title') }}
                                                        ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_240_characters') }}">
                                                        <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span></label>
                                                <input type="text"  maxlength="240" name="content3_subtitle[]"
                                                        value="{{ $content3_subtitle_translate[$lang]['value'] ?? '' }}"
                                                        class="form-control"
                                                        placeholder="{{ translate('Ex_:_Enter_Subtitle') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                @endforeach
                            @endif
                            <div class="btn--container justify-content-end mt-3">
                                <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                                <button type="submit" onclick=""
                                    class="btn btn--primary mb-2">{{ translate('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <form  id="banner_image_form" action="{{ route('admin.remove_image') }}" method="post">
        @csrf
        <input type="hidden" name="id" value="{{  $banner_image?->id}}" >
        {{-- <input type="hidden" name="json" value="1" > --}}
        <input type="hidden" name="model_name" value="ModuleWiseBanner" >
        <input type="hidden" name="image_path" value="promotional_banner" >
        <input type="hidden" name="field_name" value="value" >
    </form>
@endsection
@push('script_2')
<script>
    $(document).ready(function() {
        "use strict"
        $(".__upload-img, .upload-img-4, .upload-img-2, .upload-img-5, .upload-img-1, .upload-img").each(function(){
            var targetedImage = $(this).find('.img');
            var targetedImageSrc = $(this).find('.img img');
            function proPicURL(input) {
                if (input.files && input.files[0]) {
                    var uploadedFile = new FileReader();
                    uploadedFile.onload = function (e) {
                        targetedImageSrc.attr('src', e.target.result);
                        targetedImage.addClass('image-loaded');
                        targetedImage.hide();
                        targetedImage.fadeIn(650);
                    }
                    uploadedFile.readAsDataURL(input.files[0]);
                }
            }
            $(this).find('input').on('change', function () {
                proPicURL(this);
            })
        })
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
            $("#" + lang + "-form1").removeClass('d-none');
            if (lang == '{{ $default_lang }}') {
                $(".from_part_2").removeClass('d-none');
            }
            if (lang == 'default') {
                $(".default-form").removeClass('d-none');
            } else {
                $(".from_part_2").addClass('d-none');
            }
        });
        $(".form-check-input").click(function() {
            console.log($(this).val());
            if ($(this).val() == 'image') {
                $("#image").removeClass('d-none');
                $("#video").addClass('d-none');
            } else {
                $("#video").removeClass('d-none');
                $("#image").addClass('d-none');
            }
        });
    </script>
@endpush
