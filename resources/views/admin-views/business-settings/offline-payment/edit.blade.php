@extends('layouts.admin.app')
@section('title', translate('edit_Offline_Payment_Method'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/admin/img/3rd-party.png')}}" alt="">
                {{translate('Edit_Offline_Payment_Method')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Inlile Menu -->
        {{-- @include('admin-views.business-settings.third-party-inline-menu') --}}
        <!-- End Inlile Menu -->

        <form action="{{ route('admin.business-settings.offline.update') }}" method="POST">
            @csrf
            <div class="card mt-3">
                <div class="card-header gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <img width="20" src="{{asset('/public/assets/admin/img/payment-card.png')}}" alt="">
                        <h5 class="mb-0">{{ translate('payment_Information') }}</h5>
                    </div>
                    <a href="javascript:" onclick="add_input_fields_group()" class="btn btn--primary"><i class="tio-add"></i> {{ translate('Add_New_Field') }} </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label for="method_name" class="title_color">{{ translate('payment_Method_Name') }}</label>
                                <input type="text" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('bkash') }}" name="method_name" required value="{{ $data->method_name }}">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id" value="{{ $data->id }}">

                    <div class="input-fields-section" id="input-fields-section">
                        @foreach ($data->method_fields as $key=>$item)
                            @php($aRandomNumber = rand())
                            <div class="row align-items-end" id="{{ $aRandomNumber }}">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="input_name" class="title_color">{{ translate('Title') }}</label>
                                        <input type="text" name="input_name[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('Bank_Name') }}" required value="{{ ucwords(str_replace('_',' ',$item['input_name'])) }} ">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="input_data" class="title_color">{{ translate('Data') }}</label>
                                        <input type="text" name="input_data[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('ABC_bank') }}" required value="{{ $item['input_data'] }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-end">
                                            <a href="javascript:" class="btn action-btn btn--danger btn-outline-danger" title="Delete" onclick="remove_input_fields_group('{{ $aRandomNumber }}')">
                                                 <i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <img width="20" src="{{asset('/public/assets/admin/img/payment-card-fill.png')}}" alt="">
                        <h5 class="mb-0">{{ translate('required_Information_from_Customer') }}</h5>
                    </div>
                    <a href="javascript:" onclick="add_customer_input_fields_group()" class="btn btn--primary"><i class="tio-add"></i> {{ translate('Add_New_Field') }} </a>
                </div>
                <div class="card-body">
                    <div class="customer-input-fields-section" id="customer-input-fields-section">
                        @foreach ($data->method_informations as $key=>$item)
                            @php($cRandomNumber = rand())
                            <div class="row align-items-end" id="{{ $cRandomNumber }}">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="title_color">{{ translate('input_field_Name') }}</label>
                                        <input type="text" name="customer_input[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('payment_By') }}" required value="{{ ucwords(str_replace('_',' ',$item['customer_input'])) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer_placeholder" class="title_color">{{ translate('place_Holder') }}</label>
                                        <input type="text" name="customer_placeholder[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('enter_name') }}" required value="{{ $item['customer_placeholder'] }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div class="form-check text-start mb-3">

                                                <label class="form-check-label text-dark" for="{{ $cRandomNumber+1 }}">
                                                    <input type="checkbox" class="form-check-input" id="{{ $cRandomNumber+1 }}" name="is_required[]" {{ (isset($item['is_required']) && $item['is_required']) == 1 ? 'checked':'' }}> {{ translate('is_Required') }} ?
                                                </label>
                                            </div>

                                            <a class="btn action-btn btn--danger btn-outline-danger" title="Delete"  onclick="remove_input_fields_group('{{ $cRandomNumber }}')">
                                                 <i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="btn--container justify-content-end mt-3">
                <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                <button type="submit" onclick="" class="btn btn--primary mb-2">{{translate('submit')}}</button>
            </div>
        </form>
    </div>
@endsection


@push('script')
<script>
    function remove_input_fields_group(id)
    {
        $('#'+id).remove();
    }

    function add_input_fields_group()
    {
        let id = Math.floor((Math.random() + 1 )* 9999);
        let new_field = `<div class="row align-items-end" id="`+id+`" style="display: none;">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="input_name" class="title_color">{{ translate('Title') }}</label>
                                    <input type="text" name="input_name[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('bank_Name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="input_data" class="title_color">{{ translate('Data') }}</label>
                                    <input type="text" name="input_data[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('AVC_bank') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="d-flex justify-content-end">
                                        <a href="javascript:" class="btn action-btn btn--danger btn-outline-danger" title="Delete" onclick="remove_input_fields_group('`+id+`')">
                                             <i class="tio-delete-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>`;

        $('#input-fields-section').append(new_field);
        $('#'+id).fadeIn();
    }


    function add_customer_input_fields_group()
    {
        let id = Math.floor((Math.random() + 1 )* 9999);
        let new_field = `<div class="row align-items-end" id="`+id+`" style="display: none;">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="title_color">{{ translate('input_field_Name') }}</label>
                                    <input type="text" name="customer_input[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('payment_By') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_placeholder" class="title_color">{{ translate('place_Holder') }}</label>
                                    <input type="text" name="customer_placeholder[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('enter_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div class="form-check text-start mb-3">

                                            <label class="form-check-label text-dark" for="`+id+1+`">
                                                <input type="checkbox" class="form-check-input" id="`+id+1+`" name="is_required[]"> {{ translate('is_Required') }} ?
                                            </label>
                                        </div>

                                        <a class="btn action-btn btn--danger btn-outline-danger" title="Delete"  onclick="remove_input_fields_group('`+id+`')">
                                             <i class="tio-delete-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>`;

        $('#customer-input-fields-section').append(new_field);
        $('#'+id).fadeIn();
    }
</script>
@endpush
