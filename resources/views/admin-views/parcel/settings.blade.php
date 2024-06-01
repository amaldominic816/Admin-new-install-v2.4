@extends('layouts.admin.app')

@section('title',translate('messages.parcel_settings'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/parcel.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.parcel_settings')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        @php($parcel_per_km_shipping_charge=\App\Models\BusinessSetting::where(['key'=>'parcel_per_km_shipping_charge'])->first())
        @php($parcel_per_km_shipping_charge=$parcel_per_km_shipping_charge?$parcel_per_km_shipping_charge->value:null)

        @php($parcel_minimum_shipping_charge=\App\Models\BusinessSetting::where(['key'=>'parcel_minimum_shipping_charge'])->first())
        @php($parcel_minimum_shipping_charge=$parcel_minimum_shipping_charge?$parcel_minimum_shipping_charge->value:null)

        @php($parcel_commission_dm=\App\Models\BusinessSetting::where(['key'=>'parcel_commission_dm'])->first())
        @php($parcel_commission_dm=$parcel_commission_dm?$parcel_commission_dm->value:null)

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.parcel.update.settings')}}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label  class="input-label text-capitalize">{{translate('messages.per_km_shipping_charge')}}</label>
                                <input type="number" step=".01" placeholder="{{translate('messages.per_km_shipping_charge')}}" class="form-control" name="parcel_per_km_shipping_charge"
                                    value="{{env('APP_MODE')!='demo'?$parcel_per_km_shipping_charge??'':''}}">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{translate('messages.minimum_shipping_charge')}}</label>
                                <input type="number" step=".01" placeholder="{{translate('messages.minimum_shipping_charge')}}" class="form-control" name="parcel_minimum_shipping_charge"
                                    value="{{env('APP_MODE')!='demo'?$parcel_minimum_shipping_charge??'':''}}">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{translate('messages.deliveryman_commission')}} (%)</label>
                                <input type="number" step=".01" placeholder="{{translate('messages.deliveryman_commission')}}" class="form-control" name="parcel_commission_dm" max="100" value="{{env('APP_MODE')!='demo'?$parcel_commission_dm??'':''}}">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" onclick="{{env('APP_MODE')!='demo'?'':'call_demo()'}}" class="btn btn--primary">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-12 pt-sm-3">
            <div class="report-card-inner mb-4 pt-3 mw-100">
                <form action="{{route('admin.parcel.instruction')}}" method="post">
                    @csrf
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-md-0 mb-3">
                        <div class="mx-1">
                            <h5 class="form-label mb-0">
                                {{translate('messages.Add a Delivery Instruction')}}
                            </h5>
                        </div>
                    </div>
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                    @if($language)
                        <ul class="nav nav-tabs nav--tabs mt-3 mb-3 ">
                            <li class="nav-item">
                                <a class="nav-link lang_link1 active"
                                   href="#"
                                   id="default-link1">{{ translate('Default') }}</a>
                            </li>
                            @foreach (json_decode($language) as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link1"
                                       href="#"
                                       id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="row align-items-end">



                        <div class="col-md-10 lang_form1 default-form1">
                            <label class="form-label">{{translate('Instruction')}} ({{ translate('Default') }})</label>
                            <input type="text" class="form-control h--45px" maxlength="191" name="instruction[]"
                                   placeholder="{{ translate('Ex:_parcel_contains_document') }}">
                            <input type="hidden" name="lang[]" value="default">
                        </div>

                        @if ($language)
                            @foreach(json_decode($language) as $lang)
                                <div class="col-md-10 d-none lang_form1" id="{{$lang}}-form1">
                                    <label class="form-label">{{translate('Instruction')}} ({{strtoupper($lang)}})</label>
                                    <input type="text" class="form-control h--45px" maxlength="191" name="instruction[]"
                                           placeholder="{{ translate('Ex:_parcel_contains_document') }}">
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                </div>
                            @endforeach
                        @endif


                        <div class="col-md-auto">
                            <button type="submit" class="btn btn--primary h--45px btn-block">{{translate('messages.Add Now')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-md-0 mb-3">
                    <div class="mx-1">
                        <h5 class="form-label mb-5">
                            {{translate('Delivery Instruction List')}}
                        </h5>
                    </div>
                </div>




                <!-- Table -->
                <div class="card-body p-0">
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-align-middle" data-hs-datatables-options='{
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging":false,
                    }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.SL') }}</th>
                                <th class="border-0">{{translate('messages.Instruction')}}</th>
                                <th class="border-0">{{translate('messages.status')}}</th>
                                <th class="border-0 text-center">{{translate('messages.action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="table-div">
                            @foreach($instructions as $key=>$instruction)
                                <tr>
                                    <td>{{$key+$instructions->firstItem()}}</td>

                                    <td>
                                <span class="d-block font-size-sm text-body" title="{{ $instruction->instruction }}">
                                    {{Str::limit($instruction->instruction, 50,'...')}}
                                </span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$instruction->id}}">
                                            <input type="checkbox" onclick="location.href='{{route('admin.parcel.instruction_status',[$instruction['id'],$instruction->status?0:1])}}'"class="toggle-switch-input" id="stocksCheckbox{{$instruction->id}}" {{$instruction->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                        </label>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                               title="{{ translate('messages.edit') }}" onclick="edit_instruction('{{$instruction['id']}}')"
                                               data-toggle="modal"   data-target="#add_update_instruction_{{$instruction->id}}"
                                            ><i class="tio-edit"></i>
                                            </a>


                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn" href="javascript:"
                                               onclick="form_alert('instruction-{{$instruction['id']}}','{{ translate('Want to delete this instruction ?') }}')"
                                               title="{{translate('messages.delete')}}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.parcel.instruction_delete',[$instruction['id']])}}"
                                                  method="post" id="instruction-{{$instruction['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Modal -->
                                <div class="modal fade" id="add_update_instruction_{{$instruction->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                     aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">{{ translate('messages.Instruction_Update') }}</label></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('admin.parcel.instruction_edit') }}" method="post">
                                                    @csrf
                                                    @method('put')

                                                    @php($instruction=  \App\Models\ParcelDeliveryInstruction::withoutGlobalScope('translate')->with('translations')->find($instruction->id))
                                                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                                    @php($language = $language->value ?? null)
                                                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                                    <ul class="nav nav-tabs nav--tabs mb-3 border-0">
                                                        <li class="nav-item">
                                                            <a class="nav-link lang_link add_active active"
                                                               href="#"
                                                               id="default-link">{{ translate('Default') }}</a>
                                                        </li>
                                                        @if($language)
                                                            @foreach (json_decode($language) as $lang)
                                                                <li class="nav-item">
                                                                    <a class="nav-link lang_link"
                                                                       href="#"
                                                                       id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                    <input type="hidden" name="instruction_id"  value="{{$instruction->id}}" />

                                                    <div class="form-group mb-3 add_active_2  lang_form" id="default-form_{{$instruction->id}}">
                                                        <label class="form-label">{{translate('Instruction')}} ({{translate('messages.default')}}) </label>
                                                        <input class="form-control" name='instruction[]' maxlength="191" value="{{$instruction?->getRawOriginal('instruction')}}" type="text">
                                                        <input type="hidden" name="lang1[]" value="default">
                                                    </div>
                                                    @if($language)
                                                        @forelse(json_decode($language) as $lang)
                                                                <?php
                                                                if($instruction?->translations){
                                                                    $translate = [];
                                                                    foreach($instruction?->translations as $t)
                                                                    {
                                                                        if($t->locale == $lang && $t->key=="instruction"){
                                                                            $translate[$lang]['instruction'] = $t->value;
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            <div class="form-group mb-3 d-none lang_form" id="{{$lang}}-form_{{$instruction->id}}">
                                                                <label class="form-label">{{translate('Instruction')}} ({{strtoupper($lang)}})</label>
                                                                <input class="form-control" name='instruction[]' maxlength="191" value="{{ $translate[$lang]['instruction'] ?? null }}"  type="text">
                                                                <input type="hidden" name="lang1[]" value="{{$lang}}">
                                                            </div>
                                                @empty
                                                @endforelse
                                                @endif

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Close') }}</button>
                                                <button type="submit" class="btn btn-primary">{{ translate('Save_changes') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($instructions) === 0)
                            <div class="empty--data">
                                <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                <h5>
                                    {{translate('no_data_found')}}
                                </h5>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer pt-0 border-0">
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $instructions->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Table -->

        </div>

    </div>

@endsection

@push('script_2')
    <script>

        function edit_instruction(){
            $(".lang_link").removeClass('active');
            $(".add_active").addClass('active');
            $(".lang_form").addClass('d-none');
            $(".add_active_2").removeClass('d-none');
        }

        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(".add_active").removeClass('active');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);

            console.log(lang);

            // $("#"+lang+"-form").removeClass('d-none');

            @foreach ( $instructions as $instruction )
            $("#"+lang+"-form_{{ $instruction->id }}").removeClass('d-none');
            @endforeach
            if(lang == '{{$default_lang}}')
            {
                $(".from_part_2").removeClass('d-none');
            }
            if(lang == 'default')
            {
                $(".default-form").removeClass('d-none');
            }
            else
            {
                $(".from_part_2").addClass('d-none');
            }
        });

        $(".lang_link1").click(function(e){
            e.preventDefault();
            $(".lang_link1").removeClass('active');
            $(".lang_form1").addClass('d-none');
            $(this).addClass('active');
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 6);
            $("#"+lang+"-form1").removeClass('d-none');
            if(lang == 'default')
            {
                $(".default-form1").removeClass('d-none');
            }
        })
    </script>

@endpush
