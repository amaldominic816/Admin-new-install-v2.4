<?php

namespace App\Http\Controllers\Admin;

use App\Models\WalletBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;

class WalletBonusController extends Controller
{
    public function add_new()
    {
        $bonuses = WalletBonus::latest()->paginate(config('default_pagination'));
        return view('admin-views.wallet-bonus.index', compact('bonuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:191',
            'start_date' => 'required',
            'end_date' => 'required',
            'bonus_type' => 'required|in:percentage,amount',
            'bonus_amount' => 'required',
            'minimum_add_amount' => 'required',
            'maximum_bonus_amount' => 'required_if:bonus_type,percentage',
            'title.0' => 'required',
        ],[
            'title.0.required'=>translate('default_title_is_required'),
        ]);

        $bonus = new WalletBonus();
        $bonus->title = $request->title[array_search('default', $request->lang)];
        $bonus->description = $request->description[array_search('default', $request->lang)];
        $bonus->bonus_type = $request->bonus_type;
        $bonus->start_date = $request->start_date;
        $bonus->end_date = $request->end_date;
        $bonus->minimum_add_amount = $request->minimum_add_amount != null ? $request->minimum_add_amount : 0;
        $bonus->maximum_bonus_amount = $request->maximum_bonus_amount != null ? $request->maximum_bonus_amount : 0;
        $bonus->bonus_amount = $request->bonus_amount;
        $bonus->status =  1;
        $bonus->save();

        $data = [];
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if($default_lang == $key && !($request->title[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\WalletBonus',
                        'translationable_id' => $bonus->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $bonus->title,
                    ));
                }
            }else{
                if ($request->title[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\WalletBonus',
                        'translationable_id' => $bonus->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
            }
            if($default_lang == $key && !($request->description[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\WalletBonus',
                        'translationable_id' => $bonus->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $bonus->description,
                    ));
                }
            }else{
                if ($request->description[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\WalletBonus',
                        'translationable_id' => $bonus->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $request->description[$index],
                    ));
                }
            }
        }

        Translation::insert($data);

        Toastr::success(translate('messages.bonus_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        $bonus = WalletBonus::withoutGlobalScope('translate')->where(['id' => $id])->first();
        return view('admin-views.wallet-bonus.edit', compact('bonus'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:191',
            'start_date' => 'required',
            'end_date' => 'required',
            'bonus_type' => 'required|in:percentage,amount',
            'bonus_amount' => 'required',
            'minimum_add_amount' => 'required',
            'maximum_bonus_amount' => 'required_if:bonus_type,percentage',
            'title.0' => 'required',
        ],[
            'title.0.required'=>translate('default_title_is_required'),
        ]);

        $bonus = WalletBonus::find($id);
        $bonus->title = $request->title[array_search('default', $request->lang)];
        $bonus->description = $request->description[array_search('default', $request->lang)];
        $bonus->bonus_type = $request->bonus_type;
        $bonus->start_date = $request->start_date;
        $bonus->end_date = $request->end_date;
        $bonus->minimum_add_amount = $request->minimum_add_amount != null ? $request->minimum_add_amount : 0;
        $bonus->maximum_bonus_amount = $request->maximum_bonus_amount != null ? $request->maximum_bonus_amount : 0;
        $bonus->bonus_amount = $request->bonus_amount;
        $bonus->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if($default_lang == $key && !($request->title[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\WalletBonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'title'
                        ],
                        ['value' => $bonus->title]
                    );
                }
            }else{

                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\WalletBonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'title'
                        ],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            if($default_lang == $key && !($request->description[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\WalletBonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $bonus->description]
                    );
                }
            }else{

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\WalletBonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }
        Toastr::success(translate('messages.bonus_updated_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        $bonus = WalletBonus::find($request->id);
        $bonus->status = $request->status;
        $bonus->save();
        Toastr::success(translate('messages.bonus_status_updated'));
        return back();
    }

    public function delete(Request $request)
    {
        $bonus = WalletBonus::find($request->id);
        $bonus->delete();
        Toastr::success(translate('messages.bonus_deleted_successfully'));
        return back();
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $bonuses=WalletBonus::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('title', 'like', "%{$value}%");
            }
        })->limit(50)->get();
        return response()->json([
            'view'=>view('admin-views.wallet-bonus.partials._table',compact('bonuses'))->render(),
            'count'=>$bonuses->count()
        ]);
    }
}
