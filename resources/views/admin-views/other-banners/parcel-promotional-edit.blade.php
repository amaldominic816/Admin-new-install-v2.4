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
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <div class="row g-3">
                <div class="col-lg-12 mb-3 mb-lg-2">
                    <div class="card h-100">
                        <form action="{{ route('admin.promotional-banner.update',[$banner['id']]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="text" name="key" value="promotional_banner"  hidden>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 d-flex justify-content-between">
                                            <span class="d-flex g-1">
                                                <img src="{{asset('public/assets/admin/img/other-banner.png')}}" class="h-85" alt="">
                                                <h3 class="form-label d-block mb-2">
                                                    {{translate('messages.Promotional_Banner_Edit')}}
                                                </h3>
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label class="__upload-img aspect-4-1 m-auto d-block">
                                                <div class="img">
                                                    <img src="{{asset('storage/app/public/promotional_banner')}}/{{$banner->value}}" onerror='this.src="{{asset('/public/assets/admin/img/upload-placeholder.png')}}"' alt="">
                                                </div>
                                                    <input type="file" name="image"  hidden>
                                            </label>
                                            <div class="text-center mt-5">
                                                <h3 class="form-label d-block mt-2">
                                                {{translate('Banner_Image_Ratio_4:1')}}  
                                            </h3>
                                            <p>{{translate('image_format_:_jpg_,_png_,_jpeg_|_maximum_size:_2_MB')}}</p>
        
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn--container justify-content-end mt-3">
                                        <button type="submit" class="btn btn--primary mb-2">{{translate('messages.Update')}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script_2')
        <script>
            $('#reset_btn').click(function(){
                $('#viewer').attr('src','{{asset('/public/assets/admin/img/upload-placeholder.png')}}');
            })
        </script>
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
@endpush

