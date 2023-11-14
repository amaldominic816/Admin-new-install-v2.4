@extends('layouts.admin.app')

@section('title', translate('landing_page'))


@section('content')
    <?php
    use Illuminate\Support\Facades\File;
    
    $filePath = resource_path('views/layouts/landing/custom/index.blade.php');
    
    $custom_file = File::exists($filePath);
    ?>
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
        <!-- End Page Header -->
        @php($config = \App\CentralLogics\Helpers::get_business_settings('landing_page'))
        @php($landing_integration_type = \App\CentralLogics\Helpers::get_business_data('landing_integration_type'))
        @php($redirect_url = \App\CentralLogics\Helpers::get_business_data('landing_page_custom_url'))
        <div class="card mb-3">
            <div class="card-body">
                <div
                    class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2">
                    <h5 class="text-capitalize m-0">
                        {{ translate('admin_default_landing_page') }}
                        <i class="tio-info-outined" data-toggle="tooltip"
                            title="{{ translate('You_can_turn_off/on_system-provided_landing_page') }}"></i>
                    </h5>
                    <label class="toggle-switch toggle-switch-sm">
                        <input type="checkbox" class="status toggle-switch-input" onclick="landing_page()"
                            {{ isset($config) && $config ? 'checked' : '' }}>
                        <span class="toggle-switch-label text mb-0">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!--  -->
        <div class="card">
            <div class="card-header flex-wrap border-0">
                <h3 class="card-title">
                    {{ translate('Want_to_Integrate_Your_Own_Customised_Landing_Page_?') }}

                </h3>
                <div class="text--primary d-flex align-items-center gap-3 font-weight-bolder cursor-pointer"
                    data-toggle="modal" data-target="#read-instructions">
                    <span class="mr-2">{{ translate('Read_Instructions') }}</span>
                    <div class="ripple-animation">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                            fill="none" class="svg replaced-svg">
                            <path
                                d="M9.00033 9.83268C9.23644 9.83268 9.43449 9.75268 9.59449 9.59268C9.75449 9.43268 9.83421 9.2349 9.83366 8.99935V5.64518C9.83366 5.40907 9.75366 5.21463 9.59366 5.06185C9.43366 4.90907 9.23588 4.83268 9.00033 4.83268C8.76421 4.83268 8.56616 4.91268 8.40616 5.07268C8.24616 5.23268 8.16644 5.43046 8.16699 5.66602V9.02018C8.16699 9.25629 8.24699 9.45074 8.40699 9.60352C8.56699 9.75629 8.76477 9.83268 9.00033 9.83268ZM9.00033 13.166C9.23644 13.166 9.43449 13.086 9.59449 12.926C9.75449 12.766 9.83421 12.5682 9.83366 12.3327C9.83366 12.0966 9.75366 11.8985 9.59366 11.7385C9.43366 11.5785 9.23588 11.4988 9.00033 11.4993C8.76421 11.4993 8.56616 11.5793 8.40616 11.7393C8.24616 11.8993 8.16644 12.0971 8.16699 12.3327C8.16699 12.5688 8.24699 12.7668 8.40699 12.9268C8.56699 13.0868 8.76477 13.1666 9.00033 13.166ZM9.00033 17.3327C7.84755 17.3327 6.76421 17.1138 5.75033 16.676C4.73644 16.2382 3.85449 15.6446 3.10449 14.8952C2.35449 14.1452 1.76088 13.2632 1.32366 12.2493C0.886437 11.2355 0.667548 10.1521 0.666992 8.99935C0.666992 7.84657 0.885881 6.76324 1.32366 5.74935C1.76144 4.73546 2.35505 3.85352 3.10449 3.10352C3.85449 2.35352 4.73644 1.7599 5.75033 1.32268C6.76421 0.88546 7.84755 0.666571 9.00033 0.666016C10.1531 0.666016 11.2364 0.884905 12.2503 1.32268C13.2642 1.76046 14.1462 2.35407 14.8962 3.10352C15.6462 3.85352 16.24 4.73546 16.6778 5.74935C17.1156 6.76324 17.3342 7.84657 17.3337 8.99935C17.3337 10.1521 17.1148 11.2355 16.677 12.2493C16.2392 13.2632 15.6456 14.1452 14.8962 14.8952C14.1462 15.6452 13.2642 16.2391 12.2503 16.6768C11.2364 17.1146 10.1531 17.3332 9.00033 17.3327ZM9.00033 15.666C10.8475 15.666 12.4206 15.0168 13.7195 13.7185C15.0184 12.4202 15.6675 10.8471 15.667 8.99935C15.667 7.15213 15.0178 5.57907 13.7195 4.28018C12.4212 2.98129 10.8481 2.33213 9.00033 2.33268C7.1531 2.33268 5.58005 2.98185 4.28116 4.28018C2.98227 5.57852 2.3331 7.15157 2.33366 8.99935C2.33366 10.8466 2.98283 12.4196 4.28116 13.7185C5.57949 15.0174 7.15255 15.6666 9.00033 15.666Z"
                                fill="currentColor"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <form id="theme_form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <label class="text-capitalize form-label form--label mb-3">
                        {{ translate('Integrate_Your_Landing_Page_Via') }}
                        <i class="tio-info-outined" data-toggle="tooltip"
                            title="{{ translate('You_can_upload_your_landing_page_either_using_URL_or_File_Upload') }}"></i>
                    </label>
                    <div class="mb-30">
                        <div class="resturant-type-group border d-inline-flex">
                            <label class="form-check form--check mr-2 mr-md-4">
                                <input class="form-check-input" type="radio" value="url" name="landing_integration_via"
                                    {{ $landing_integration_type == 'url' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('messages.url') }}
                                </span>
                            </label>
                            <label class="form-check form--check mr-2 mr-md-4">
                                <input class="form-check-input" type="radio" value="file_upload"
                                    name="landing_integration_via"
                                    {{ $landing_integration_type == 'file_upload' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('file_upload') }}
                                </span>
                            </label>
                            <label class="form-check form--check mr-2 mr-md-4">
                                <input class="form-check-input" type="radio" value="none" name="landing_integration_via"
                                    {{ $landing_integration_type == 'none' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('none') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-30">
                        <div class="__input-tab {{ $landing_integration_type == 'url' ? 'active' : '' }}" id="url">
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group mb-0 pb-2">
                                    <label class="form-label text-capitalize">
                                        {{ translate('landing_page_url') }}
                                    </label>
                                    <input type="text"
                                        placeholder="{{ translate('messages.Ex: https://6ammart-web.6amtech.com/') }}"
                                        class="form-control h--45px" name="redirect_url" value="{{ $redirect_url }}">
                                </div>
                            </div>
                        </div>
                        <div class="__input-tab {{ $landing_integration_type == 'file_upload' ? 'active' : '' }}"
                            id="file_upload">
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group mb-0 pb-2">
                                    <div class="row g-3">
                                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-3">
                                            <!-- Drag & Drop Upload -->
                                            <div class="uploadDnD">
                                                <div class="form-group mb-0 inputDnD bg-white rounded">
                                                    <input type="file" name="file_upload"
                                                        class="form-control-file text--primary font-weight-bold"
                                                        id="inputFile" onchange="readUrl(this)" accept=".zip"
                                                        data-title="Drag & drop file or Browse file">
                                                </div>
                                            </div>

                                            <div class="mt-5 card px-3 py-2 d--none" id="progress-bar">
                                                <div class="d-flex flex-wrap align-items-center gap-3">
                                                    <div class="">
                                                        <img width="24"
                                                            src="{{ asset('/public/assets/admin/img/zip.png') }}"
                                                            alt="">
                                                    </div>
                                                    <div class="flex-grow-1 text-start">
                                                        <div
                                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                            <span id="name_of_file" class="text-truncate fz-12"></span>
                                                            <span class="text-muted fz-12" id="progress-label">0%</span>
                                                        </div>
                                                        <progress id="uploadProgress" class="w-100" value="0"
                                                            max="100"></progress>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-9">
                                            <div class="pl-sm-5">
                                                <h3 class="mb-3 d-flex">{{ translate('instructions') }}</h3>
                                                <ul class="pl-3 d-flex flex-column gap-2 instructions-list mb-0">
                                                    <li>
                                                        {{ translate('Upload_content_as_a_single_ZIP_file_and_the_file_name_must_be') }}
                                                        <b>index.blade.php</b>
                                                    </li>
                                                    {{-- <li>
                                                            Other instructions -1
                                                        </li>
                                                        <li>
                                                            Other instructions -2
                                                        </li> --}}
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @if ($custom_file)    
                            <div class="row g-1 g-sm-2 mt-2">
                                <div class="col-6 col-md-4 col-xxl-3">
                                    <div class="card theme-card">
                                        <div class="card-body d-flex justify-content-between">
                                            <h3>
                                                index.blade.php
                                            </h3>

                                            
                                            <a class="btn action-btn btn--danger btn-outline-danger border-0" href="javascript:"
                                            onclick="form_alert('index_page','{{ translate('Want to delete this index_page ?') }}')" title="{{translate('messages.delete_index_page')}}"><i class="tio-delete-outlined"></i>
                                        </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @endif
                        </div>
                        <div class="__input-tab {{ $landing_integration_type == 'none' ? 'active' : '' }}" id="none">
                            <div class="__bg-F8F9FC-card">

                                @if (isset($config) && $config)
                                    <div class="text-center max-w-595 mx-auto py-4">
                                        <img src="{{ asset('/public/assets/admin/img/landing-icon-2.png') }}"
                                            class="mb-3" alt="">
                                        <p class="m-0">
                                            {{ translate('Currently_you_are_using_6amMart_Default_Admin_Landing_Page_Theme.') }}
                                            <a href="{{ route('home') }}"
                                                class="text--primary text-underline">{{ translate('Visit_Landing_Page') }}</a>
                                        </p>
                                    </div>
                                @else
                                    <div class="text-center max-w-487 mx-auto py-4">
                                        <img src="{{ asset('/public/assets/admin/img/landing-icon-2.png') }}"
                                            class="mb-3" alt="">
                                        <p class="m-0">
                                            {{ translate('You_have_no_business_landing_page_to_show._If_user_search_landing_page_URL_they_will_see_404_page.') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{ translate('Reset') }}</button>
                        <button type="button" onclick="zip_upload()" class="btn btn--primary mb-2" id="update_setting">
                            {{ translate('Save_Information') }}</button>
                    </div>
                </div>
        </div>
        </form>
        <form action="{{route('admin.business-settings.delete-custom-landing-page')}}" method="post" id="index_page">
            @csrf @method('delete')
        </form>

    </div>

    <div class="modal fade" id="read-instructions">
        <div class="modal-dialog status-warning-modal max-w-842">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body px-4 px-md-5 pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{ asset('/public/assets/admin/img/read-instructions.png') }}"
                                        alt="" class="mb-20">
                                    <h5 class="modal-title">
                                        {{ translate('If_you_want_to_set_up_your_own_landing_page_please_follow_tha_instructions_below') }}
                                    </h5>
                                </div>
                                <ol type="1">
                                    <li>
                                        {{ translate('You_can_add_your_customised_landing_page_via_URL_or_upload_ZIP_file_of_the_landing_page.') }}
                                    </li>
                                    <li>
                                        {{ translate('If_you_want_to_use_URL_option._Just_host_you_landing_page_and_copy_the_page_URL_and_click_save_information.') }}
                                    </li>
                                    <li>
                                        {{ translate('If_you_want_to_Upload_your_landing_page_source_code_file.') }}

                                        <div class="ms-2 mt-1">
                                            {{ translate('a._Create_an_html_file_named') }} <b
                                                class="bg--4 text--primary-2">index.blade.php</b>
                                            {{ translate('_and_insert_your_landing_page_design_code_and_make_a_zip_file.') }}

                                        </div>
                                        <div class="ms-2 mt-1">
                                            {{ translate('b._upload_the_zip_file_in_file_upload_section_and_click_save_information.') }}
                                        </div>
                                    </li>
                                </ol>
                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="slide-counter"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script_2')
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script href="{{ asset('public/assets/admin/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <script>
        $("img.svg").each(function() {
            var $img = jQuery(this);
            var imgID = $img.attr("id");
            var imgClass = $img.attr("class");
            var imgURL = $img.attr("src");

            jQuery.get(
                imgURL,
                function(data) {
                    // Get the SVG tag, ignore the rest
                    var $svg = jQuery(data).find("svg");

                    // Add replaced image's ID to the new SVG
                    if (typeof imgID !== "undefined") {
                        $svg = $svg.attr("id", imgID);
                    }
                    // Add replaced image's classes to the new SVG
                    if (typeof imgClass !== "undefined") {
                        $svg = $svg.attr("class", imgClass + " replaced-svg");
                    }

                    // Remove any invalid XML tags as per http://validator.w3.organim
                    $svg = $svg.removeAttr("xmlns:a");

                    // Check if the viewport is set, else we gonna set it if we can.
                    if (
                        !$svg.attr("viewBox") &&
                        $svg.attr("height") &&
                        $svg.attr("width")
                    ) {
                        $svg.attr(
                            "viewBox",
                            "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
                        );
                    }

                    // Replace image with new SVG
                    $img.replaceWith($svg);
                },
                "xml"
            );
        });
    </script>

    <script>
        function readUrl(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = (e) => {
                    let imgData = e.target.result;
                    let imgName = input.files[0].name;
                    input.setAttribute("data-title", imgName);
                    // console.log(e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script>
        function zip_upload() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var formData = new FormData(document.getElementById('theme_form'));
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.business-settings.update-landing-setup') }}",
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    if ($('#inputFile').val()) {
                        $('#progress-bar').show();
                    }

                    // Listen to the upload progress event
                    xhr.upload.addEventListener("progress", function(e) {
                        if (e.lengthComputable) {
                            var percentage = Math.round((e.loaded * 100) / e.total);
                            $("#uploadProgress").val(percentage);
                            $("#progress-label").text(percentage + "%");
                        }
                    }, false);

                    return xhr;
                },
                beforeSend: function() {
                    $('#update_setting').attr('disabled');
                },
                success: function(response) {
                    if (response.status == 'error') {
                        $('#progress-bar').hide();
                        toastr.error(response.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    } else if (response.status == 'success') {
                        toastr.success(response.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        location.reload();
                    }
                },
                complete: function() {
                    $('#update_setting').removeAttr('disabled');
                },
            });
        }

        function publish_addon(path) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.business-settings.system-addon.publish') }}',
                data: {
                    'path': path
                },
                success: function(data) {
                    if (data.flag === 'inactive') {
                        // console.log(data.view)
                        $('#activatedThemeModal').modal('show');
                        $('#activateData').empty().html(data.view);
                    } else {
                        if (data.errors) {
                            for (var i = 0; i < data.errors.length; i++) {
                                toastr.error(data.errors[i].message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        } else {
                            toastr.success('{{ translate('updated successfully!') }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            setTimeout(function() {
                                location.reload()
                            }, 2000);
                        }
                    }
                }
            });
        }

        function theme_delete(path) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.business-settings.system-addon.delete') }}',
                data: {
                    path
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.status === 'success') {
                        setTimeout(function() {
                            location.reload()
                        }, 2000);

                        toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    } else if (data.status === 'error') {
                        toastr.error(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        }

        var swiper = new Swiper(".mySwiper", {
            pagination: {
                el: ".swiper-pagination",
                dynamicBullets: true,
            },
        });
    </script>
    <script>
        function landing_page(message) {
            @if (env('APP_MODE') == 'demo')
                toastr.warning('Sorry! You can not change landing page in demo!');
            @else
                Swal.fire({
                    title: '{{ isset($config) && $config ? translate('messages.Want_to_Turn_Off_the_Default_Admin_Landing_Page_?') : translate('messages.Want_to_Turn_On_the_Default_Admin_Landing_Page_?') }}',
                    text: '{{ isset($config) && $config ? translate('If_disabled,_the_landing_page_won’t_be_visible_to_anyone') : translate('If_enabled,_the_landing_page_will_be_visible_to_everyone') }}',
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#00868F',
                    cancelButtonText: '{{ translate('messages.no') }}',
                    confirmButtonText: '{{ translate('messages.yes') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.get({
                            url: '{{ route('admin.landing-page') }}',
                            contentType: false,
                            processData: false,
                            beforeSend: function() {
                                $('#loading').show();
                            },
                            success: function(data) {
                                toastr.success(data.message);
                                location.reload();
                            },
                            complete: function() {
                                $('#loading').hide();
                            },
                        });
                    } else {
                        location.reload();
                    }
                })
            @endif
        };
    </script>
    <script>
        $('input[name="landing_integration_via"]').on('change', function() {
            $(`.__input-tab`).removeClass('active')
            $(`#${this.value}`).addClass('active')
        })
    </script>
    <script>
        $('#reset_btn').click(function() {
            $('.uploadDnD').empty().append(`<div class="form-group mb-0 inputDnD bg-white rounded">
                                                        <input type="file" name="file_upload" class="form-control-file text--primary font-weight-bold"
                                                        id="inputFile" onchange="readUrl(this)" accept=".zip" data-title="Drag & drop file or Browse file">
                                                    </div>`)
            $(`.__input-tab`).removeClass('active')
            $(`#{{ $landing_integration_type }}`).addClass('active')
        })
    </script>
@endpush
