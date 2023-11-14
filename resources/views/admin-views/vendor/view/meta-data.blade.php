@extends('layouts.admin.app')

@section('title',$store->name."'s ".translate('messages.settings'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/admin/css/croppie.css')}}" rel="stylesheet">

@endpush

@section('content')
<div class="content container-fluid">
    @include('admin-views.vendor.view.partials._header',['store'=>$store])
    <!-- Page Heading -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="vendor">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <img class="w--22" src="{{asset('public/assets/admin/img/store.png')}}" alt="">
                        </span>
                        <span class="p-md-1"> {{translate('messages.store_meta_data')}}</span>
                    </h5>
                </div>
                @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                @php($language = $language->value ?? null)
                @php($default_lang = 'en')
                <div class="card-body">
                    <form action="{{route('admin.store.update-meta-data',[$store['id']])}}" method="post"
                    enctype="multipart/form-data" class="col-12">
                    @csrf
                        <div class="row g-2">
                            <div class="col-lg-6">
                                <div class="card shadow--card-2">
                                    <div class="card-body">
                                        @if($language)
                                        <ul class="nav nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active"
                                                href="#"
                                                id="default-link">{{ translate('Default') }}</a>
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
                                        <div class="lang_form"
                                        id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="default_title">{{ translate('messages.meta_title') }}
                                                    ({{ translate('messages.Default') }})
                                                </label>
                                                <input type="text" name="meta_title[]" id="default_title"
                                                    class="form-control" placeholder="{{ translate('messages.meta_title') }}" value="{{$store->getRawOriginal('meta_title')}}"

                                                    oninvalid="document.getElementById('en-link').click()">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="form-group mb-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.meta_description') }} ({{ translate('messages.default') }})</label>
                                                <textarea type="text" name="meta_description[]" placeholder="{{translate('messages.meta_description')}}" class="form-control min-h-90px ckeditor">{{$store->getRawOriginal('meta_description')}}</textarea>
                                            </div>
                                        </div>
                                            @foreach (json_decode($language) as $lang)
                                            <?php
                                                if(count($store['translations'])){
                                                    $translate = [];
                                                    foreach($store['translations'] as $t)
                                                    {
                                                        if($t->locale == $lang && $t->key=="meta_title"){
                                                            $translate[$lang]['meta_title'] = $t->value;
                                                        }
                                                        if($t->locale == $lang && $t->key=="meta_description"){
                                                            $translate[$lang]['meta_description'] = $t->value;
                                                        }
                                                    }
                                                }
                                            ?>
                                                <div class="d-none lang_form"
                                                    id="{{ $lang }}-form">
                                                    <div class="form-group">
                                                        <label class="input-label"
                                                            for="{{ $lang }}_title">{{ translate('messages.meta_title') }}
                                                            ({{ strtoupper($lang) }})
                                                        </label>
                                                        <input type="text" name="meta_title[]" id="{{ $lang }}_title"
                                                            class="form-control" value="{{ $translate[$lang]['meta_title']??'' }}" placeholder="{{ translate('messages.meta_title') }}"
                                                            oninvalid="document.getElementById('en-link').click()">
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label"
                                                            for="exampleFormControlInput1">{{ translate('messages.meta_description') }} ({{ strtoupper($lang) }})</label>
                                                        <textarea type="text" name="meta_description[]" placeholder="{{translate('messages.meta_description')}}" class="form-control min-h-90px ckeditor">{{ $translate[$lang]['meta_description']??'' }}</textarea>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div id="default-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ translate('messages.meta_title') }} ({{ translate('messages.default') }})</label>
                                                    <input type="text" name="meta_title[]" class="form-control"
                                                        placeholder="{{ translate('messages.meta_title') }}" >
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                <div class="form-group mb-0">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ translate('messages.meta_description') }}
                                                    </label>
                                                    <textarea type="text" name="meta_description[]" placeholder="{{translate('messages.meta_description')}}" class="form-control min-h-90px ckeditor"></textarea>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card shadow--card-2">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                            <span>{{translate('store_meta_image')}}</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap flex-sm-nowrap __gap-12px">
                                            <label class="__custom-upload-img mr-lg-5">
                                                <div class="position-relative">
                                                <label class="form-label">
                                                    {{ translate('meta_image') }} <span class="text--primary">({{ translate('1:1') }})</span>
                                                </label>
                                                <center>
                                                    <img class="img--110 min-height-170px min-width-170px" id="viewer"
                                                        onerror="this.src='{{ asset('public/assets/admin/img/upload.png') }}'"
                                                        src="{{asset('storage/app/public/store').'/'.$store->meta_image}}" alt="{{$store->name}}"
                                                        alt="{{ translate('meta_image') }}" />
                                                </center>
                                                <input type="file" name="meta_image" id="customFileEg1" class="custom-file-input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                                    @if (isset($store->meta_image))
                                                    <span id="earning_delivery_img" class="remove_image_button mt-4"
                                                        onclick="toogleStatusModal(event,'earning_delivery_img','mail-success','mail-warning','{{translate('Important!')}}','{{translate('Warning!')}}',`<p>{{translate('Are_you_sure_you_want_to_remove_this_image')}}</p>`,`<p>{{translate('Are_you_sure_you_want_to_remove_this_image.')}}</p>`)"
                                                        > <i class="tio-clear"></i></span>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="justify-content-end btn--container">
                                    <button type="submit" class="btn btn--primary">{{translate('save_changes')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<form  id="earning_delivery_img_form" action="{{ route('admin.remove_image') }}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{  $store?->id}}" >
    {{-- <input type="hidden" name="json" value="1" > --}}
    <input type="hidden" name="model_name" value="Store" >
    <input type="hidden" name="image_path" value="store" >
    <input type="hidden" name="field_name" value="meta_image" >
</form>
@endsection

@push('script_2')
<script>
    function readURL(input, viewer) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#'+viewer).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#customFileEg1").change(function () {
        readURL(this, 'viewer');
    });

    $("#coverImageUpload").change(function () {
        readURL(this, 'coverImageViewer');
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
        if(lang == '{{$default_lang}}')
        {
            $(".from_part_2").removeClass('d-none');
        }
        else
        {
            $(".from_part_2").addClass('d-none');
        }
    });
</script>
@endpush
