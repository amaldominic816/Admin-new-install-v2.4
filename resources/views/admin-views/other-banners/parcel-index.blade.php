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
                        <form action="{{ route('admin.promotional-banner.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="text" name="key" value="promotional_banner"  hidden>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 d-flex justify-content-between">
                                            <span class="d-flex g-1">
                                                <img src="{{asset('public/assets/admin/img/other-banner.png')}}" class="h-85" alt="">
                                                <h3 class="form-label d-block mb-2">
                                                    {{translate('messages.Promotional Banners')}}
                                                </h3>
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label class="__upload-img aspect-4-1 m-auto d-block">
                                                <div class="img">
                                                    <img src="" onerror='this.src="{{asset('/public/assets/admin/img/upload-placeholder.png')}}"' alt="">
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
                                        <button type="submit" class="btn btn--primary mb-2">{{translate('Submit')}}</button>
                                    </div>
                                </div>
                            </form>
                            @php($banners=\App\Models\ModuleWiseBanner::where('module_id',Config::get('module.current_module_id'))->where('key','promotional_banner')->get())
                            {{-- <div class="card"> --}}
                                <div class="card-header py-2">
                                    <div class="search--button-wrapper">
                                        <h5 class="card-title">{{translate('Promotional_Banner_List')}}
                                        </h5>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <!-- Table -->
                                    <div class="table-responsive datatable-custom">
                                        <table id="columnSearchDatatable"
                                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                                data-hs-datatables-options='{
                                                    "order": [],
                                                    "orderCellsTop": true,
                                                    "paging":false

                                                }'>
                                            <thead class="thead-light">
                                            <tr>
                                                <th class="border-0">{{translate('sl')}}</th>
                                                <th class="border-0">{{translate('Image')}}</th>
                                                <th class="border-0">{{translate('Status')}}</th>
                                                <th class="text-center border-0">{{translate('messages.action')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($banners as $key=>$banner)
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>
                                                        <img src="{{asset('storage/app/public/promotional_banner')}}/{{$banner->value}}"
                                                        onerror="this.src='{{asset('/public/assets/admin/img/upload-3.png')}}'" class="__size-105" alt="">
                                                    </td>
                                                    <td>
                                                        <label class="toggle-switch toggle-switch-sm">
                                                            <input type="checkbox" class="toggle-switch-input" onclick="toogleStatusModal(event,'status-{{$banner->id}}','promotional-on.png','promotional-off.png','{{translate('By Turning ONN Promotional Banner Section')}}','{{translate('By Turning OFF Promotional Banner Section')}}',`<p>{{translate('Promotional banner will be enabled. You will be able to see promotional activity')}}</p>`,`<p>{{translate('Promotional banner will be disabled. You will be unable to see promotional activity')}}</p>`)" id="status-{{$banner->id}}" {{$banner->status?'checked':''}}>
                                                            <span class="toggle-switch-label">
                                                                <span class="toggle-switch-indicator"></span>
                                                            </span>
                                                        </label>
                                                        <form action="{{route('admin.promotional-banner.update-status',[$banner->id,$banner->status?0:1])}}" method="get" id="status-{{$banner->id}}_form">
                                                        </form>
                                                    </td>

                                                    <td>
                                                        <div class="btn--container justify-content-center">
                                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.promotional-banner.edit',[$banner['id']])}}">
                                                                <i class="tio-edit"></i>
                                                            </a>
                                                            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:"
                                                            onclick="form_alert('banner-{{$banner['id']}}','{{ translate('Want to delete this banner ?') }}')" title="{{translate('messages.delete_banner')}}"><i class="tio-delete-outlined"></i>
                                                            </a>
                                                            <form action="{{route('admin.promotional-banner.delete',[$banner['id']])}}" method="post" id="banner-{{$banner['id']}}">
                                                                @csrf @method('delete')
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                    <!-- End Table -->
                                </div>
                                @if(count($banners) === 0)
                                <div class="empty--data">
                                    <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                    <h5>
                                        {{translate('no_data_found')}}
                                    </h5>
                                </div>
                                @endif
                            {{-- </div> --}}
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

