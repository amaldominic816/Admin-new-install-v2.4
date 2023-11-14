<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Banner;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\CentralLogics\BannerLogic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function get_banners(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id= $request->header('zoneId');
        $banners = BannerLogic::get_banners($zone_id, $request->query('featured'));
        $campaigns = [];
        if(!$request->featured)
        {
            $campaigns = Campaign::
            whereHas('module.zones',function($query)use($zone_id){
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->when(config('module.current_module_data'), function($query)use($zone_id){
                $query->module(config('module.current_module_data')['id']);
                if(!config('module.current_module_data')['all_zone_service']) {
                    $query->whereHas('stores', function($q)use($zone_id){
                        $q->whereIn('zone_id', json_decode($zone_id, true));
                    });
                }
            })
            ->running()->active()->get();
        }

        try {
            return response()->json(['campaigns'=>Helpers::basic_campaign_data_formatting($campaigns, true),'banners'=>$banners], 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_store_banners(Request $request,$store_id)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id= $request->header('zoneId');
        $banners = Banner::active();
        if(config('module.current_module_data')) {
            $banners = $banners->whereHas('zone.modules', function($query){
                $query->where('modules.id', config('module.current_module_data')['id']);
            })
            ->module(config('module.current_module_data')['id'])
            ->when(!config('module.current_module_data')['all_zone_service'], function($query)use($zone_id){
                $query->whereIn('zone_id', json_decode($zone_id, true));
            });
        }

        $banners = $banners->whereIn('zone_id', json_decode($zone_id, true))->whereHas('module',function($query){
            $query->active();
        })->where('data',$store_id)->where('created_by','store')
        ->get();

        return response()->json($banners, 200);
    }
}
