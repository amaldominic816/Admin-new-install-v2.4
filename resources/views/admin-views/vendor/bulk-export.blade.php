@extends('layouts.admin.app')

@section('title',translate('messages.restaurant_bulk_export'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/resturant.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{translate('messages.export_stores')}}
                </span>
            </h1>
        </div>
        <div class="card rest-part">
            <div class="card-body p-2">
                <div class="export-steps">
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{translate('STEP 1')}}</h5>
                            <p>
                                {{translate('Select Data Type')}}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{translate('STEP 2')}}</h5>
                            <p>
                                {{translate('Select Data Range and Export')}}
                            </p>
                        </div>
                    </div>
                </div>
                <form class="product-form px-3 pb-3" action="{{route('admin.store.bulk-export')}}" method="POST"
                        enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.type')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="type" id="type" data-placeholder="{{translate('messages.select_type')}}" class="form-control" required title="Select Type">
                                    <option value="all">{{translate('messages.all_data')}}</option>
                                    <option value="date_wise">{{translate('messages.date_wise')}}</option>
                                    <option value="id_wise">{{translate('messages.id_wise')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.start_id')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="start_id" class="form-control">
                            </div>
                            <div class="form-group date_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.from_date')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="from_date" id="date_from" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.end_id')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="end_id" class="form-control">
                            </div>
                            <div class="form-group date_wise">
                                <label class="input-label text-capitalize" for="exampleFormControlSelect1">{{translate('messages.to_date')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="to_date" id="date_to" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button class="btn btn--reset" type="reset">{{translate('messages.clear')}}</button>
                                <button class="btn btn--primary" type="submit">{{translate('messages.export')}}</button>
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
    $(document).on('ready', function (){
        $('#date_from').attr('max',(new Date()).toISOString().split('T')[0]);
        $('#date_to').attr('max',(new Date()).toISOString().split('T')[0]);
        $('.id_wise').hide();
        $('.date_wise').hide();
        $('#type').on('change', function()
        {
            $('.id_wise').hide();
            $('.date_wise').hide();
            $('.'+$(this).val()).show();
        })
    });
</script>
@endpush
