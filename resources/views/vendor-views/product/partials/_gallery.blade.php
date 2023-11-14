
@foreach($items as $key=>$item)
{{-- <div class="col-md-6 mt-4">
        <a target="_blank" href="{{ route('vendor.item.edit',['id' => $item->id , 'product_gellary' => true ]) }}">
        <div class="product-card card" onclick="quickView('{{$item->id}}')">
            <div class="card-header inline_product clickable p-0 initial--31">
                <div class="d-flex align-items-center justify-content-center h-100 d-block w-100">
                    <img src="{{asset('storage/app/public/product')}}/{{$item['image']}}"
                        onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                        class="w-100 h-100 object-cover">
                </div>
            </div>

            <div class="card-body inline_product text-center p-1 clickable initial--32">
                <div class="position-relative product-title1 text-dark font-weight-bold text-capitalize">
                    {{ Str::limit($item['name'], 120,'...') }}
                </div>
                <div class="justify-content-between text-center">
                    <div class="product-price text-center">
                        <span class="text-accent text-dark font-weight-bold">
                            {{\App\CentralLogics\Helpers::format_currency($item['price'])}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div> --}}
<div class="col-12">
    <div class="card mb-3">
        <!-- Body -->
        <div class="card-body ml-2">
            <div class="table-responsive">
                <div class="min-width-720">
                    <div class="d-flex">
                        <div>
                            <div class="d-flex flex-wrap align-items-center food--media position-relative mr-4">
                                <img class="avatar avatar-xxl avatar-4by3"
                                    src="{{ asset('storage/app/public/product') }}/{{ $item['image'] }}"
                                    onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'"
                                    alt="Image Description">
                            </div>
                        </div>
                        <div class="col-10">
                            <div class="table-responsive">
                                <div class="d-flex justify-content-between">
                                    <h2 class="ml-3">{{ $item?->getRawOriginal('name') }} </h2>
                                    <div>
                                        <a target="_blank" href="{{ route('vendor.item.edit',['id' => $item->id , 'product_gellary' => true ]) }}" class="btn btn--sm btn-outline-primary">
                                            {{ translate('messages.use_this_product_info') }}
                                        </a>


                                    </div>
                                </div>
                                <table class="table table-borderless table-thead-bordered">
                                    <thead>
                                        <tr>
                                            <th class="px-4 border-0">
                                                <h4 class="m-0 text-capitalize">{{ translate('General_Information') }}</h4>
                                            </th>
                                            <th class="px-4 border-0">
                                                <h4 class="m-0 text-capitalize">{{ translate('Available_Variations') }}</h4>
                                            </th>
                                            <th class="px-4 border-0">
                                                <h4 class="m-0 text-capitalize">{{ translate('tags') }}</h4>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-4 max-w--220px">
                                                <span class="d-block mb-1">
                                                    <span>{{ translate('messages.Category') }} : </span>
                                                    <strong>{{ Str::limit(($item?->category?->parent ? $item?->category?->parent?->name : $item?->category?->name )  ?? translate('messages.uncategorize')
                                                        , 20, '...') }}</strong>
                                                </span>
                                                <span class="d-block mb-1">
                                                    <span>{{ translate('messages.Sub_Category') }} : </span>
                                                    <strong>{{ Str::limit(($item?->category?->name )  ?? translate('messages.uncategorize')
                                                        , 20, '...') }}</strong>
                                                </span>
                                                @if ($item->module->module_type == 'grocery')
                                                <span class="d-block mb-1">
                                                    <span>{{ translate('messages.Is_Organic') }} : </span>
                                                    <strong> {{  $item->organic == 1 ?  translate('messages.yes') : translate('messages.no') }}</strong>
                                                </span>
                                                @endif
                                                @if ($item->module->module_type == 'food')
                                                <span class="d-block mb-1">
                                                    <span>{{ translate('messages.Item_type') }} : </span>
                                                    <strong> {{  $item->veg == 1 ?  translate('messages.veg') : translate('messages.non_veg') }}</strong>
                                                </span>
                                                @else
                                                    @if ($item?->unit)
                                                    <span class="d-block mb-1">
                                                        <span>{{ translate('messages.Unit') }} : </span>
                                                        <strong> {{ $item?->unit?->unit  }}</strong>
                                                    </span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="px-4">
                                                @if ($item->module->module_type == 'food')
                                                    @if ($item->food_variations && is_array(json_decode($item['food_variations'], true)))
                                                        @foreach (json_decode($item->food_variations, true) as $variation)
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
                                                                {{-- @if ($variation['type'] == 'multi')
                                                                    {{ translate('messages.multiple_select') }}
                                                                @elseif($variation['type'] == 'single')
                                                                    {{ translate('messages.single_select') }}
                                                                @endif
                                                                @if ($variation['required'] == 'on')
                                                                    - ({{ translate('messages.required') }})
                                                                @endif --}}
                                                            </span>

                                                            {{-- @if ($variation['min'] != 0 && $variation['max'] != 0)
                                                                ({{ translate('messages.Min_select') }}: {{ $variation['min'] }} -
                                                                {{ translate('messages.Max_select') }}: {{ $variation['max'] }})
                                                            @endif --}}

                                                            @if (isset($variation['values']))
                                                                @foreach ($variation['values'] as $value)
                                                                    <span class="d-block text-capitalize">
                                                                        &nbsp; &nbsp; {{ $value['label'] }}
                                                                        {{-- : <strong>{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</strong> --}}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @else
                                                @if ($item->variations && is_array(json_decode($item['variations'], true)))
                                                    @foreach (json_decode($item['variations'], true) as $variation)
                                                        <span class="d-block mb-1 text-capitalize">
                                                            {{ $variation['type'] }}
                                                            {{-- : {{ \App\CentralLogics\Helpers::format_currency($variation['price']) }} --}}
                                                        </span>
                                                    @endforeach
                                                @endif
                                        </td>
                                        @endif

                                            <td>
                                                @foreach($item->tags as $c) {{$c->tag.','}} @endforeach
                                            </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                    <div>
                        <h6> {{ translate('description') }}:</h6>
                        <P> {{ $item?->getRawOriginal('description') }}</P>
                    </div>
                </div>
            </div>


        </div>
        <!-- End Body -->
    </div>
</div>
@endforeach
