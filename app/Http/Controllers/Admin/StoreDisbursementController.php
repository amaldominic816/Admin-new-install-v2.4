<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\DisbursementExport;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Store;
use App\Models\Disbursement;
use App\Models\DisbursementDetails;
use App\Models\StoreWallet;
use App\Models\WithdrawRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class StoreDisbursementController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->status??'all';
        $disbursements = Disbursement::
        when($status!='all', function($q) use($status){
                return $q->where('status',$status);
        })
        ->where('created_for','store')
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.store-disbursement.index', compact('disbursements','status'));
    }

    public function view(Request $request,$id)
    {
        $key = explode(' ', $request['search']);
        $store_id = $request->query('store_id', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');
        $disbursement = Disbursement::findOrFail($id);
        $store = is_numeric($store_id) ? Store::findOrFail($store_id) : null;
        $module_id = $request->query('module_id', 'all');


        $disbursements=DisbursementDetails::with('store','withdraw_method')->where(['disbursement_id'=>$id])
            ->when(isset($key) , function($q) use($key){
                $q->whereHas('store', function ($q) use($key){
                    $q->where(function($query)use ($key){
                        $query->orWhereHas('vendor', function ($q) use ($key) {
                            foreach ($key as $value) {
                                $q->orWhere('f_name', 'like', "%{$value}%")
                                    ->orWhere('l_name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%")
                                    ->orWhere('phone', 'like', "%{$value}%");
                            }
                        })
                            ->where(function ($q) use ($key) {
                                foreach ($key as $value) {
                                    $q->orWhere('name', 'like', "%{$value}%")
                                        ->orWhere('email', 'like', "%{$value}%")
                                        ->orWhere('phone', 'like', "%{$value}%");
                                }
                            });
                    });
                });
            })

            ->when((isset($store_id) && is_numeric($store_id)), function ($query) use ($store_id){
                $query->where('store_id', $store_id);
            })
            ->when((isset($module_id) &&  is_numeric($module_id)), function ($query) use ($module_id) {
                return $query->whereHas('store', function ($query) use ($module_id) {
                    $query->where('module_id',$module_id);
                });
            })

            ->when((isset($payment_method_id) && is_numeric($payment_method_id)), function ($query) use ($payment_method_id){
                $query->whereHas('withdraw_method', function ($q) use($payment_method_id){
                    return $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->latest();
        $store_ids = json_encode($disbursements->pluck('store_id')->toArray());
        $disbursement_stores = $disbursements->paginate(config('default_pagination'));
        return view('admin-views.store-disbursement.view', compact('disbursement','disbursement_stores','store_ids','store_id','payment_method_id','store'));
    }
    public function export(Request $request,$id, $type = 'excel')
    {
        $key = explode(' ', $request['search']);
        $store_id = $request->query('store_id', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');
        $disbursement = Disbursement::findOrFail($id);
        $disbursements=DisbursementDetails::where(['disbursement_id'=>$id])
            ->when(isset($key) , function($q) use($key){
                $q->whereHas('store', function ($q) use($key){
                    $q->where(function($query)use ($key){
                        $query->orWhereHas('vendor', function ($q) use ($key) {
                            foreach ($key as $value) {
                                $q->orWhere('f_name', 'like', "%{$value}%")
                                    ->orWhere('l_name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%")
                                    ->orWhere('phone', 'like', "%{$value}%");
                            }
                        })
                            ->where(function ($q) use ($key) {
                                foreach ($key as $value) {
                                    $q->orWhere('name', 'like', "%{$value}%")
                                        ->orWhere('email', 'like', "%{$value}%")
                                        ->orWhere('phone', 'like', "%{$value}%");
                                }
                            });
                    });
                });
            })
            ->when((isset($store_id) && is_numeric($store_id)), function ($query) use ($store_id){
                $query->where('store_id', $store_id);
            })
            ->when((isset($payment_method_id) && is_numeric($payment_method_id)), function ($query) use ($payment_method_id){
                $query->whereHas('withdraw_method', function ($q) use($payment_method_id){
                    return $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->latest()->get();
        $data=[
            'type'=>'store',
            'disbursement' =>$disbursement,
            'disbursements' =>$disbursements,
        ];
        if($type == 'pdf'){
            $mpdf_view = View::make('admin-views.store-disbursement.pdf', compact('disbursement','disbursements')
            );
            Helpers::gen_mpdf(view: $mpdf_view,file_prefix: 'Disbursement',file_postfix: $id);
        }elseif($type == 'csv'){
            return Excel::download(new DisbursementExport($data), 'Disbursement.csv');
        }
        return Excel::download(new DisbursementExport($data), 'Disbursement.xlsx');
    }

    public function status(Request $request)
    {
        $disbursements=DisbursementDetails::where(['disbursement_id'=>$request->disbursement_id])->whereIn('store_id',$request->store_ids)->get();

        foreach ($disbursements as $disbursement){
            $wallet=  StoreWallet::where('vendor_id',$disbursement->store->vendor_id)->first();


            if ( (string) $wallet->total_earning <  (string) ($wallet->total_withdrawn + $wallet->pending_withdraw) ) {
                return response()->json([
                    'status' => 'error',
                    'message'=> translate('messages.Blalnce_mismatched_total_earning_is_too_low_for').' '.$disbursement->store?->name,
                ]);
            }

            if($request->status == 'completed'){
                if($disbursement->status != 'completed') {
                    $withdraw = new WithdrawRequest();
                    $withdraw->vendor_id = $disbursement->store?->vendor?->id;
                    $withdraw->amount = $disbursement['disbursement_amount'];
                    $withdraw->withdrawal_method_id = $disbursement['payment_method'];
                    $withdraw->withdrawal_method_fields = $disbursement->withdraw_method->method_fields;
                    $withdraw->approved = 1;
                    $withdraw->transaction_note =$disbursement->id;
                    $withdraw->type = 'disbursement';

                    if($disbursement->status== 'canceled'){
                        $wallet->increment('total_withdrawn', $disbursement['disbursement_amount']);
                        } else{
                            $wallet->decrement('pending_withdraw', $disbursement['disbursement_amount']);
                            $wallet->increment('total_withdrawn', $disbursement['disbursement_amount']);
                        }
                    $withdraw->save();
                }
            }elseif ($request->status == 'canceled'){
                if($disbursement->status == 'completed'){
                    return response()->json([
                        'status' => 'error',
                        'message'=> translate('messages.can_not_cancel_completed_disbursement_,_uncheck_completed_disbursements')
                    ]);
                }

                $wallet->decrement('pending_withdraw', $disbursement['disbursement_amount']);
            }
            $disbursement->status = $request->status;
            $disbursement->save();
        }

        self::check_status($request->disbursement_id);


        return response()->json([
            'status' => 'success',
            'message'=> translate('messages.status_updated')
        ]);
    }

    public function statusById($id,$status)
    {
        $disbursement=DisbursementDetails::find($id);
        $wallet=  StoreWallet::where('vendor_id',$disbursement->store->vendor_id)->first();

        if ((string) $wallet->total_earning <  (string) ($wallet->total_withdrawn + $wallet->pending_withdraw) ) {
            Toastr::error(translate('messages.Blalnce_mismatched_total_earning_is_too_low'));
            return back();

        }

        if($status == 'completed'){
            $withdraw = new WithdrawRequest();
            $withdraw->vendor_id = $disbursement->store?->vendor?->id;
            $withdraw->amount = $disbursement['disbursement_amount'];
            $withdraw->withdrawal_method_id = $disbursement['payment_method'];
            $withdraw->withdrawal_method_fields = $disbursement->withdraw_method->method_fields;
            $withdraw->approved = 1;
            $withdraw->transaction_note = $id;
            $withdraw->type = 'disbursement';

            if($disbursement->status== 'canceled'){
                $wallet->increment('total_withdrawn', $disbursement['disbursement_amount']);
                } else{
                    $wallet->decrement('pending_withdraw', $disbursement['disbursement_amount']);
                    $wallet->increment('total_withdrawn', $disbursement['disbursement_amount']);
                }
            $withdraw->save();
        }
        elseif ($status == 'canceled'){
            if($disbursement->status == 'completed'){
                Toastr::error(translate('messages.can_not_cancel_completed_disbursement_,_uncheck_completed_disbursements'));
                return back();
            }
            $wallet->decrement('pending_withdraw', $disbursement['disbursement_amount']);

        }elseif ($status == 'pending'){
            if($disbursement->status == 'completed'){
                $withdraw = WithdrawRequest::where('transaction_note',$id)->where('vendor_id', $disbursement->store->vendor_id)->first();
                if ($withdraw){
                    $withdraw->delete();
                }
            }
            $wallet->decrement('total_withdrawn', $disbursement['disbursement_amount']);
            $wallet->increment('pending_withdraw', $disbursement['disbursement_amount']);
        }

        $disbursement->status = $status;
        $disbursement->save();

        self::check_status($disbursement->disbursement_id);

        Toastr::success(translate('messages.status_updated'));
        return back();
    }
    public function generate_disbursement()
    {
        $stores = Store::all();
        $disbursement_details = [];
        $total_amount = 0;

        $disbursement = new Disbursement();
        $disbursement->id = 1000 + Disbursement::count() + 1;
        if (Disbursement::find($disbursement->id)) {
            $disbursement->id = Disbursement::orderBy('id', 'desc')->first()->id + 1;
        }
        $disbursement->title = 'Disbursement # '.$disbursement->id;
        $minimum_amount = BusinessSetting::where(['key' => 'store_disbursement_min_amount'])->first()?->value;
        foreach ($stores as $store){
            if(isset($store->vendor->wallet)){

                $total_earning = $store->vendor->wallet->total_earning ?? 0;
                $total_withdraw = ($store->vendor->wallet->total_withdrawn ?? 0) + ($store->vendor->wallet->pending_withdraw ?? 0);
                $total_cash_in_hand = $store->vendor->wallet->collected_cash ?? 0;
                $disbursement_amount = ((string) $total_earning> (string) ($total_withdraw+$total_cash_in_hand))?(  ($total_earning - ($total_withdraw+$total_cash_in_hand))):0;

                if ($disbursement_amount > $minimum_amount && isset($store->disbursement_method)){

                    $res_d = [
                        'disbursement_id' => $disbursement->id,
                        'store_id' => $store->id,
                        'disbursement_amount' => $disbursement_amount,
                        'payment_method' => $store->disbursement_method->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $disbursement_details[] = $res_d;
                    $total_amount += $res_d['disbursement_amount'];

                    $store->vendor->wallet->pending_withdraw = $store->vendor->wallet->pending_withdraw + $disbursement_amount;
                    $store->vendor->wallet->save();
                }
            }
        }

        if ($total_amount > 0){
            $disbursement->total_amount = $total_amount;
            $disbursement->created_for = 'store';
            $disbursement->save();

            DisbursementDetails::insert($disbursement_details);
        }
        info("Store-----Disbursement");
        return true;

    }

    public function check_status($id) {
        $disbursements = DisbursementDetails::where(['disbursement_id' => $id])->get();
        $statusCounts = $disbursements->countBy('status');

        $disbursement = Disbursement::find($id);

        if (isset($statusCounts['pending']) && ($statusCounts['pending'] == count($disbursements))) {
            $disbursement->status = 'pending';
        } elseif (isset($statusCounts['canceled']) && ($statusCounts['canceled'] == count($disbursements))) {
            $disbursement->status = 'canceled';
        } elseif (isset($statusCounts['completed']) && ($statusCounts['completed'] == count($disbursements))) {
            $disbursement->status = 'completed';
        } else {
            $disbursement->status = 'partially_completed';
        }

        return $disbursement->save();
    }
}
