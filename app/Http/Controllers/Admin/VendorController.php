<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\Zone;
use App\Models\AddOn;
use App\Models\Order;
use App\Models\Store;
use App\Models\Module;
use App\Models\Vendor;
use App\Models\Message;
use App\Models\UserInfo;
use App\Scopes\StoreScope;
use App\Models\DataSetting;
use App\Models\StoreConfig;
use App\Models\StoreWallet;
use App\Models\TempProduct;
use App\Models\Translation;
use Illuminate\Support\Str;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\StoreSchedule;
use App\CentralLogics\Helpers;
use App\Models\WithdrawRequest;
use App\Exports\StoreListExport;
use App\Models\OrderTransaction;
use App\CentralLogics\StoreLogic;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Exports\StoreWiseItemReviewExport;
use App\Exports\StoreCashTransactionExport;
use App\Exports\StoreOrderTransactionExport;
use MatanYadaev\EloquentSpatial\Objects\Point;
use App\Exports\StoreWithdrawTransactionExport;
use App\Exports\StoreWiseWithdrawTransactionExport;


class VendorController extends Controller
{
    public function index()
    {
        return view('admin-views.vendor.index');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'address' => 'required|max:1000',
            'latitude' => 'required',
            'longitude' => 'required',
            'email' => 'required|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors',
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'delivery_time_type'=>'required',
            'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'zone_id' => 'required',
            // 'module_id' => 'required',
            'logo' => 'required',
            'tax' => 'required'
        ], [
            'f_name.required' => translate('messages.first_name_is_required'),
            'name.0.required'=>translate('default_name_is_required'),
        ]);

        if($request->zone_id)
        {
            $zone = Zone::query()
            ->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))
            ->where('id',$request->zone_id)
            ->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
                return back()->withErrors($validator)
                        ->withInput();
            }
        }
        if ($request->delivery_time_type == 'min') {
            $minimum_delivery_time = (int) $request->input('minimum_delivery_time');
            if ($minimum_delivery_time < 10) {
                $validator->getMessageBag()->add('minimum_delivery_time', translate('messages.minimum_delivery_time_should_be_more_than_10_min'));
                return back()->withErrors($validator)
                        ->withInput();
            }
        }
        if ($validator->fails()) {
            return back()
            ->withErrors($validator)
            ->withInput();
        }

        $vendor = new Vendor();
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->save();

        $store = new Store;
        $store->name = $request->name[array_search('default', $request->lang)];
        $store->phone = $request->phone;
        $store->email = $request->email;
        $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
        $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
        $store->address = $request->address[array_search('default', $request->lang)];
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->vendor_id = $vendor->id;
        $store->zone_id = $request->zone_id;
        $store->tax = $request->tax;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->module_id = Config::get('module.current_module_id');
        try {
            $store->save();
            $store->module->increment('stores_count');
            if(config('module.'.$store->module->module_type)['always_open'])
            {
                StoreLogic::insert_schedule($store->id);
            }
            $default_lang = str_replace('_', '-', app()->getLocale());
            $data = [];
            foreach ($request->lang as $index => $key) {
                if($default_lang == $key && !($request->name[$index])){
                    if ($key != 'default') {
                        array_push($data, array(
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'name',
                            'value' => $store->name,
                        ));
                    }
                }else{
                    if ($request->name[$index] && $key != 'default') {
                        array_push($data, array(
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'name',
                            'value' => $request->name[$index],
                        ));
                    }
                }
                if($default_lang == $key && !($request->address[$index])){
                    if ($key != 'default') {
                        array_push($data, array(
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'address',
                            'value' => $store->address,
                        ));
                    }
                }else{
                    if ($request->address[$index] && $key != 'default') {
                        array_push($data, array(
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'address',
                            'value' => $request->address[$index],
                        ));
                    }
                }
            }
            Translation::insert($data);
            // $store->zones()->attach($request->zone_ids);
            //code...
        } catch (\Exception $ex) {
            info($ex->getMessage());
        }
        Toastr::success(translate('messages.store').translate('messages.added_successfully'));
        return redirect('admin/store/list');
    }

    public function edit($id)
    {
        if(env('APP_MODE')=='demo' && $id == 2)
        {
            Toastr::warning(translate('messages.you_can_not_edit_this_store_please_add_a_new_store_to_edit'));
            return back();
        }
        $store = Store::withoutGlobalScope('translate')->findOrFail($id);
        return view('admin-views.vendor.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name' => 'required|max:191',
            'email' => 'required|unique:vendors,email,'.$store->vendor->id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors,phone,'.$store->vendor->id,
            'zone_id'=>'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'tax' => 'required',
            'password' => ['nullable', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'delivery_time_type'=>'required'
        ], [
            'f_name.required' => translate('messages.first_name_is_required')
        ]);

        if($request->zone_id)
        {
            $zone = Zone::query()
            ->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))
            ->where('id',$request->zone_id)
            ->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
                return back()->withErrors($validator)
                        ->withInput();
            }
        }
        if ($request->delivery_time_type == 'min') {
            $minimum_delivery_time = (int) $request->input('minimum_delivery_time');
            if ($minimum_delivery_time < 10) {
                $validator->getMessageBag()->add('minimum_delivery_time', translate('messages.minimum_delivery_time_should_be_more_than_10_min'));
                return back()->withErrors($validator)
                        ->withInput();
            }
        }

        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput();
        }
        $vendor = Vendor::findOrFail($store->vendor->id);
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = strlen($request->password)>1?bcrypt($request->password):$store->vendor->password;
        $vendor->save();

        $slug = Str::slug($request->name[array_search('default', $request->lang)]);
        $store->slug = $store->slug? $store->slug :"{$slug}{$store->id}";
        $store->email = $request->email;
        $store->phone = $request->phone;
        $store->logo = $request->has('logo') ? Helpers::update('store/', $store->logo, 'png', $request->file('logo')) : $store->logo;
        $store->cover_photo = $request->has('cover_photo') ? Helpers::update('store/cover/', $store->cover_photo, 'png', $request->file('cover_photo')) : $store->cover_photo;
        $store->name = $request->name[array_search('default', $request->lang)];
        $store->address = $request->address[array_search('default', $request->lang)];
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->zone_id = $request->zone_id;
        $store->tax = $request->tax;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach($request->lang as $index=>$key)
        {
            if($default_lang == $key && !($request->name[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $store->name]
                    );
                }
            }else{

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\Store',
                            'translationable_id'    => $store->id,
                            'locale'                => $key,
                            'key'                   => 'name'],
                        ['value'                 => $request->name[$index]]
                    );
                }
            }
            if($default_lang == $key && !($request->address[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Store',
                            'translationable_id' => $store->id,
                            'locale' => $key,
                            'key' => 'address'
                        ],
                        ['value' => $store->address]
                    );
                }
            }else{

                if ($request->address[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\Store',
                            'translationable_id'    => $store->id,
                            'locale'                => $key,
                            'key'                   => 'address'],
                        ['value'                 => $request->address[$index]]
                    );
                }
            }
        }
        if ($vendor->userinfo) {
            $userinfo = $vendor->userinfo;
            $userinfo->f_name = $store->name;
            $userinfo->l_name = '';
            $userinfo->email = $store->email;
            $userinfo->image = $store->logo;
            $userinfo->save();
        }
        Toastr::success(translate('messages.store').translate('messages.updated_successfully'));
        return redirect('admin/store/list');
    }

    public function destroy(Request $request, Store $store)
    {
        if(env('APP_MODE')=='demo' && $store->id == 2)
        {
            Toastr::warning(translate('messages.you_can_not_delete_this_store_please_add_a_new_store_to_delete'));
            return back();
        }
        if (Storage::disk('public')->exists('store/' . $store['logo'])) {
            Storage::disk('public')->delete('store/' . $store['logo']);
        }
        $store->delete();

        $vendor = Vendor::findOrFail($store->vendor->id);
        if($vendor->userinfo){
            $vendor->userinfo->delete();
        }
        $vendor->delete();
        Toastr::success(translate('messages.store_removed'));
        return back();
    }

    public function view($store_id, $tab=null, $sub_tab='cash')
    {
        $key = explode(' ', request()->search);

        $store = Store::find($store_id);
        $wallet = $store->vendor->wallet;
        if(!$wallet)
        {
            $wallet= new StoreWallet();
            $wallet->vendor_id = $store->vendor->id;
            $wallet->total_earning= 0.0;
            $wallet->total_withdrawn=0.0;
            $wallet->pending_withdraw=0.0;
            $wallet->created_at=now();
            $wallet->updated_at=now();
            $wallet->save();
        }
        if($tab == 'settings')
        {
            return view('admin-views.vendor.view.settings', compact('store'));
        }
        else if($tab == 'order')
        {
            $orders=Order::where('store_id', $store->id)->latest()
            ->when(isset($key ), function ($q) use ($key){
                        $q->where(function ($q) use ($key) {
                            foreach ($key as $value) {
                                $q->orWhere('id', 'like', "%{$value}%");
                            }
                        });
                    })
            ->Notpos()->paginate(10);
            return view('admin-views.vendor.view.order', compact('store','orders'));
        }
        else if($tab == 'item')
        {
            if($sub_tab == 'pending-items' || $sub_tab == 'rejected-items' ){

                $foods = TempProduct::withoutGlobalScope(\App\Scopes\StoreScope::class)->where('store_id', $store->id)
                ->when(isset($key) , function($q) use($key){
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('name', 'like', "%{$value}%");
                        }
                    });
                })
                ->when($sub_tab == 'pending-items' , function($q){
                    $q->where('is_rejected' , 0);
                })
                ->when($sub_tab == 'rejected-items' , function($q){
                    $q->where('is_rejected' , 1);
                })
                ->latest()->paginate(25);
            }
            else{

                $foods = Item::withoutGlobalScope(\App\Scopes\StoreScope::class)->where('store_id', $store->id)
                    ->when(isset($key) , function($q) use($key){
                        $q->where(function ($q) use ($key) {
                            foreach ($key as $value) {
                                $q->where('name', 'like', "%{$value}%");
                            }
                        });
                    })
                    ->when($sub_tab == 'active-items' , function($q){
                        $q->where('status' , 1);
                    })
                    ->when($sub_tab == 'inactive-items' , function($q){
                        $q->where('status' , 0);
                    })
                    ->latest()->paginate(25);
            }

            return view('admin-views.vendor.view.product', compact('store','foods','sub_tab'));
        }
        else if($tab == 'discount')
        {
            return view('admin-views.vendor.view.discount', compact('store'));
        }
        else if($tab == 'transaction')
        {
            return view('admin-views.vendor.view.transaction', compact('store', 'sub_tab'));
        }

        else if($tab == 'reviews')
        {
            return view('admin-views.vendor.view.review', compact('store', 'sub_tab'));

        } else if ($tab == 'conversations') {
            $user = UserInfo::where(['vendor_id' => $store->vendor->id])->first();
            if ($user) {
                $conversations = Conversation::with(['sender', 'receiver', 'last_message'])->WhereUser($user->id)
                    ->paginate(8);
            } else {
                $conversations = [];
            }
            return view('admin-views.vendor.view.conversations', compact('store', 'sub_tab', 'conversations'));
        } else if ($tab == 'meta-data') {
            $store = Store::withoutGlobalScope('translate')->findOrFail($store_id);
            return view('admin-views.vendor.view.meta-data', compact('store', 'sub_tab'));
        }
        return view('admin-views.vendor.view.index', compact('store', 'wallet'));
    }

    public function view_tab(Store $store)
    {

        Toastr::error(translate('messages.unknown_tab'));
        return back();
    }

    public function list(Request $request)
    {
        $key = explode(' ', $request['search']);

        $zone_id = $request->query('zone_id', 'all');
        $type = $request->query('type', 'all');
        $module_id = $request->query('module_id', 'all');
        $stores = Store::with('vendor','module')->whereHas('vendor', function($query){
            return $query->where('status', 1);
        })
        ->when(is_numeric($zone_id), function($query)use($zone_id){
                return $query->where('zone_id', $zone_id);
        })
        ->when(is_numeric($module_id), function($query)use($request){
            return $query->module($request->query('module_id'));
        })
                ->when(isset($key), function($query)use($key){
            return $query->where(function($query)use($key){
                $query->orWhereHas('vendor',function ($q) use ($key) {
                    $q->where(function($q)use($key){
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
                })->orWhere(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
            });
        })
        ->module(Config::get('module.current_module_id'))
        ->with('vendor','module')->type($type)->latest()->paginate(config('default_pagination'));
        $zone = is_numeric($zone_id)?Zone::findOrFail($zone_id):null;
        return view('admin-views.vendor.list', compact('stores', 'zone','type'));
    }

    public function pending_requests(Request $request)
    {
        $zone_id = $request->query('zone_id', 'all');
        $search_by = $request->query('search_by');
        $key = explode(' ', $search_by);
        $type = $request->query('type', 'all');
        $module_id = $request->query('module_id', 'all');
        $stores = Store::with('vendor','module')->whereHas('vendor', function($query){
            return $query->where('status', null);
        })
        ->when(is_numeric($zone_id), function($query)use($zone_id){
                return $query->where('zone_id', $zone_id);
        })
        ->when(is_numeric($module_id), function($query)use($request){
            return $query->module($request->query('module_id'));
        })
        ->when($search_by, function($query)use($key){
            return $query->where(function($query)use($key){
                $query->orWhereHas('vendor',function ($q) use ($key) {
                    $q->where(function($q)use($key){
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
                })->orWhere(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
            });
        })
        ->module(Config::get('module.current_module_id'))
        ->type($type)->latest()->paginate(config('default_pagination'));
        $zone = is_numeric($zone_id)?Zone::findOrFail($zone_id):null;
        return view('admin-views.vendor.pending_requests', compact('stores', 'zone','type', 'search_by'));
    }

    public function deny_requests(Request $request)
    {
        $search_by = $request->query('search_by');
        $key = explode(' ', $search_by);
        $zone_id = $request->query('zone_id', 'all');
        $type = $request->query('type', 'all');
        $module_id = $request->query('module_id', 'all');
        $stores = Store::with('vendor','module')->whereHas('vendor', function($query){
            return $query->where('status', 0);
        })
        ->when(is_numeric($zone_id), function($query)use($zone_id){
                return $query->where('zone_id', $zone_id);
        })
        ->when(is_numeric($module_id), function($query)use($request){
            return $query->module($request->query('module_id'));
        })
        ->when($search_by, function($query)use($key){
            return $query->where(function($query)use($key){
                $query->orWhereHas('vendor',function ($q) use ($key) {
                    $q->where(function($q)use($key){
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
                })->orWhere(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
            });
        })
        ->module(Config::get('module.current_module_id'))
        ->type($type)->latest()->paginate(config('default_pagination'));
        $zone = is_numeric($zone_id)?Zone::findOrFail($zone_id):null;
        return view('admin-views.vendor.deny_requests', compact('stores', 'zone','type', 'search_by'));
    }

    public function export(Request $request){

        $key = explode(' ', $request['search']);

        $zone_id = $request->query('zone_id', 'all');
        $module_id = $request->query('module_id', 'all');
        $stores = Store::whereHas('vendor', function($query){
            return $query->where('status', 1);
        })
        ->when(is_numeric($zone_id), function($query)use($zone_id){
                return $query->where('zone_id', $zone_id);
        })
        ->when(is_numeric($module_id), function($query)use($request){
            return $query->module($request->query('module_id'));
        })
        ->when(isset($key), function($query)use($key){
            return $query->where(function($query)use($key){
                $query->orWhereHas('vendor',function ($q) use ($key) {
                    $q->where(function($q)use($key){
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
                })->orWhere(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
            });
        })
        ->module(Config::get('module.current_module_id'))
        ->with('vendor','module')
        ->orderBy('id','DESC')
        ->withCount('items')
        ->get();

        $data=[
            'data' =>$stores,
            'zone' =>is_numeric($zone_id)?Helpers::get_zones_name($zone_id):null,
            'module'=>request('module_id')?Helpers::get_module_name(Config::get('module.current_module_id')):null,
            'search' =>$request['search'] ?? null,
        ];
        if($request->type == 'csv'){
            return Excel::download(new StoreListExport($data), 'Stores.csv');
        }
        return Excel::download(new StoreListExport($data), 'Stores.xlsx');



        // if($request->type == 'excel'){
        //     return (new FastExcel(Helpers::export_stores(Helpers::Export_generator($stores))))->download('Stores.xlsx');
        // }elseif($request->type == 'csv'){
        //     return (new FastExcel(Helpers::export_stores(Helpers::Export_generator($stores))))->download('Stores.csv');
        // }
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $stores=Store::whereHas('vendor',function ($q) {
            $q->where('status', 1);
        })->where(function($query)use($key){
            $query->orWhereHas('vendor',function ($q) use ($key) {
                $q->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->orWhere('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
            })->orWhere(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
        })
        ->module(Config::get('module.current_module_id'))
        ->get();
        $total=$stores->count();
        return response()->json([
            'view'=>view('admin-views.vendor.partials._table',compact('stores'))->render(), 'total'=>$total
        ]);
    }

    public function get_stores(Request $request){
        $zone_ids = isset($request->zone_ids)?(count($request->zone_ids)>0?$request->zone_ids:[]):0;
        $data = Store::
        // withOutGlobalScopes()
        // ->
        // join('zones', 'zones.id', '=', 'stores.zone_id')
        // ->
        when($zone_ids, function($query) use($zone_ids){
            $query->whereIn('stores.zone_id', [$zone_ids]);
        })
        ->when($request->module_id, function($query)use($request){
            $query->where('module_id', $request->module_id);
        })
        ->when($request->module_type, function($query)use($request){
            $query->whereHas('module', function($q)use($request){
                $q->where('module_type', $request->module_type);
            });
        })
        ->where('stores.name', 'like', '%'.$request->q.'%')
        ->limit(8)->get()
        ->map(function ($store) {
            return [
                'id' => $store->id,
                'text' => $store->name . ' (' . $store->zone?->name . ')',
            ];
        });
        if(isset($request->all))
        {
            $data[]=(object)['id'=>'all', 'text'=>translate('messages.all')];
        }
        return response()->json($data);
    }

    public function status(Store $store, Request $request)
    {
        $store->status = $request->status;
        $store->save();
        $vendor = $store->vendor;

        try
        {
            if($request->status == 0)
            {   $vendor->auth_token = null;
                if(isset($vendor->fcm_token))
                {
                    $data = [
                        'title' => translate('messages.suspended'),
                        'description' => translate('messages.your_account_has_been_suspended'),
                        'order_id' => '',
                        'image' => '',
                        'type'=> 'block'
                    ];
                    Helpers::send_push_notif_to_device($vendor->fcm_token, $data);
                    DB::table('user_notifications')->insert([
                        'data'=> json_encode($data),
                        'vendor_id'=>$vendor->id,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }

            }

        }
        catch (\Exception $e) {
            Toastr::warning(translate('messages.push_notification_faild'));
        }

        Toastr::success(translate('messages.store').translate('messages.status_updated'));
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
        if($request->menu == "self_delivery_system" && $request->status == '0') {
            $store['free_delivery'] = 0;
        }

        $store[$request->menu] = $request->status;
        $store->save();
        Toastr::success(translate('messages.store').translate('messages.settings_updated'));
        return back();
    }

    public function discountSetup(Store $store, Request $request)
    {
        $message=translate('messages.discount');
        $message .= $store->discount?translate('messages.updated_successfully'):translate('messages.added_successfully');
        $store->discount()->updateOrinsert(
        [
            'store_id' => $store->id
        ],
        [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : 0,
            'discount' => $request->discount_type == 'amount' ? $request->discount : $request['discount'],
            'discount_type' => 'percent'
        ]
        );
        return response()->json(['message'=>$message], 200);
    }

    public function updateStoreSettings(Store $store, Request $request)
    {
        $request->validate([
            'minimum_order'=>'required',
            'comission'=>'required',
            'tax'=>'required',
            'minimum_delivery_time' => 'required|min:1|max:2',
            'maximum_delivery_time' => 'required|min:1|max:2|gt:minimum_delivery_time',
        ]);

        if($request->comission_status)
        {
            $store->comission = $request->comission;
        }
        else{
            $store->comission = null;
        }

        $store->minimum_order = $request->minimum_order;
        $store->tax = $request->tax;
        $store->order_place_to_schedule_interval = $request->order_place_to_schedule_interval;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->veg = (bool)($request->veg_non_veg == 'veg' || $request->veg_non_veg == 'both');
        $store->non_veg = (bool)($request->veg_non_veg == 'non_veg' || $request->veg_non_veg == 'both');

        $store->save();
        Toastr::success(translate('messages.store').translate('messages.settings_updated'));
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

    public function update_application(Request $request)
    {
        $store = Store::findOrFail($request->id);
        $store->vendor->status = $request->status;
        $store->vendor->save();
        if($request->status) $store->status = 1;
        $store->save();
        try{
            if($request->status==1){
                $mail_status = Helpers::get_mail_status('approve_mail_status_store');
                if ( config('mail.status') && $mail_status == '1') {
                    Mail::to($store?->vendor?->email)->send(new \App\Mail\VendorSelfRegistration('approved', $store->vendor->f_name.' '.$store->vendor->l_name));
                }
            }else{
                $mail_status = Helpers::get_mail_status('deny_mail_status_store');
                if ( config('mail.status') && $mail_status == '1') {
                    Mail::to($store?->vendor?->email)->send(new \App\Mail\VendorSelfRegistration('denied', $store->vendor->f_name.' '.$store->vendor->l_name));
                }
            }
        }catch(\Exception $ex){
            info($ex->getMessage());
        }
        Toastr::success(translate('messages.application_status_updated_successfully'));
        return back();
    }

    public function cleardiscount(Store $store)
    {
        $store->discount->delete();
        Toastr::success(translate('messages.store').translate('messages.discount_cleared'));
        return back();
    }

    public function withdraw(Request $request)
    {
        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_req =WithdrawRequest::with(['vendor'])
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->when(isset($key), function ($query) use ($key) {
                return $query->whereHas('vendor', function ($query) use ($key) {
                    $query->whereHas('stores', function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('name', 'like', "%{$value}%");
                        }
                    });
                });
            })
            ->latest()
            ->paginate(config('default_pagination'));

            if(!Helpers::module_permission_check('withdraw_list')){
                return view('admin-views.wallet.withdraw-dashboard');
            }

        return view('admin-views.wallet.withdraw', compact('withdraw_req'));
    }
    public function withdraw_export(Request $request)
    {
        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_req =WithdrawRequest::with(['vendor'])
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->when(isset($key), function ($query) use ($key) {
                return $query->whereHas('vendor', function ($query) use ($key) {
                    $query->whereHas('stores', function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('name', 'like', "%{$value}%");
                        }
                    });
                });
            })
            ->latest()->get();

        $data = [
            'withdraw_requests'=>$withdraw_req,
            'search'=>$request->search??null,
            'request_status'=>session()->has('withdraw_status_filter')?session('withdraw_status_filter'):null,

        ];

        if ($request->type == 'excel') {
            return Excel::download(new StoreWithdrawTransactionExport($data), 'WithdrawRequests.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new StoreWithdrawTransactionExport($data), 'WithdrawRequests.csv');
        }
    }

    public function withdraw_search(Request $request){
        $key = explode(' ', $request['search']);
        $withdraw_req = WithdrawRequest::whereHas('vendor', function ($query) use ($key) {
            $query->whereHas('stores', function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            });
        })->get();
        $total=$withdraw_req->count();
        return response()->json([
            'view'=>view('admin-views.wallet.partials._table',compact('withdraw_req'))->render(), 'total'=>$total
        ]);
    }

    public function withdraw_view($withdraw_id, $seller_id)
    {
        $wr = WithdrawRequest::with(['vendor'])->where(['id' => $withdraw_id])->first();
        return view('admin-views.wallet.withdraw-view', compact('wr'));
    }

    public function status_filter(Request $request){
        session()->put('withdraw_status_filter',$request['withdraw_status_filter']);
        return response()->json(session('withdraw_status_filter'));
    }

    public function withdrawStatus(Request $request, $id)
    {
        $withdraw = WithdrawRequest::findOrFail($id);
        $withdraw->approved = $request->approved;
        $withdraw->transaction_note = $request['note'];
        if ($request->approved == 1) {
            StoreWallet::where('vendor_id', $withdraw->vendor_id)->increment('total_withdrawn', $withdraw->amount);
            StoreWallet::where('vendor_id', $withdraw->vendor_id)->decrement('pending_withdraw', $withdraw->amount);
            $withdraw->save();
            try
            {
                $mail_status = Helpers::get_mail_status('withdraw_approve_mail_status_store');
                if(config('mail.status') && $mail_status == '1') {
                    Mail::to($withdraw->vendor->email)->send(new \App\Mail\WithdrawRequestMail('approved',$withdraw));
                }
            }
            catch(\Exception $e)
            {
                info($e->getMessage());
            }
            Toastr::success(translate('messages.seller_payment_approved'));
            return redirect()->route('admin.transactions.store.withdraw_list');
        } else if ($request->approved == 2) {
            StoreWallet::where('vendor_id', $withdraw->vendor_id)->decrement('pending_withdraw', $withdraw->amount);
            $withdraw->save();
            try
            {
                $mail_status = Helpers::get_mail_status('withdraw_deny_mail_status_store');
                if(config('mail.status') && $mail_status == '1') {
                    Mail::to($withdraw->vendor->email)->send(new \App\Mail\WithdrawRequestMail('denied',$withdraw));
                }
            }
            catch(\Exception $e)
            {
                info($e->getMessage());
            }
            Toastr::info(translate('messages.seller_payment_denied'));
            return redirect()->route('admin.transactions.store.withdraw_list');
        } else {
            Toastr::error(translate('messages.not_found'));
            return back();
        }
    }

    public function get_addons(Request $request)
    {
        $cat = AddOn::
        withoutGlobalScope(StoreScope::class)->
        // withoutGlobalScope('translate')->
        where(['store_id' => $request->store_id])->active()->get();
        $res = '';
        foreach ($cat as $row) {
            $res .= '<option value="' . $row->id.'"';
            if(count($request->data))
            {
                $res .= in_array($row->id, $request->data)?'selected':'';
            }
            $res .=  '>' . $row->name . '</option>';
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function get_store_data(Store $store)
    {
        return response()->json($store);
    }

    public function store_filter($id)
    {
        if ($id == 'all') {
            if (session()->has('store_filter')) {
                session()->forget('store_filter');
            }
        } else {
            session()->put('store_filter', Store::where('id', $id)->first(['id', 'name']));
        }
        return back();
    }

    public function get_account_data(Store $store)
    {
        $wallet = $store->vendor->wallet;
        $cash_in_hand = 0;
        $balance = 0;

        if($wallet)
        {
            $cash_in_hand = $wallet->collected_cash;
            $balance = $wallet->total_earning;
        }
        return response()->json(['cash_in_hand'=>$cash_in_hand, 'earning_balance'=>$balance], 200);

    }

    public function bulk_import_index()
    {
        return view('admin-views.vendor.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        $request->validate([
            'products_file'=>'required|max:2048'
        ]);
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }
        $duplicate_phones = $collections->duplicates('phone');
        $duplicate_emails = $collections->duplicates('email');


        if ($duplicate_emails->isNotEmpty()) {
            Toastr::error(translate('messages.duplicate_data_on_column', ['field' => translate('messages.email')]));
            return back();
        }

        if ($duplicate_phones->isNotEmpty()) {
            Toastr::error(translate('messages.duplicate_data_on_column', ['field' => translate('messages.phone')]));
            return back();
        }

        $email= $collections->pluck('email')->toArray();
        $phone= $collections->pluck('phone')->toArray();

        if($request->button == 'import'){



            if(Store::whereIn('email', $email)->orWhereIn('phone', $phone)->exists()
            ){
                Toastr::error(translate('messages.duplicate_email_or_phone_exists_at_the_database'));
                return back();
            }

            $vendors = [];
            $stores = [];
            $vendor = Vendor::orderBy('id', 'desc')->first('id');
            $vendor_id = $vendor?$vendor->id:0;
            $store = Store::orderBy('id', 'desc')->first('id');
            $store_id = $store?$store->id:0;
            $store_ids = [];
            foreach ($collections as $key => $collection) {
                if ($collection['ownerFirstName'] === "" || $collection['storeName'] === "" || $collection['phone'] === ""
                || $collection['email'] === "" || $collection['latitude'] === "" || $collection['longitude'] === ""
                || $collection['zone_id'] === "" ||  $collection['DeliveryTime'] === ""  || $collection['Tax'] === "" || $collection['logo'] === ""  ) {
                    Toastr::error(translate('messages.please_fill_all_required_fields'));
                    return back();
                }
                if(isset($collection['DeliveryTime']) && explode("-", (string)$collection['DeliveryTime'])[0] >  explode("-", (string)$collection['DeliveryTime'])[1]){
                    Toastr::error('messages.max_delivery_time_must_be_greater_than_min_delivery_time');
                    return back();
                }
                if(isset($collection['Comission']) && ($collection['Comission'] < 0 ||  $collection['Comission'] > 100) ) {
                    Toastr::error('messages.Comission_must_be_in_0_to_100');
                    return back();
                }
                if(isset($collection['Tax']) && ($collection['Tax'] < 0 ||  $collection['Tax'] > 100 )) {
                    Toastr::error('messages.Tax_must_be_in_0_to_100');
                    return back();
                }
                if(isset($collection['latitude']) && ($collection['latitude'] < -90 ||  $collection['latitude'] > 90 )) {
                    Toastr::error('messages.latitude_must_be_in_-90_to_90');
                    return back();
                }
                if(isset($collection['longitude']) && ($collection['longitude'] < -180 ||  $collection['longitude'] > 180 )) {
                    Toastr::error('messages.longitude_must_be_in_-180_to_180');
                    return back();
                }
                if(isset($collection['MinimumDeliveryFee']) && ($collection['MinimumDeliveryFee'] < 0  )) {
                    Toastr::error('messages.Enter_valid_Minimum_Delivery_Fee');
                    return back();
                }
                if(isset($collection['MinimumOrderAmount']) && ($collection['MinimumOrderAmount'] < 0  )) {
                    Toastr::error('messages.Enter_valid_Minimum_Order_Amount');
                    return back();
                }
                if(isset($collection['PerKmDeliveryFee']) && ($collection['PerKmDeliveryFee'] < 0  )) {
                    Toastr::error('messages.Enter_valid_Per_Km_Delivery_Fee');
                    return back();
                }
                if(isset($collection['MaximumDeliveryFee']) && ($collection['MaximumDeliveryFee'] < 0  )  ) {
                    Toastr::error('messages.Enter_valid_Maximum_Delivery_Fee');
                    return back();
                }



                array_push($vendors, [
                    'id'=>$vendor_id+$key+1,
                    'f_name' => $collection['ownerFirstName'],
                    'l_name' => $collection['ownerLastName'],
                    'password' => bcrypt(12345678),
                    'phone' => $collection['phone'],
                    'email' => $collection['email'],
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

                array_push($stores, [
                    'name' => $collection['storeName'],
                    'phone' => $collection['phone'],
                    'email' => $collection['email'],
                    'logo' => $collection['logo'],
                    'cover_photo' => $collection['CoverPhoto'],
                    'latitude' => $collection['latitude'],
                    'longitude' => $collection['longitude'],
                    'address' => $collection['Address'],
                    'zone_id' => $collection['zone_id'],
                    'module_id' => $collection['module_id'],
                    'minimum_order' => $collection['MinimumOrderAmount'],
                    'comission' => $collection['Comission'],
                    'tax' => $collection['Tax'],
                    'delivery_time' => (isset($collection['DeliveryTime']) && preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $collection['DeliveryTime'])) ? $collection['DeliveryTime'] :'30-40 min',
                    'minimum_shipping_charge' => $collection['MinimumDeliveryFee'],
                    'per_km_shipping_charge' => $collection['PerKmDeliveryFee'],
                    'maximum_shipping_charge' => $collection['MaximumDeliveryFee'],
                    'schedule_order' => $collection['ScheduleOrder'] == 'yes' ? 1 : 0,
                    'status' => $collection['Status'] == 'active' ? 1 : 0,
                    'self_delivery_system' => $collection['SelfDeliverySystem'] == 'active' ? 1 : 0,
                    'veg' => $collection['Veg'] == 'yes' ? 1 : 0,
                    'non_veg' => $collection['NonVeg'] == 'yes' ? 1 : 0,
                    'free_delivery' => $collection['FreeDelivery'] == 'yes' ? 1 : 0,
                    'take_away' => $collection['TakeAway'] == 'yes' ? 1 : 0,
                    'delivery' => $collection['Delivery'] == 'yes' ? 1 : 0,
                    'reviews_section' => $collection['ReviewsSection'] == 'active' ? 1 : 0,
                    'pos_system' => $collection['PosSystem'] == 'active' ? 1 : 0,
                    'active' => $collection['storeOpen'] == 'yes' ? 1 : 0,
                    'featured' => $collection['FeaturedStore'] == 'yes' ? 1 : 0,
                    'vendor_id' => $vendor_id+$key+1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if($module = Module::select('module_type')->where('id', $collection['module_id'])->first())
                {
                    if(config('module.'.$module->module_type))
                    {
                        $store_ids[] = $store_id+$key+1;
                    }
                }

            }

            $data = array_map(function($id){
                return array_map(function($item)use($id){
                    return     ['store_id'=>$id,'day'=>$item,'opening_time'=>'00:00:00','closing_time'=>'23:59:59'];
                },[0,1,2,3,4,5,6]);
            },$store_ids);

            try{
                DB::beginTransaction();

                $chunkSize = 100;
                $chunk_stores= array_chunk($stores,$chunkSize);
                $chunk_vendors= array_chunk($vendors,$chunkSize);

                foreach($chunk_stores as $key=> $chunk_store){
                    DB::table('vendors')->insert($chunk_vendors[$key]);
                    DB::table('stores')->insert($chunk_store);
                }
                DB::table('store_schedule')->insert(array_merge(...$data));
                DB::commit();
            }catch(\Exception $e)
            {
                DB::rollBack();
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();
            }

            Toastr::success(translate('messages.store_imported_successfully',['count'=>count($stores)]));
            return back();
        }

        if(Store::whereIn('email', $email)->orWhereIn('phone', $phone)->doesntExist()
        ){
            Toastr::error(translate('messages.email_or_phone_doesnt_exist_at_the_database'));
            return back();
        }


        $vendors = [];
            $stores = [];
            $vendor = Vendor::orderBy('id', 'desc')->first('id');
            $vendor_id = $vendor?$vendor->id:0;
            $store = Store::orderBy('id', 'desc')->first('id');
            $store_id = $store?$store->id:0;
            $store_ids = [];
            foreach ($collections as $key => $collection) {
                if ($collection['id'] === "" || $collection['ownerId'] === "" || $collection['ownerFirstName'] === "" || $collection['storeName'] === "" || $collection['phone'] === ""
                || $collection['email'] === "" || $collection['latitude'] === "" || $collection['longitude'] === ""
                || $collection['zone_id'] === "" ||  $collection['DeliveryTime'] === ""  || $collection['Tax'] === "" || $collection['logo'] === ""  ) {
                    Toastr::error(translate('messages.please_fill_all_required_fields'));
                    return back();
                }
                if(isset($collection['DeliveryTime']) && explode("-", (string)$collection['DeliveryTime'])[0] >  explode("-", (string)$collection['DeliveryTime'])[1]){
                    Toastr::error('messages.max_delivery_time_must_be_greater_than_min_delivery_time');
                    return back();
                }
                if(isset($collection['Comission']) && ($collection['Comission'] < 0 ||  $collection['Comission'] > 100) ) {
                    Toastr::error('messages.Comission_must_be_in_0_to_100');
                    return back();
                }
                if(isset($collection['Tax']) && ($collection['Tax'] < 0 ||  $collection['Tax'] > 100 )) {
                    Toastr::error('messages.Tax_must_be_in_0_to_100');
                    return back();
                }
                if(isset($collection['latitude']) && ($collection['latitude'] < -90 ||  $collection['latitude'] > 90 )) {
                    Toastr::error('messages.latitude_must_be_in_-90_to_90');
                    return back();
                }
                if(isset($collection['longitude']) && ($collection['longitude'] < -180 ||  $collection['longitude'] > 180 )) {
                    Toastr::error('messages.longitude_must_be_in_-180_to_180');
                    return back();
                }
                if(isset($collection['MinimumDeliveryFee']) && ($collection['MinimumDeliveryFee'] < 0  )) {
                    Toastr::error('messages.Enter_valid_Minimum_Delivery_Fee');
                    return back();
                }
                if(isset($collection['MinimumOrderAmount']) && ($collection['MinimumOrderAmount'] < 0  )) {
                    Toastr::error('messages.Enter_valid_Minimum_Order_Amount');
                    return back();
                }
                if(isset($collection['PerKmDeliveryFee']) && ($collection['PerKmDeliveryFee'] < 0  )) {
                    Toastr::error('messages.Enter_valid_Per_Km_Delivery_Fee');
                    return back();
                }
                if(isset($collection['MaximumDeliveryFee']) && ($collection['MaximumDeliveryFee'] < 0  )  ) {
                    Toastr::error('messages.Enter_valid_Maximum_Delivery_Fee');
                    return back();
                }



                array_push($vendors, [
                    'id'=>$collection['ownerId'],
                    'f_name' => $collection['ownerFirstName'],
                    'l_name' => $collection['ownerLastName'],
                    'password' => bcrypt(12345678),
                    'phone' => $collection['phone'],
                    'email' => $collection['email'],
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

                array_push($stores, [
                    'id' => $collection['id'],
                    'name' => $collection['storeName'],
                    'phone' => $collection['phone'],
                    'email' => $collection['email'],
                    'logo' => $collection['logo'],
                    'cover_photo' => $collection['CoverPhoto'],
                    'latitude' => $collection['latitude'],
                    'longitude' => $collection['longitude'],
                    'address' => $collection['Address'],
                    'zone_id' => $collection['zone_id'],
                    'module_id' => $collection['module_id'],
                    'minimum_order' => $collection['MinimumOrderAmount'],
                    'comission' => $collection['Comission'],
                    'tax' => $collection['Tax'],
                    'delivery_time' => (isset($collection['DeliveryTime']) && preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $collection['DeliveryTime'])) ? $collection['DeliveryTime'] :'30-40 min',
                    'minimum_shipping_charge' => $collection['MinimumDeliveryFee'],
                    'per_km_shipping_charge' => $collection['PerKmDeliveryFee'],
                    'maximum_shipping_charge' => $collection['MaximumDeliveryFee'],
                    'schedule_order' => $collection['ScheduleOrder'] == 'yes' ? 1 : 0,
                    'status' => $collection['Status'] == 'active' ? 1 : 0,
                    'self_delivery_system' => $collection['SelfDeliverySystem'] == 'active' ? 1 : 0,
                    'veg' => $collection['Veg'] == 'yes' ? 1 : 0,
                    'non_veg' => $collection['NonVeg'] == 'yes' ? 1 : 0,
                    'free_delivery' => $collection['FreeDelivery'] == 'yes' ? 1 : 0,
                    'take_away' => $collection['TakeAway'] == 'yes' ? 1 : 0,
                    'delivery' => $collection['Delivery'] == 'yes' ? 1 : 0,
                    'reviews_section' => $collection['ReviewsSection'] == 'active' ? 1 : 0,
                    'pos_system' => $collection['PosSystem'] == 'active' ? 1 : 0,
                    'active' => $collection['storeOpen'] == 'yes' ? 1 : 0,
                    'featured' => $collection['FeaturedStore'] == 'yes' ? 1 : 0,
                    'vendor_id' => $collection['id'],
                    'updated_at' => now(),
                ]);
            }

            try{
                $chunkSize = 100;
                $chunk_stores= array_chunk($stores,$chunkSize);
                $chunk_vendors= array_chunk($vendors,$chunkSize);


                DB::beginTransaction();

                foreach($chunk_stores as $key=> $chunk_store){
                DB::table('vendors')->upsert($chunk_vendors[$key],['id','email','phone'],['f_name','l_name','password']);
                DB::table('stores')->upsert($chunk_store,['id','email','phone','vendor_id'],['name','logo','cover_photo','latitude','longitude','address','zone_id','module_id','minimum_order','comission','tax','delivery_time','minimum_shipping_charge','per_km_shipping_charge','maximum_shipping_charge','schedule_order','status','self_delivery_system','veg','non_veg','free_delivery','take_away','delivery','reviews_section','pos_system','active','featured']);
                }
                DB::commit();
            }catch(\Exception $e)
            {
                DB::rollBack();
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();
            }

            Toastr::success(translate('messages.store_imported_successfully',['count'=>count($stores)]));
            return back();
    }

    public function bulk_export_index()
    {
        return view('admin-views.vendor.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        $request->validate([
            'type'=>'required',
            'start_id'=>'required_if:type,id_wise',
            'end_id'=>'required_if:type,id_wise',
            'from_date'=>'required_if:type,date_wise',
            'to_date'=>'required_if:type,date_wise'
        ]);
        $vendors = Vendor::with('stores')
        ->when($request['type']=='date_wise', function($query)use($request){
            $query->whereBetween('created_at', [$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59']);
        })
        ->when($request['type']=='id_wise', function($query)use($request){
            $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
        })->whereHas('stores', function ($q) use ($request) {
            return $q->where('module_id', Config::get('module.current_module_id'));
        })
        ->get();
        // Export consumes only a few MB, even with 10M+ rows.
        return  (new FastExcel(StoreLogic::format_export_stores(Helpers::Export_generator($vendors))))->download('Stores.xlsx');
        // return (new FastExcel(StoreLogic::format_export_stores($vendors)))->download('Stores.xlsx');
    }

    public function add_schedule(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i|after:start_time',
            'store_id'=>'required',
        ],[
            'end_time.after'=>translate('messages.End time must be after the start time')
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $temp = StoreSchedule::where('day', $request->day)->where('store_id',$request->store_id)
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

        $store = Store::find($request->store_id);
        $store_schedule = StoreLogic::insert_schedule($request->store_id, [$request->day], $request->start_time, $request->end_time.':59');

        return response()->json([
            'view' => view('admin-views.vendor.view.partials._schedule', compact('store'))->render(),
        ]);
    }

    public function remove_schedule($store_schedule)
    {
        $schedule = StoreSchedule::find($store_schedule);
        if(!$schedule)
        {
            return response()->json([],404);
        }
        $store = $schedule->store;
        $schedule->delete();
        return response()->json([
            'view' => view('admin-views.vendor.view.partials._schedule', compact('store'))->render(),
        ]);
    }

    public function featured(Request $request)
    {
        $store = Store::findOrFail($request->store);
        $store->featured = $request->status;
        $store->save();
        Toastr::success(translate('messages.store_featured_status_updated'));
        return back();
    }

    public function conversation_list(Request $request)
    {

        $user = UserInfo::where('vendor_id', $request->user_id)->first();

        $conversations = Conversation::WhereUser($user->id);

        if ($request->query('key') != null) {
            $key = explode(' ', $request->get('key'));
            $conversations = $conversations->where(function ($qu) use ($key) {

                $qu->whereHas('sender', function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->where('f_name', 'like', "%{$value}%")->orWhere('l_name', 'like', "%{$value}%")->orWhere('phone', 'like', "%{$value}%");
                    }
                })->orWhereHas('receiver', function ($query1) use ($key) {
                        foreach ($key as $value) {
                            $query1->where('f_name', 'like', "%{$value}%")->orWhere('l_name', 'like', "%{$value}%")->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
            });
        }

        $conversations = $conversations->paginate(8);

        $view = view('admin-views.vendor.view.partials._conversation_list', compact('conversations'))->render();
        return response()->json(['html' => $view]);
    }

    public function conversation_view($conversation_id, $user_id)
    {
        $convs = Message::where(['conversation_id' => $conversation_id])->get();
        $conversation = Conversation::find($conversation_id);
        $receiver = UserInfo::find($conversation->receiver_id);
        $sender = UserInfo::find($conversation->sender_id);
        $user = UserInfo::find($user_id);
        return response()->json([
            'view' => view('admin-views.vendor.view.partials._conversations', compact('convs', 'user', 'receiver'))->render()
        ]);
    }


    public function cash_export($type,$store_id)
    {
        $store = Store::find($store_id);
        $account = AccountTransaction::where('from_type', 'store')->where('from_id', $store->id)->get();
        $data=[
            'data' =>$account,
            'search' =>$request['search'] ?? null,
        ];
        if($type == 'csv'){
            return Excel::download(new StoreCashTransactionExport($data), 'CashTransaction.csv');
        }
        return Excel::download(new StoreCashTransactionExport($data), 'CashTransaction.xlsx');
    }

    public function order_export($type,$store_id)
    {
        $store = Store::find($store_id);
        $account = OrderTransaction::where('vendor_id', $store->vendor->id)->latest()->get();
            // if($type == 'excel'){
            //     return (new FastExcel(Helpers::export_order_transaction_report($account)))->download('OrderTransaction.xlsx');
            // }elseif($type == 'csv'){
            //     return (new FastExcel(Helpers::export_order_transaction_report($account)))->download('OrderTransaction.csv');
            // }
            $data=[
                'data' =>$account,
                'search' =>$request['search'] ?? null,
            ];
            if($type == 'csv'){
                return Excel::download(new StoreOrderTransactionExport($data), 'OrderTransaction.csv');
            }
            return Excel::download(new StoreOrderTransactionExport($data), 'OrderTransaction.xlsx');
    }

    public function withdraw_trans_export($type,$store_id)
    {
        $store = Store::find($store_id);
        $account = WithdrawRequest::where('vendor_id', $store->vendor->id)->get();

        $data=[
            'data' =>$account,
            'search' =>$request['search'] ?? null,
        ];
        if($type == 'csv'){
            return Excel::download(new StoreWiseWithdrawTransactionExport($data), 'WithdrawTransaction.csv');
        }
        return Excel::download(new StoreWiseWithdrawTransactionExport($data), 'WithdrawTransaction.xlsx');

    }

    public function store_wise_reviwe_export(Request $request){
        $store =Store::where('id',$request->id)->first();
        $reviews=  $store->reviews()->with('item',function($query){
                $query->withoutGlobalScope(\App\Scopes\StoreScope::class);
            })->latest()->get();
        $store_reviews = \App\CentralLogics\StoreLogic::calculate_store_rating($store['rating']);
        $data=[
            'store_name' =>$store->name,
            'store_id' =>$store->id,
            'rating' =>$store_reviews['rating'],
            'total_reviews' =>$store_reviews['total'],
            'data' => $reviews
        ];
        if($request->type == 'csv'){
            return Excel::download(new StoreWiseItemReviewExport($data), 'StoreWiseItemReview.csv');
        }
        return Excel::download(new StoreWiseItemReviewExport($data), 'StoreWiseItemReview.xlsx');
    }

    public function recommended_store(){
        $key = explode(' ', request()->search);
        $stores=Store::withcount(['orders' ,'items'])->with('Store_config')->where('module_id',Config::get('module.current_module_id'))
        ->wherehas('Store_config', function ($q){
            $q->where('is_recommended_deleted',0);
        })

        ->when(isset($key) , function($q) use($key){
            $q->where(function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->where('name', 'like', "%{$value}%");
                    }
                    $query->orWhereHas('translations',function($query)use($key){
                        $query->where(function($q)use($key){
                            foreach ($key as $value) {
                                $q->where('value', 'like', "%{$value}%");
                            };
                        });
                    });
                });
            })
        ->paginate(config('default_pagination'));

      $shuffle_recommended_store =  DataSetting::where(['key' => 'shuffle_recommended_store' , 'type' => Config::get('module.current_module_id')])?->first()?->value;

        return view('admin-views.vendor.recommended_store_list',compact('stores','shuffle_recommended_store'));
    }
    public function recommended_store_add(Request $request){
        $request->validate([
            'selected_store_ids'=>'required'
        ],[
            'selected_store_ids.required' => translate('Please_select_a_store'),
        ]);
        $ids = explode(',', $request['selected_store_ids']);
        $ids= array_unique($ids);

        foreach($ids as $id){
            StoreConfig::updateOrInsert(['store_id' => $id], [
                'is_recommended' => 1,
                'is_recommended_deleted' => 0
            ]);
        }
        Toastr::success(translate('messages.Recommended_Store_added_successfully'));
        return back();
    }

    public function recommended_store_remove($id){
        StoreConfig::updateOrInsert(['store_id' => $id], [
            'is_recommended_deleted' => 1
        ]);
        Toastr::success(translate('messages.store_is_removed_from_the_recommended_list'));
        return back();
    }

    public function recommended_store_status($id,$status){
        StoreConfig::updateOrInsert(['store_id' => $id], [
            'is_recommended' => $status
        ]);
        Toastr::success(translate('messages.store_recommendation_status_updated'));
        return back();
    }


    public function get_all_stores(Request $request){
        $key = explode(' ', $request['name']);
        $stores= Store::withcount(['orders' ,'items'])->where('module_id',Config::get('module.current_module_id') )
        ->where(function ($query) use ($key) {
            foreach ($key as $value) {
                $query->where('name', 'like', "%{$value}%");
            }
            $query->orWhereHas('translations',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('value', 'like', "%{$value}%");
                    };
                });
            });
        })
        ->take(6)
        ->get()

        ->map(function ($stores) {
            $stores->ratings =  StoreLogic::calculate_store_rating($stores['rating']);
            unset($stores['rating']);
            return $stores;
        });

        return response()->json([
            'result' => view('admin-views.vendor.partials._search_store', compact('stores'))->render(),
        ]);
    }
    public function selected_stores(Request $request){
        $id=$request->id ?? [];
        $id= array_unique($id);

        $stores= Store::whereIn('id' , $id)->where('module_id',Config::get('module.current_module_id') )
        ->get(['id','name','rating', 'logo'])
        ->map(function ($stores) {
            $stores->ratings =  StoreLogic::calculate_store_rating($stores['rating']);
            unset($stores['rating']);
            return $stores;
        });

        return response()->json([
            'result' => view('admin-views.vendor.partials._selected_store', compact('stores'))->render(),
        ]);
    }
    public function shuffle_recommended_store($status){
        // dd($status);
        $data = DataSetting::firstOrNew(
            ['key' =>  'shuffle_recommended_store',
            'type' =>  Config::get('module.current_module_id')],
        );
        $data->value =  $status == 1 ? 0 : 1;
        $data->save();

        Toastr::success(translate('messages.store_shuffle_status_updated'));
        return back();
    }




}
