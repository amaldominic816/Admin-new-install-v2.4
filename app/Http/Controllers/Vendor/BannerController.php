<?php

namespace App\Http\Controllers\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;


class BannerController extends Controller
{
    function list(Request $request)
    {
        $key = explode(' ', $request['search']);
        $banners=Banner::where('data',Helpers::get_store_id())->where('created_by','store')
        ->when($key, function($query)use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%". $value."%");
                }
            });
        })
        ->latest()->paginate(config('default_pagination'));
        return view('vendor-views.banner.index',compact('banners'));
    }


    public function status(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $store_id = $request->status;
        $store_ids = json_decode($banner->restaurant_ids);
        if(in_array($store_id, $store_ids))
        {
            unset($store_ids[array_search($store_id, $store_ids)]);
        }
        else
        {
            array_push($store_ids, $store_id);
        }

        $banner->restaurant_ids = json_encode($store_ids);
        $banner->save();
        Toastr::success(translate('messages.capmaign_participation_updated'));
        return back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'required|max:2048',
            'default_link' => 'max:255',
        ]);

        $store = Helpers::get_store_data();
        $banner = new Banner;
        $banner->title = $request->title;
        $banner->type = 'store_wise';
        $banner->zone_id = $store->zone_id;
        $banner->image = Helpers::upload('banner/', 'png', $request->file('image'));
        $banner->data = $store->id;
        $banner->module_id = $store->module_id;
        $banner->default_link = $request->default_link;
        $banner->created_by = 'store';
        $banner->save();
        Toastr::success(translate('messages.banner_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        $banner = Banner::withoutGlobalScope('translate')->findOrFail($id);
        return view('vendor-views.banner.edit', compact('banner'));
    }

    public function status_update(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $banner->status = $request->status;
        $banner->save();
        Toastr::success(translate('messages.banner_status_updated'));
        return back();
    }
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required',
            'default_link' => 'max:255',

        ]);
        $banner->title = $request->title;
        $banner->image = $request->has('image') ? Helpers::update('banner/', $banner->image, 'png', $request->file('image')) : $banner->image;
        $banner->default_link = $request->default_link;
        $banner->save();
        Toastr::success(translate('messages.banner_updated_successfully'));
        return back();
    }

    public function delete(Banner $banner)
    {
        if (Storage::disk('public')->exists('banner/' . $banner['image'])) {
            Storage::disk('public')->delete('banner/' . $banner['image']);
        }
        $banner->translations()->delete();
        $banner->delete();
        Toastr::success(translate('messages.banner_deleted_successfully'));
        return back();
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $banners=Banner::where('data',Helpers::get_store_id())->where('created_by','store')->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('title', 'like', "%{$value}%");
            }
        })->limit(50)->get();
        return response()->json([
            'view'=>view('vendor-views.banner.partials._table',compact('banners'))->render(),
            'count'=>$banners->count()
        ]);
    }

}
