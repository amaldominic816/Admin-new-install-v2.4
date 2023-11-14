@extends('layouts.vendor.app')

@section('title', translate('Item Preview'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between">
                <h1 class="page-header-title text-break">
                    <span class="page-header-icon">
                        <img src="{{ asset('public/assets/admin/img/temp_pro.png') }}" class="w--22" alt="">
                    </span>
                    <span>{{ translate('Product_Details') }}</span>
                </h1>

            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3">
            <!-- Body -->
            <div class="card-body">
                <div class="row flex-wrap">
                    <div>
                        <div class="d-flex flex-wrap align-items-center food--media position-relative mr-4">
                            <img class="avatar avatar-xxl avatar-4by3"
                                src="{{ asset('storage/app/public/product') }}/{{ $product['image'] }}"
                                onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'"
                                alt="Image Description">
                                @if ($product['is_rejected'] == 1 )
                                <div class="reject-info"> {{ translate('Your_Item_Has_Been_Rejected') }}</div>
                                @else
                                <div class="pending-info"> {{ translate('This_Item_Is_Under_Review') }}</div>
                                @endif
                        </div>
                    </div>
                    <div class="w-70 flex-grow">
                        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first()?->value ?? null)
                        @php($default_lang = str_replace('_', '-', app()->getLocale()))
                        <div class="d-flex flex-wrap gap-2 justify-content-between">
                            @if ($language)
                            <ul class="nav nav-tabs border-0 mb-3">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#"
                                        id="default-link">{{ translate('messages.default') }}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#"
                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                                <div class="d-flex flex-wrap gap-2 align-items-start">
                                    <a class="btn btn--sm btn-outline-danger" href="javascript:"
                                    onclick="form_alert('food-{{$product['id']}}','{{ translate('Want to delete this item ?') }}')" title="{{translate('messages.delete_item')}}">{{ translate('messages.Delete') }} <i class="tio-delete-outlined"></i>
                                    </a>
                                    <a href="{{ route('vendor.item.edit', [$product['id'],'temp_product' => true]) }}" class="btn btn--sm btn-outline-primary">
                                        <i class="tio-edit"></i>  {{ translate('messages.edit_&_Resubmit') }}
                                    </a>
                                <form action="{{route('vendor.item.delete',[$product['id']])}}"
                                        method="post" id="food-{{$product['id']}}">
                                    @csrf @method('delete')
                                    <input type="hidden" value="1" name="temp_product" >
                                </form>


                                </div>
                            </div>

                        <div class="lang_form" id="default-form">
                            <h2 class="mt-3">{{ $product?->getRawOriginal('name') }} </h2>
                            <h6> {{ translate('description') }}:</h6>
                            <P> {{ $product?->getRawOriginal('description') }}</P>
                        </div>

                        @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (count($product['translations'])) {
                                        $translate = [];
                                        foreach ($product['translations'] as $t) {
                                            if ($t->locale == $lang && $t->key == 'name') {
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                            if ($t->locale == $lang && $t->key == 'description') {
                                                $translate[$lang]['description'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="d-none lang_form" id="{{ $lang }}-form">
                                        <h2>{{ $translate[$lang]['name'] ?? '' }} </h2>
                                        <h6> {{ translate('description') }}:</h6>
                                        <P> {!! $translate[$lang]['description'] ?? '' !!}</P>
                                    </div>
                        @endforeach
                    </div>
                </div>


            </div>
            <!-- End Body -->
        </div>

    <!-- Description Card Start -->
    <div class="card mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ translate('General_Information') }}</h4>
                            </th>
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ translate('price_Information') }}</h4>
                            </th>
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ translate('Available_Variations') }}</h4>
                            </th>
                            @if ($product->module->module_type == 'food')
                                <th class="px-4 border-0">
                                    <h4 class="m-0 text-capitalize">{{ translate('addons') }}</h4>
                                </th>
                            @endif
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ translate('tags') }}</h4>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 max-w--220px">
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.Store') }} : </span>
                                    <strong>{{ $product?->store?->name }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.Category') }} : </span>
                                    <strong>{{ Str::limit(($product?->category?->parent ? $product?->category?->parent?->name : $product?->category?->name )  ?? translate('messages.uncategorize')
                                        , 20, '...') }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.Sub_Category') }} : </span>
                                    <strong>{{ Str::limit(($product?->category?->name )  ?? translate('messages.uncategorize')
                                        , 20, '...') }}</strong>
                                </span>
                                @if ($product->module->module_type == 'grocery')
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.Is_Organic') }} : </span>
                                    <strong> {{  $product->organic == 1 ?  translate('messages.yes') : translate('messages.no') }}</strong>
                                </span>
                                @endif
                                @if ($product->module->module_type == 'food')
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.Item_type') }} : </span>
                                    <strong> {{  $product->veg == 1 ?  translate('messages.veg') : translate('messages.non_veg') }}</strong>
                                </span>
                                @else
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.Total_stock') }} : </span>
                                    <strong> {{  $product->stock  }}</strong>
                                </span>

                                    @if ($product?->unit)
                                    <span class="d-block mb-1">
                                        <span>{{ translate('messages.Unit') }} : </span>
                                        <strong> {{ $product?->unit?->unit  }}</strong>
                                    </span>
                                    @endif
                                @endif
                                @if (config('module.' . $product->module->module_type)['item_available_time'])
                                <span class="d-block mb-1">
                                    {{ translate('messages.available_time_starts') }} :
                                    <strong>{{ date(config('timeformat'), strtotime($product['available_time_starts'])) }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    {{ translate('messages.available_time_ends') }} :
                                    <strong>{{ date(config('timeformat'), strtotime($product['available_time_ends'])) }}</strong>
                                </span>
                            @endif
                            </td>
                            <td class="px-4">
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.Unit_Price') }} : </span>
                                    <strong>{{ \App\CentralLogics\Helpers::format_currency($product['price']) }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.discounted_amount') }} :</span>
                                    <strong>{{ \App\CentralLogics\Helpers::format_currency(\App\CentralLogics\Helpers::discount_calculate($product, $product['price'])) }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.discount') }} :</span>
                                    <strong> {{ $product->discount_type == 'percent' ? $product->discount .'%' :  \App\CentralLogics\Helpers::format_currency($product['discount']) }} </strong>
                                </span>

                                @php($tax_included = \App\Models\BusinessSetting::where('key', 'tax_included')->first()?->value)

                                <span class="d-block mb-1">
                                    <span>{{ translate('messages.tax') }} :</span>
                                    <strong> {{ $product?->store?->tax .'%' }}  ({{  $tax_included == 1 ? translate('included') :  translate('excluded')}})</strong>
                                </span>

                            </td>
                            <td class="px-4">
                                @if ($product->module->module_type == 'food')
                                    @if ($product->food_variations && is_array(json_decode($product['food_variations'], true)))
                                        @foreach (json_decode($product->food_variations, true) as $variation)
                                            @if (isset($variation['price']))
                                                <span class="d-block mb-1 text-capitalize">
                                                    <strong>
                                                        {{ translate('please_update_the_food_variations.') }}
                                                    </strong>
                                                </span>
                                            @break

                                        @else
                                            <span class="d-block text-capitalize">
                                                <strong>
                                                    {{ $variation['name'] }} -
                                                </strong>
                                                @if ($variation['type'] == 'multi')
                                                    {{ translate('messages.multiple_select') }}
                                                @elseif($variation['type'] == 'single')
                                                    {{ translate('messages.single_select') }}
                                                @endif
                                                @if ($variation['required'] == 'on')
                                                    - ({{ translate('messages.required') }})
                                                @endif
                                            </span>

                                            @if ($variation['min'] != 0 && $variation['max'] != 0)
                                                ({{ translate('messages.Min_select') }}: {{ $variation['min'] }} -
                                                {{ translate('messages.Max_select') }}: {{ $variation['max'] }})
                                            @endif

                                            @if (isset($variation['values']))
                                                @foreach ($variation['values'] as $value)
                                                    <span class="d-block text-capitalize">
                                                        &nbsp; &nbsp; {{ $value['label'] }} :
                                                        <strong>{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</strong>
                                                    </span>
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            @else
                                @if ($product->variations && is_array(json_decode($product['variations'], true)))
                                    @foreach (json_decode($product['variations'], true) as $variation)
                                        <span class="d-block mb-1 text-capitalize">
                                            {{ $variation['type'] }} :
                                            {{ \App\CentralLogics\Helpers::format_currency($variation['price']) }}
                                        </span>
                                    @endforeach
                                @endif
                        </td>
                        @endif
                        @if ($product->module->module_type == 'food')
                            <td class="px-4">
                                {{-- @if (config('module.' . $product->module->module_type)['add_on']) --}}
                                    @foreach (\App\Models\AddOn::whereIn('id', json_decode($product['add_ons'], true))->get() as $addon)
                                        <span class="d-block mb-1 text-capitalize">
                                            {{ $addon['name'] }} :
                                            {{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}
                                        </span>
                                    @endforeach
                                {{-- @endif --}}
                            </td>
                        @endif

                        @php( $tags =\App\Models\Tag::whereIn('id',json_decode($product?->tag_ids) )->get('tag'))
                            <td>
                                @foreach($tags as $c) {{$c->tag.','}} @endforeach
                            </td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Description Card End -->

</div>
@endsection

@push('script_2')
<script>
        function request_alert(url, message) {
            Swal.fire({
                title: '{{translate('messages.are_you_sure')}}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{translate('messages.no')}}',
                confirmButtonText: '{{translate('messages.yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    $(".lang_link").click(function(e) {
        e.preventDefault();
        $(".lang_link").removeClass('active');
        $(".lang_form").addClass('d-none');
        $(this).addClass('active');

        let form_id = this.id;
        let lang = form_id.substring(0, form_id.length - 5);
        console.log(lang);
        $("#" + lang + "-form").removeClass('d-none');
        if (lang == 'en') {
            $("#from_part_2").removeClass('d-none');
        } else {
            $("#from_part_2").addClass('d-none');
        }
    })
    function cancelled_status(route, message, processing = false) {
            Swal.fire({
                    //text: message,
                    title: '{{ translate('messages.Are you sure ?') }}',
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: '{{ translate('messages.Cancel') }}',
                    confirmButtonText: '{{ translate('messages.submit') }}',
                    inputPlaceholder: "{{ translate('Enter_a_reason') }}",
                    input: 'text',
                    html: message + '<br/>'+'<label>{{ translate('Enter_a_reason') }}</label>',
                    inputValue: processing,
                    preConfirm: (note) => {
                        location.href = route + '&note=' + note;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
        }
</script>
@endpush
