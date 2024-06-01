<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\DisbursementWithdrawalMethod;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\WithdrawalMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WithdrawMethodController extends Controller
{
    public function get_disbursement_withdrawal_methods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $vendor = $request['vendor'];
        $store=  $vendor->stores[0];

        $key = explode(' ', $request['search']);
        $paginator = DisbursementWithdrawalMethod::where('store_id', $store['id'])
            ->when( isset($key) , function($query) use($key){
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('method_name', 'like', "%{$value}%");
                    }
                });
            }
            )
            ->latest()
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $datas =[];
        $userInputs=[];
        foreach ($paginator->items() as $k => $v) {
            $userInputs=[];
            foreach(json_decode($v->method_fields,true)as $key => $value){
                $userInput = [
                    'user_input' => $key,
                    'user_data' => $value,
                ];
                $userInputs[] = $userInput;
            }
            $v['method_fields'] = $userInputs;
            $datas[] = $v;
        }

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'methods' =>  $datas
        ];
        return response()->json($data, 200);
    }

    public function disbursement_withdrawal_method_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'withdraw_method_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $vendor = $request['vendor'];
        $store=  $vendor->stores[0];

        $method = WithdrawalMethod::find($request['withdraw_method_id']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }

        $data = [
            'store_id' => $store['id'],
            'withdrawal_method_id' => $method['id'],
            'method_name' => $method['method_name'],
            'method_fields' => json_encode($method_data),
            'is_default' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('disbursement_withdrawal_methods')->insert($data);

        return response()->json(['message'=>'successfully added!'], 200);
    }

    public function disbursement_withdrawal_method_default(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'is_default' => 'required',
        ]);

        $vendor = $request['vendor'];
        $store=  $vendor->stores[0];

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $method = DisbursementWithdrawalMethod::find($request->id);
        $method->is_default = $request->is_default;
        $method->save();
        DisbursementWithdrawalMethod::whereNot('id', $request->id)->where('store_id',$store['id'])->update(['is_default' => 0]);
        return response()->json(['message'=>translate('messages.method_updated_successfully')], 200);
    }

    public function disbursement_withdrawal_method_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $method = DisbursementWithdrawalMethod::find($request->id);
        $method->delete();
        return response()->json(['message'=>translate('messages.method_deleted_successfully')], 200);
    }
    public function withdraw_method_list(){
        $wi=WithdrawalMethod::where('is_active',1)->get();
        return response()->json($wi,200);
    }
}
