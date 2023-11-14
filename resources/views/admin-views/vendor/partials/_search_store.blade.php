@forelse ($stores as $key => $store)
    <div  class="select-product-item media gap-3 cursor-pointer">
        <img class="avatar avatar-xl border" width="75"
        onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
        src="{{asset('storage/app/public/store')}}/{{$store['logo']}}"
            alt="">
        <div class="media-body d-flex flex-column gap-1 ">
            <a href="#"  class="d-flex flex-column gap-1"  onclick="selected_stores({{ $store->id }})">
                <h6 class="fs-13 mb-1 text-truncate custom-width product-name">{{$store['name']}}</h6>
                <div class="d-flex gap-1 flex-wrap align-items-center lh--1">
                    <i class=" fs-13 tio-star"></i>
                    <div class="fs-10 text-dark" > {{ $store->ratings['rating'] }}</div>
                    <div class="fs-10 text-muted" >  ({{ $store->ratings['total'] }})</div>
                </div>
                <div class="fs-10 text-muted" >{{ $store->address }}</div>
                <div class="d-flex gap-3 flex-wrap align-items-center text-primary "  >
                    <div class="fs-10  " >{{ $store->items_count }} {{ translate('messages.items') }}+</div>
                    <div class=" bg-primary" style="width: 1px;height: 10px;">

                    </div>
                    <div class="fs-10 " >{{ $store->orders_count }} {{ translate('messages.Orders') }}</div>
                </div>

            </a>

        </div>
    </div>
    @empty
    <p class="text-center">{{ translate('messages.No Data found') }}</p>
@endforelse
