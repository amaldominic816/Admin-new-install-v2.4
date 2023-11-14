<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FlashSaleItem;
use Illuminate\Support\Facades\Validator;

class FlashSaleController extends Controller
{
    public function get_flash_sales(Request $request){
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 200);
        }
        $zone_id= $request->header('zoneId');
        try {
            $flash_sales = FlashSale::with(['activeProducts','activeProducts.item'])->module(config('module.current_module_data')['id'])->whereHas('module.zones', function($query)use($zone_id){
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })->whereHas('activeProducts.item.store',function($query) use ($zone_id){
                $query->when(config('module.current_module_data'), function($query){
                    $query->where('module_id', config('module.current_module_data')['id'])->whereHas('zone.modules',function($query){
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                })->whereIn('zone_id', json_decode($zone_id, true));
            })
            ->running()->active()->first();
            if ($flash_sales) {
                $flash_sales->activeProducts->each(function ($activeProduct) {
                    $activeProduct->item = Helpers::product_data_formatting($activeProduct->item, false, false, app()->getLocale());
                });
            }
            return response()->json($flash_sales, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
    public function get_flash_sale_items(Request $request){
        $validator = Validator::make($request->all(), [
            'flash_sale_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 200);
        }
        $limit = isset($request['limit'])?$request['limit']:50;
        $offset = isset($request['offset'])?$request['offset']:1;
        $zone_id= $request->header('zoneId');
        $flash_sale = FlashSale::whereHas('module.zones', function($query)use($zone_id){
            $query->whereIn('zones.id', json_decode($zone_id, true));
        })->module(config('module.current_module_data')['id'])
        ->running()->active()->first();
        if(!$flash_sale){
            return response()->json([
                'errors' => [
                    ['code' => 'flash_sale', 'message' => translate('messages.not_found')]
                ]
            ], 403);
        }
        try {
            $flash_sale_items = FlashSaleItem::where('flash_sale_id',$flash_sale->id)->active()->paginate($limit, ['*'], 'page', $offset);
            if ($flash_sale_items) {
                $flash_sale_items->each(function ($activeProduct) {
                    $activeProduct->item = Helpers::product_data_formatting($activeProduct->item, false, false, app()->getLocale());
                });
            }
            $data =  [
                'total_size' => $flash_sale_items->total(),
                'limit' => $limit,
                'offset' => $offset,
                'flash_sale' => $flash_sale,
                'products' => $flash_sale_items->items()
            ];
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
