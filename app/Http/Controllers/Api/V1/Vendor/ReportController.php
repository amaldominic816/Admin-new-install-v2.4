<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\BusinessSetting;
use App\Models\DisbursementDetails;
use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;

class ReportController extends Controller
{
    public function expense_report(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'from' => 'required',
            'to' => 'required',
        ]);

        $key = explode(' ', $request['search']);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $limit = $request['limite']??25;
        $offset = $request['offset']??1;
        $from = $request->from;
        $to = $request->to;
        $store_id = $request->vendor->stores[0]->id;

        $expense = Expense::where('created_by','vendor')->where('store_id',$store_id)
            ->when(isset($from) &&  isset($to) ,function($query) use($from,$to){
                $query->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:29']);
            })->when(isset($key), function($query) use($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('order_id', 'like', "%{$value}%");
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $offset);
            $data = [
                'total_size' => $expense->total(),
                'limit' => $limit,
                'offset' => $offset,
                'expense' => $expense->items()
            ];
            return response()->json($data,200);
    }

    public function disbursement_report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $limit = $request['limit']??25;
        $offset = $request['offset']??1;

        $store_id = $request?->vendor?->stores[0]?->id;

        $total_disbursements=DisbursementDetails::where('store_id',$store_id)->orderBy('created_at', 'desc')->get();
        $paginator=DisbursementDetails::where('store_id',$store_id)->latest()->paginate($limit, ['*'], 'page', $offset);

        $paginator->each(function ($data) {
            $data->withdraw_method->method_fields = json_decode($data->withdraw_method->method_fields,true);
        });

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'pending' =>(float) $total_disbursements->where('status','pending')->sum('disbursement_amount'),
            'completed' =>(float) $total_disbursements->where('status','completed')->sum('disbursement_amount'),
            'canceled' =>(float) $total_disbursements->where('status','canceled')->sum('disbursement_amount'),
            'complete_day' =>(int) BusinessSetting::where(['key'=>'store_disbursement_waiting_time'])->first()?->value,
            'disbursements' => $paginator->items()
        ];
        return response()->json($data,200);

    }


}
