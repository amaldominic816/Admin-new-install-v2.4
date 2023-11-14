@foreach ($stores as $key => $store)
<div class="col-sm-6 col-lg-4 col-xxl-3">
    <div class="media gap-3 cursor-pointer flex-grow-1">
        <img class="avatar avatar-lg border" width="75"
        onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
        src="{{asset('storage/app/public/store')}}/{{$store['logo']}}"
            alt="">
        <div class="media-body d-flex flex-column gap-1 ">
                <div class="d-flex gap-2 justify-content-between" >
                    <h6 class="fs-13 mb-1 text-truncate custom-width product-name">{{$store['name']}}</h6>
                    <button type="button"  onclick="selected_stores({{ $store->id }}, true)" class="bg-transparent border-0 p-0"> <i class="tio-clear"></i></button>
                </div>
                <div class="d-flex gap-1 flex-wrap align-items-center lh--1">
                    <i class=" fs-13 tio-star"></i>
                    <div class="fs-10 text-dark" > {{ $store->ratings['rating'] }}</div>
                    <div class="fs-10 text-muted" >  ({{ $store->ratings['total'] }})</div>
                </div>
        </div>
    </div>
</div>
@endforeach
