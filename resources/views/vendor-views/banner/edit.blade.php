@extends('layouts.vendor.app')

@section('title',translate('Update Banner'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.update_banner')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('vendor.banner.update', [$banner->id])}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 d-flex justify-content-between">
                                    <h3 class="form-label d-block mb-2">
                                        {{translate('Upload_Banner')}}
                                    </h3>
                                    {{-- <div class="blinkings">
                                        <strong class="mr-2">{{translate('instructions')}}</strong>
                                        <div>
                                            <i class="tio-info-outined"></i>
                                        </div>
                                        <div class="business-notes">
                                            <h6><img src="{{asset('/public/assets/admin/img/notes.png')}}" alt=""> {{translate('Note')}}</h6>
                                            <div>
                                                {{translate('messages.Customer_will_see_there_banners_in_your_store_details_page_in_website_and_user_apps.')}}
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">

                                        <label class="form-label">{{translate('title')}}</label>
                                        <input type="text" name="title" class="form-control" value="{{ $banner->title }}" placeholder="{{translate('messages.title_here...')}}" required>
                                    </div>
                                    <div class="form-group">

                                        <label class="form-label">{{translate('Redirection_URL_/_Link')}}</label>
                                        <input type="url" name="default_link" class="form-control" value="{{ $banner->default_link }}" placeholder="{{translate('messages.Enter_URL')}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="upload-img-3 m-0 d-block">
                                        <div class="img">
                                            <img src="{{asset('storage/app/public/banner')}}/{{$banner['image']}}" id="viewer" onerror='this.src="{{asset('/public/assets/admin/img/upload-4.png')}}"' class="vertical-img mw-100 vertical" alt="">
                                        </div>
                                            <input type="file" name="image"  hidden>
                                    </label>
                                    <h3 class="form-label d-block mt-2">
                                        {{translate('Banner_Image_Ratio_3:1')}}  
                                    </h3>
                                    <p>{{translate('image_format_:_jpg_,_png_,_jpeg_|_maximum_size:_2_MB')}}</p>
                                </div>
                                <div class="col-sm-6">
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-3">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('Reset')}}</button>
                                <button type="submit" class="btn btn--primary mb-2">{{translate('Update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

@push('script_2')
        <script>
            $('#reset_btn').click(function(){
                $('#viewer').attr('src','{{asset('/public/assets/admin/img/upload-4.png')}}');
            })
        </script>
        <script>
            $(document).ready(function() {
                "use strict"
                $(".upload-img-3, .upload-img-4, .upload-img-2, .upload-img-5, .upload-img-1, .upload-img").each(function(){
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
