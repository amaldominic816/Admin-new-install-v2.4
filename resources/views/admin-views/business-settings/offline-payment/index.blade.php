@extends('layouts.admin.app')
@section('title', translate('offline_Payment_Method'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/admin/img/3rd-party.png')}}" alt="">
                {{translate('Offline_Payment_Method_Setup')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="js-nav-scroller hs-nav-scroller-horizontal mb-2">
                <!-- Nav -->
                <ul class="nav nav-tabs border-0 nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ !request()->has('status') ? 'active':'' }}" href="{{route('admin.business-settings.offline')}}">{{ translate('all') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'active' ? 'active':'' }}" href="{{route('admin.business-settings.offline')}}?status=active">{{ translate('active') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'inactive' ? 'active':'' }}" href="{{route('admin.business-settings.offline')}}?status=inactive">{{ translate('inactive') }}</a>
                    </li>

                </ul>
                <!-- End Nav -->
            </div>
        </div>

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-all" role="tabpanel" aria-labelledby="nav-all-tab">
                <div class="card">
                    <!-- Data Table Top -->
                    <div class="px-3 py-4">
                        <div class="row g-2 flex-grow-1">
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <!-- Search -->
                                <form action="{{ route('admin.business-settings.offline') }}" method="GET">
                                    <div class="input-group input-group-custom input-group-merge">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{ translate('search_by_name') }}" aria-label="Search by ID or name" value="{{ request('search') }}" required="">
                                        <button type="submit" class="btn btn--primary input-group-text">{{ translate('search') }}</button>
                                    </div>
                                </form>
                                <!-- End Search -->
                            </div>
                            <div class="col-sm-4 col-md-6 col-lg-8 d-flex justify-content-end">
                                <a href="{{route('admin.business-settings.offline.new')}}" class="btn btn--primary"><i class="tio-add"></i> {{ translate('add_New_Method') }}</a>
                            </div>
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Data Table Top -->

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                                <thead class="thead-light thead-50 text-capitalize">
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('payment_Method_Name') }}</th>
                                        <th>{{ translate('payment_Info') }}</th>
                                        <th>{{ translate('required_Info_From_Customer') }}</th>
                                        <th>{{ translate('status') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($methods as $key => $method)
                                        <tr>
                                            <td>{{$key+$methods->firstItem()}}</td>
                                            <td>{{ $method->method_name }}</td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach ($method->method_fields as $key=>$item)
                                                        <div>{{ ucwords(str_replace('_',' ',$item['input_name'])) }} : {{ $item['input_data'] }}</div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                @foreach ($method->method_informations as $key=>$item)
                                                    {{ ucwords(str_replace('_',' ',$item['customer_input'])) }}
                                                    {{ count($method->method_informations) > ($key+1) ?'|':'' }}
                                                @endforeach
                                            </td>

                                            <td>
                                                <label class="toggle-switch toggle-switch-sm">
                                                    <input type="checkbox" class="toggle-switch-input" onclick="toogleStatusModal(event,'status-{{$method->id}}','this-criteria-on.png','this-criteria-off.png','{{translate('Want_to_enable_this_offline_payment_method?')}}','{{translate('Want_to_disable_this_offline_payment_method?')}}',`<p>{{translate('It_will_be_available_on_the_user_views.')}}</p>`,`<p>{{translate('It_will_be_hidden_from_the_user_views.')}}</p>`)" id="status-{{$method->id}}" {{$method->status?'checked':''}}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                                <form action="{{route('admin.business-settings.offline.status',['id'=>$method->id])}}" method="get" id="status-{{$method->id}}_form">
                                                </form>
                                            </td>

                                            <td>
                                                <div class="btn--container justify-content-center">
                                                    <a class="btn action-btn btn--primary btn-outline-primary" title="Edit" href="{{route('admin.business-settings.offline.edit', ['id'=>$method->id])}}">
                                                        <i class="tio-edit"></i>
                                                    </a>
                                                    <button class="btn action-btn btn--danger btn-outline-danger" title="Delete" onclick="form_alert('delete-method_name-{{ $method->id }}', '{{ translate('Want_to_delete_this_offline_payment_method') }} ?')">
                                                        <i class="tio-delete-outlined"></i>
                                                    </button>

                                                    <form action="{{route('admin.business-settings.offline.delete')}}" method="post" id="delete-method_name-{{ $method->id }}">
                                                        @csrf
                                                        <input type="hidden" value="{{ $method->id }}" name="id" required>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @if ($methods->count() > 0)
                                <div class="p-3 d-flex justify-content-end">
                                    @php
                                        if (request()->has('status')) {
                                            $paginationLinks = $methods->links();
                                            $modifiedLinks = preg_replace('/href="([^"]*)"/', 'href="$1&status='.request('status').'"', $paginationLinks);
                                        } else {
                                            $modifiedLinks = $methods->links();
                                        }
                                    @endphp

                                    {!! $modifiedLinks !!}
                                </div>
                            @else
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
            </div>
        </div>
    </div>
@endsection

@push('script')
{{-- <script>
    function method_status(id) {
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
          });
          $.ajax({
              url: "{{route('admin.business-settings.offline.status')}}",
              method: 'POST',
              data: {
                  id: id
              },
              success: function (data) {
                  if(data.success_status == 1) {
                      toastr.success(data.message);
                      setTimeout(function(){
                          location.reload();
                      }, 1000);
                  }
                  else if(data.success_status == 0) {
                      toastr.error(data.message);
                      setTimeout(function(){
                          location.reload();
                      }, 1000);
                  }
              }
          });
      }
</script> --}}
@endpush
