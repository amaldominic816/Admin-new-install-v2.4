<?php

namespace App\Http\Controllers\Admin;

use App\Models\CommonCondition;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;

class CommonConditionController extends Controller
{
    function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        $conditions=CommonCondition::
        // with(['products'])->
        when(isset($key) , function ($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        })
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.common-condition.index',compact('conditions'));
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'name.0' => 'required',
        ], [
            'name.required' => translate('messages.Name is required!'),
            'name.0.required'=>translate('default_name_is_required'),
        ]);

        $condition = new CommonCondition();
        $condition->name = $request->name[array_search('default', $request->lang)];
        $condition->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($default_lang == $key && !($request->name[$index])){
                if($key != 'default')
                {
                    array_push($data, Array(
                        'translationable_type'  => 'App\Models\CommonCondition',
                        'translationable_id'    => $condition->id,
                        'locale'                => $key,
                        'key'                   => 'name',
                        'value'                 => $condition->name,
                    ));
                }
            }else{

                if($request->name[$index] && $key != 'default')
                {
                    array_push($data, Array(
                        'translationable_type'  => 'App\Models\CommonCondition',
                        'translationable_id'    => $condition->id,
                        'locale'                => $key,
                        'key'                   => 'name',
                        'value'                 => $request->name[$index],
                    ));
                }
            }
        }
        if(count($data))
        {
            Translation::insert($data);
        }

        Toastr::success(translate('messages.common_condition_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        $condition = CommonCondition::withoutGlobalScope('translate')->findOrFail($id);
        return view('admin-views.common-condition.edit', compact('condition'));
    }

    public function status(Request $request)
    {
        $condition = CommonCondition::find($request->id);
        $condition->status = $request->status;
        $condition->save();
        Toastr::success(translate('messages.common_condition_status_updated'));
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100',
            'name.0' => 'required',
        ],[
            'name.0.required'=>translate('default_name_is_required'),
        ]);

        $condition = CommonCondition::find($id);
        $slug = Str::slug($request->name[array_search('default', $request->lang)]);
        $condition->slug = $condition->slug? $condition->slug :"{$slug}{$condition->id}";
        $condition->name = $request->name[array_search('default', $request->lang)];
        $condition->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach($request->lang as $index=>$key)
        {
            if($default_lang == $key && !($request->name[$index])){
                if($key != 'default')
                {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\CommonCondition',
                            'translationable_id'    => $condition->id,
                            'locale'                => $key,
                            'key'                   => 'name'],
                        ['value'                 => $condition->name]
                    );
                }
            }else{

                if($request->name[$index] && $key != 'default')
                {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\CommonCondition',
                            'translationable_id'    => $condition->id,
                            'locale'                => $key,
                            'key'                   => 'name'],
                        ['value'                 => $request->name[$index]]
                    );
                }
            }
        }
        Toastr::success(translate('messages.common_condition_updated_successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        $condition = CommonCondition::findOrFail($request->id);
        $condition->translations()->delete();
        $condition->delete();
        Toastr::success(translate('common_condition_removed!'));
        return back();
    }

    public function get_all(Request $request){
        $data = CommonCondition::where('name', 'like', '%'.$request->q.'%')->limit(8)->get()

        ->map(function ($condition) {
            return [
                'id' => $condition->id,
                'text' => $condition->name,
            ];
        });


        if(isset($request->all))
        {
            $data[]=(object)['id'=>'all', 'text'=>translate('messages.all')];
        }
        return response()->json($data);
    }
}
