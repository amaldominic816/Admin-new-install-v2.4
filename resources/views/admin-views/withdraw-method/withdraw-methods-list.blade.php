@extends('layouts.admin.app')

@section('title', translate('messages.disbursement_method_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                    {{-- <img width="20" src="{{asset('/public/assets/back-end/img/withdraw-icon.png')}}" alt=""> --}}
                    {{ translate('messages.wallet_method_list')}}
                </h2>
                <a href="{{route('admin.transactions.withdraw-method.create')}}" class="btn btn--primary">+ {{ translate('messages.Add_method')}}</a>
            </div>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="p-3">
                        <div class="row gy-1 align-items-center justify-content-between">
                            <div class="col-auto">
                                <h5>
                                {{  translate('messages.methods')}}
                                    <span class="badge badge-soft-dark radius-50 fz-12 ml-1"> {{ $withdrawal_methods->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-auto">
                                <form  class="search-form">
                                    <!-- Search -->
                                    <div class="input-group input--group">
                                        <input id="datatableSearch" name="search" type="search" value="{{ $search }}"class="form-control h--40px" placeholder="{{ translate('messages.Search_Method_Name')}}" aria-label="{{translate('messages.search_here')}}">
                                        <button type="submit" class="btn btn--secondary h--40px"><i class="tio-search"></i></button>
                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable"
                                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('messages.sl')}}</th>
                                <th>{{ translate('messages.method_name')}}</th>
                                <th>{{  translate('messages.method_fields') }}</th>
                                <th>{{ translate('messages.active_status')}}</th>
                                <th >{{ translate('messages.default_method')}}</th>
                                <th class="text-center">{{ translate('messages.action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($withdrawal_methods as $key=>$withdrawal_method)
                                <tr>
                                    <td>{{$withdrawal_methods->firstitem()+$key}}</td>
                                    <td>{{$withdrawal_method['method_name']}}</td>


                                    <td>
                                        @foreach($withdrawal_method['method_fields'] as $key=>$method_field)
                                            <span class="badge badge-secondary opacity-75 fz-12 border border-white">
                                                <b>{{ translate('messages.Name')}}:</b> {{ translate($method_field['input_name'])}} |
                                                <b>{{ translate('messages.Type')}}:</b> {{ translate($method_field['input_type']) }} |
                                                <b>{{ translate('messages.Placeholder')}}:</b> {{ $method_field['placeholder'] }} |
                                                {{ $method_field['is_required'] ? translate('messages.Required') :  translate('messages.Optional') }}
                                            </span><br/>
                                        @endforeach
                                    </td>



                                    <td>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input class="toggle-switch-input status"
                                                   onclick="featured_status('{{$withdrawal_method->id}}')"
                                                   type="checkbox" {{$withdrawal_method->is_active?'checked':''}}>
                                                   <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                        </label>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" class="default-method toggle-switch-input"
                                            id="{{$withdrawal_method->id}}" {{$withdrawal_method->is_default == 1?'checked':''}}>
                                                   <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                        </label>
                                    </td>



                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a href="{{route('admin.transactions.withdraw-method.edit',[$withdrawal_method->id])}}"
                                               class="btn btn-sm btn--primary btn-outline-primary action-btn">
                                                <i class="tio-edit"></i>
                                            </a>

                                            @if(!$withdrawal_method->is_default)
                                                <a class="btn btn-sm btn--danger btn-outline-danger action-btn" href="javascript:"
                                                   title="{{ translate('messages.Delete')}}"
                                                   onclick="form_alert('delete-{{$withdrawal_method->id}}','Want to delete this item ?')">
                                                    <i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('admin.transactions.withdraw-method.delete',[$withdrawal_method->id])}}"
                                                      method="post" id="delete-{{$withdrawal_method->id}}">
                                                    @csrf @method('delete')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($withdrawal_methods)==0)
                            <div class="empty--data">
                                <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                            </div>
                       @endif
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-center justify-content-md-end">
                            <!-- Pagination -->
                            {{$withdrawal_methods->links()}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection


@push('script_2')
  <script>
      $(document).on('change', '.default-method', function () {
          let id = $(this).attr("id");
          let status = $(this).prop("checked") === true ? 1:0;

          $.ajaxSetup({
              headers: {
                //   'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              }
          });
          $.ajax({
              url: "{{route('admin.transactions.withdraw-method.default-status-update')}}",
              method: 'POST',
              data: {
                  id: id,
                  status: status
              },
              success: function (data) {
                  if(data.success == true) {
                      toastr.success('{{ translate('messages.Default_Method_updated_successfully')}}');
                      setTimeout(function(){
                          location.reload();
                      }, 1000);
                  }
                  else if(data.success == false) {
                      toastr.error('{{ translate('messages.Default_Method_updated_failed.')}}');
                      setTimeout(function(){
                          location.reload();
                      }, 1000);
                  }
              }
          });
      });

      function featured_status(id) {
          $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                //   'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
          });
          $.ajax({
              url: "{{route('admin.transactions.withdraw-method.status-update')}}",
              method: 'POST',
              data: {
                  id: id
              },
              success: function (data) {
                  toastr.success('{{ translate('messages.status_updated_successfully')}}');
              }
          });
      }
  </script>
@endpush
