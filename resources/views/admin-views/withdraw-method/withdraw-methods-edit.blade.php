@extends('layouts.admin.app')

@section('title',  translate('messages.Withdrawal_Methods'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                <h2 class="page-title">
                    {{-- <img width="20" src="{{asset('/public/assets/back-end/img/withdraw-icon.png')}}" alt=""> --}}
                    {{ translate('messages.Withdrawal_Methods')}}
                </h2>

            </div>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <form action="{{route('admin.transactions.withdraw-method.update')}}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" value="{{$withdrawal_method['id']}}" name="id">
                    <div class="p-30">
                        <div class="card card-body">
                            <div class="form-floating">
                                <label>{{ translate('messages.method_name')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                <div class="d-flex justify-content-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" value="1" name="is_default" id="flexCheckDefaultMethod" {{$withdrawal_method['is_default'] == 1 ? 'checked' : ''}}>
                                        <label class="form-check-label" for="flexCheckDefaultMethod">
                                            {{ translate('messages.default_method')}}
                                        </label>
                                    </div>
                                </div>
                                <input type="text" class="form-control " name="method_name" id="method_name"
                                       placeholder="Select method name"
                                       value="{{$withdrawal_method['method_name']}}" required>

                            </div>
                        </div>

                        @if($withdrawal_method['method_fields'][0])
                            @php($field = $withdrawal_method['method_fields'][0])
                            <div class="card card-body mt-3">
                                <div class="row gy-4 align-items-center">
                                    <div class="col-md-3 col-12">
                                        {{-- <div class=""> --}}
                                            <label>{{ translate('messages.Input_Field_Type')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                            <select class="form-control js-select js-select2-custom" name="field_type[]" required>
                                                <option value="string" {{$field=='string'?'selected':''}}>{{ translate('messages.Text')}}</option>
                                                <option value="number" {{$field=='number'?'selected':''}}>{{ translate('messages.Number')}}</option>
                                                <option value="date" {{$field=='date'?'selected':''}}>{{ translate('messages.Date')}}</option>
                                                <option value="email" {{$field=='email'?'selected':''}}>{{ translate('messages.Email')}}</option>
                                                <option value="phone" {{$field=='phone'?'selected':''}}>{{ translate('messages.Phone')}}</option>
                                            </select>
                                        {{-- </div> --}}
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <label>{{ translate('messages.field_name')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="field_name[]"
                                                   placeholder="{{ translate('messages.Ex:_Bank')}}"
                                                   value="{{Str::title(str_replace('_', " ", $field['input_name'])) ??''}}"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-floating">
                                            <label>{{ translate('messages.placeholder_text')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                            <input type="text" class="form-control" name="placeholder_text[]"
                                                   placeholder="{{ translate('messages.Ex:_John')}}"
                                                   value="{{$field['placeholder']??''}}"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1"
                                            name="is_required[0]" id="flexCheckDefault"
                                            {{$field['is_required'] ? 'checked' : ''}}>
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ translate('messages.Is_required_?')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- HERE CUSTOM FIELDS WILL BE ADDED -->
                        <div id="custom-field-section">
                            @foreach($withdrawal_method['method_fields'] as $key=>$field)
                                @if($key>0)
                                    <div class="card card-body mt-3" id="field-row--{{$key}}">
                                        <div class="row gy-4 align-items-center">
                                            <div class="col-md-3 col-12">
                                                <div class="form-floating">
                                                    <label>{{ translate('messages.Input_Field_Type')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                                    <select class="form-control" name="field_type[]" required>
                                                        <option value="string" {{$field['input_type']=='string'?'selected':''}}>{{ translate('messages.Text')}}</option>
                                                        <option value="number" {{$field['input_type']=='number'?'selected':''}}>{{ translate('messages.Number')}}</option>
                                                        <option value="date" {{$field['input_type']=='date'?'selected':''}}>{{ translate('messages.Date')}}</option>
                                                        <option value="email" {{$field['input_type']=='email'?'selected':''}}>{{ translate('messages.Email')}}</option>
                                                        <option value="phone" {{$field['input_type']=='phone'?'selected':''}}>{{ translate('messages.Phone')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12">
                                                <div class="form-floating">
                                                    <label>{{ translate('messages.field_name')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="field_name[]"
                                                           placeholder="{{ translate('messages.Ex:_Bank')}}"
                                                           value="{{  Str::title(str_replace('_', " ", $field['input_name']))  ?? ''}}"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12">
                                                <div class="form-floating">
                                                    <label>{{ translate('messages.placeholder_text')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="placeholder_text[]"
                                                           placeholder="{{ translate('messages.Ex:_John')}}"
                                                           value="{{$field['placeholder'] ?? ''}}"
                                                           required>
                                                </div>
                                            </div>

                                            <div class="col-md-2 col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                           name="is_required[{{$key}}]" id="flexCheckDefault__e{{$key}}"
                                                        {{$field['is_required'] ? 'checked' : ''}}>
                                                    <label class="form-check-label" for="flexCheckDefault__e{{$key}}">
                                                        {{ translate('messages.Is_required_')}}
                                                    </label>
                                                </div>
                                            </div>



                                            <div class="col-md-1 ">
                                                <span class="btn btn-danger" onclick="remove_field({{$key}})">
                                                    <i class="tio-delete"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <button class="btn btn--primary mt-3" id="add-more-field">
                            <i class="tio-add"></i> {{ translate('messages.Add_Fields')}}
                        </button>

                        <!-- BUTTON -->
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn--secondary mx-2">{{ translate('messages.Reset')}}</button>
                            <button type="submit" class="btn btn--primary demo_check">{{ translate('messages.Submit')}}</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection


@push('script_2')
    <script>
        function remove_field(fieldRowId) {
            $( `#field-row--${fieldRowId}` ).remove();
            counter--;
        }
        var count= {{isset($withdrawal_method->method_fields)?count($withdrawal_method->method_fields):0}};
        jQuery(document).ready(function ($) {
            counter = count + 1;

            $('#add-more-field').on('click', function (event) {
                if(counter < 15) {
                    event.preventDefault();

                    $('#custom-field-section').append(
                        `<div class="card card-body mt-3" id="field-row--${counter}">
                            <div class="row gy-4 align-items-center">
                                <div class="col-md-3 col-12">
                                    <label>{{ translate('messages.Input_Field_Type')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <select class="form-control js-select2-custom js-select" name="field_type[]" required>
                                        <option value="" selected disabled>{{ translate('messages.Input_Field_Type')}} <span
                                            class="input-label-secondary text-danger">*</span></option>
                                        <option value="string">{{ translate('messages.Text')}}</option>
                                        <option value="number">{{ translate('messages.Number')}}</option>
                                        <option value="date">{{ translate('messages.Date')}}</option>
                                        <option value="email">{{ translate('messages.Email')}}</option>
                                        <option value="phone">{{ translate('messages.Phone')}}</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="form-floating">
                                        <label>{{ translate('messages.field_name')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                        <input type="text" class="form-control" name="field_name[]"
                                               placeholder="{{ translate('messages.Ex:_Bank')}}" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="form-floating">
                                        <label>{{ translate('messages.placeholder_text')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                        <input type="text" class="form-control" name="placeholder_text[]"
                                               placeholder="{{ translate('messages.Ex:_John')}}" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-2 col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="is_required[${counter}]" id="flexCheckDefault__${counter}" checked>
                                        <label class="form-check-label" for="flexCheckDefault__${counter}">
                                            {{ translate('messages.Is_required_')}}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-1 ">
                                    <span class="btn btn-danger" onclick="remove_field(${counter})">
                                    <i class="tio-delete"></i>
                                    </span>
                                </div>
                            </div>
                        </div>`
                        );

                    $(".js-select").select2();

                    counter++;
                } else {
                    Swal.fire({
                        title: '{{ translate('messages.Reached_maximum')}}',
                        confirmButtonText: '{{ translate('messages.ok')}}',
                    });
                }
            })

            $('form').on('reset', function (event) {
                if(counter > 1) {
                    $('#custom-field-section').html("");
                    $('#method_name').val("");
                }

                counter = 1;
            })
        });
    </script>
@endpush
