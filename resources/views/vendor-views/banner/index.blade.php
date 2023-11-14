@extends('layouts.vendor.app')

@section('title',translate('messages.banner'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/fi_9752284.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.Banner_Setup')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{ route('vendor.banner.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 d-flex justify-content-end">

                                    <div class="blinkings">
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
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">

                                        <label class="form-label">{{translate('Banner_title')}}</label>
                                        <input type="text" name="title" class="form-control" placeholder="{{translate('messages.title_here...')}}" required>
                                    </div>
                                    <div class="form-group">

                                        <label class="form-label">{{translate('Redirection_URL_/_Link')}}</label>
                                        <input type="url" name="default_link" class="form-control" placeholder="{{translate('messages.Enter_URL')}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                        <h3 class="form-label d-block mb-2">
                                                {{translate('Upload_Banner')}}
                                            </h3>
                                    <label class="upload-img-3 m-0 d-block">
                                        <div class="img">
                                            <img src="" id="viewer" onerror='this.src="{{asset('/public/assets/admin/img/upload-4.png')}}"' class="vertical-img mw-100 vertical" alt="">
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
                                <button type="submit" class="btn btn--primary mb-2">{{translate('Submit')}}</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{translate('messages.banner_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$banners->count()}}</span>
                            </h5>
                            <form id="search-form" class="search-form">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{translate('messages.search_by_title')}}" aria-label="{{translate('messages.search_here')}}" value="{{ request()->search }}">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,
                                "search": "#datatableSearch",
                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging": false
                               }'
                               >
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{ translate('messages.SL') }}</th>
                                    <th class="border-0">{{translate('messages.title')}}</th>
                                    <th class="border-0">{{translate('messages.banner_Image')}}</th>
                                    <th class="border-0">{{translate('messages.redirection_Link')}}</th>
                                    <th class="border-0 text-center">{{translate('messages.status')}}</th>
                                    <th class="border-0 text-center">{{translate('messages.action')}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($banners as $key=>$banner)
                                <tr>
                                    <td>{{$key+$banners->firstItem()}}</td>
                                    <td><h5 class="text-hover-primary mb-0">{{Str::limit($banner['title'], 25, '...')}}</h5></td>
                                    <td>
                                        <span class="media align-items-center">
                                            <img class="img--ratio-3 w-auto h--50px rounded mr-2" src="{{asset('storage/app/public/banner')}}/{{$banner['image']}}"
                                                 onerror="this.src='{{asset('/public/assets/admin/img/900x400/img1.jpg')}}'" alt="{{$banner->name}} image">
                                        </span>
                                    </td>
                                    <td><a href="{{ $banner->default_link }}"> {{Str::limit($banner['default_link'], 60, '...')}}</a></td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{$banner->id}}">
                                            <input type="checkbox" onclick="location.href='{{route('vendor.banner.status_update',[$banner['id'],$banner->status?0:1])}}'" class="toggle-switch-input" id="statusCheckbox{{$banner->id}}" {{$banner->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('vendor.banner.edit',[$banner['id']])}}"title="{{translate('messages.edit_banner')}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:" onclick="form_alert('banner-{{$banner['id']}}','{{ translate('Want to delete this banner ?') }}')" title="{{translate('messages.delete_banner')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('vendor.banner.delete',[$banner['id']])}}"
                                                        method="post" id="banner-{{$banner['id']}}">
                                                    @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($banners) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $banners->links() !!}
                        </div>
                        @if(count($banners) === 0)
                        <div class="empty--data">
                            <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            <!-- End Table -->
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
