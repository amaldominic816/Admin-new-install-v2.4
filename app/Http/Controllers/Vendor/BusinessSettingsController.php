<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\StoreSchedule;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;
use App\Models\Translation;
use Illuminate\Support\Facades\Validator;

class BusinessSettingsController extends Controller
{

    private $store;

    public function store_index()
    {
        $store = Helpers::get_store_data();
        $store = Store::withoutGlobalScope('translate')->findOrFail($store->id);
        return view('vendor-views.business-settings.restaurant-index', compact('store'));
    }

    public function store_setup(Store $store, Request $request)
    {
        $request->validate([
            'gst' => 'required_if:gst_status,1',
            'per_km_delivery_charge'=>'required_with:minimum_delivery_charge',
            'minimum_delivery_charge'=>'required_with:per_km_delivery_charge'
        ], [
            'gst.required_if' => translate('messages.gst_can_not_be_empty'),
        ]);

        if(isset($request->maximum_shipping_charge) && ($request->minimum_delivery_charge > $request->maximum_shipping_charge)){
            Toastr::error(translate('Maximum delivery charge must be greater than minimum delivery charge.'));
                return back();
        }

        $store->minimum_order = $request->minimum_order??0;
        $store->gst = json_encode(['status'=>$request->gst_status, 'code'=>$request->gst]);
        // $store->delivery_charge = $store->self_delivery_system?$request->delivery_charge??0: $store->delivery_charge;
        $store->minimum_shipping_charge = $store->self_delivery_system?$request->minimum_delivery_charge??0: $store->minimum_shipping_charge;
        $store->per_km_shipping_charge = $store->self_delivery_system?$request->per_km_delivery_charge??0: $store->per_km_shipping_charge;
        $store->per_km_shipping_charge = $store->self_delivery_system?$request->per_km_delivery_charge??0: $store->per_km_shipping_charge;
        $store->maximum_shipping_charge = $store->self_delivery_system?$request->maximum_shipping_charge??0: $store->maximum_shipping_charge;
        $store->order_place_to_schedule_interval = $request->order_place_to_schedule_interval;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->save();
        Toastr::success(translate('messages.store_settings_updated'));
        return back();
    }
    public function updateStoreMetaData(Store $store, Request $request)
    {
        $request->validate([
            'meta_title.0' => 'required',
            'meta_description.0' => 'required',
        ],[
            'meta_title.0.required'=>translate('default_meta_title_is_required'),           
            'meta_description.0.required'=>translate('default_meta_description_is_required'),           
        ]);

        $store->meta_image = $request->has('meta_image') ? Helpers::update('store/', $store->meta_image, 'png', $request->file('meta_image')) : $store->meta_image;

        $store->meta_title = $request->meta_title[array_search('default', $request->lang)];
        $store->meta_description = $request->meta_description[array_search('default', $request->lang)];

        $store->save();        
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach($request->lang as $index=>$key)
        {
            if($default_lang == $key && !($request->meta_title[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'meta_title'
                        ],
                        ['value' => $store->meta_title]
                    );
                }
            }else{

                if ($request->meta_title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\Store',
                            'translationable_id'    => $store->id,
                            'locale'                => $key,
                            'key'                   => 'meta_title'],
                        ['value'                 => $request->meta_title[$index]]
                    );
                }
            }
            if($default_lang == $key && !($request->meta_description[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'meta_description'
                        ],
                        ['value' => $store->meta_description]
                    );
                }
            }else{

                if ($request->meta_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\Store',
                            'translationable_id'    => $store->id,
                            'locale'                => $key,
                            'key'                   => 'meta_description'],
                        ['value'                 => $request->meta_description[$index]]
                    );
                }
            }
        }
        Toastr::success(translate('messages.store').translate('messages.meta_data_updated'));
        return back();
    }
    public function store_status(Store $store, Request $request)
    {
        if($request->menu == "schedule_order" && !Helpers::schedule_order())
        {
            Toastr::warning(translate('messages.schedule_order_disabled_warning'));
            return back();
        }

        if((($request->menu == "delivery" && $store->take_away==0) || ($request->menu == "take_away" && $store->delivery==0)) &&  $request->status == 0 )
        {
            Toastr::warning(translate('messages.can_not_disable_both_take_away_and_delivery'));
            return back();
        }

        if((($request->menu == "veg" && $store->non_veg==0) || ($request->menu == "non_veg" && $store->veg==0)) &&  $request->status == 0 )
        {
            Toastr::warning(translate('messages.veg_non_veg_disable_warning'));
            return back();
        }

        $store[$request->menu] = $request->status;
        $store->save();
        Toastr::success(translate('messages.store settings updated!'));
        return back();
    }

    public function active_status(Request $request)
    {
        $store = Helpers::get_store_data();
        $store->active = $store->active?0:1;
        $store->save();
        return response()->json(['message' => $store->active?translate('messages.store_opened'):translate('messages.store_temporarily_closed')], 200);
    }

    public function add_schedule(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i|after:start_time',
        ],[
            'end_time.after'=>translate('messages.End time must be after the start time')
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
        $temp = StoreSchedule::where('day', $request->day)->where('store_id',Helpers::get_store_id())
        ->where(function($q)use($request){
            return $q->where(function($query)use($request){
                return $query->where('opening_time', '<=' , $request->start_time)->where('closing_time', '>=', $request->start_time);
            })->orWhere(function($query)use($request){
                return $query->where('opening_time', '<=' , $request->end_time)->where('closing_time', '>=', $request->end_time);
            });
        })
        ->first();

        if(isset($temp))
        {
            return response()->json(['errors' => [
                ['code'=>'time', 'message'=>translate('messages.schedule_overlapping_warning')]
            ]]);
        }

        $store = Helpers::get_store_data();
        $store_schedule = StoreSchedule::insert(['store_id'=>Helpers::get_store_id(),'day'=>$request->day,'opening_time'=>$request->start_time,'closing_time'=>$request->end_time]);
        return response()->json([
            'view' => view('vendor-views.business-settings.partials._schedule', compact('store'))->render(),
        ]);
    }

    public function remove_schedule($store_schedule)
    {
        $store = Helpers::get_store_data();
        $schedule = StoreSchedule::where('store_id', $store->id)->find($store_schedule);
        if(!$schedule)
        {
            return response()->json([],404);
        }
        $schedule->delete();
        return response()->json([
            'view' => view('vendor-views.business-settings.partials._schedule', compact('store'))->render(),
        ]);
    }


    public function site_direction_vendor(Request $request){
        session()->put('site_direction_vendor', ($request->status == 1?'ltr':'rtl'));
        return response()->json();
    }
}
