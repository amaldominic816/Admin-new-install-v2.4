<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\DisbursementExport;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\DeliveryMan;
use App\Models\Disbursement;
use App\Models\DisbursementDetails;
use App\Models\ProvideDMEarning;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class DeliveryManDisbursementController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->status??'all';
        $disbursements = Disbursement::
        when($status!='all', function($q) use($status){
            return $q->where('status',$status);
        })
        ->where('created_for','delivery_man')
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.dm-disbursement.index', compact('disbursements','status'));
    }

    public function view(Request $request,$id)
    {
        $key = explode(' ', $request['search']);
        $delivery_man_id = $request->query('delivery_man_id', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');
        $disbursement = Disbursement::findOrFail($id);
        $disbursements=DisbursementDetails::with('delivery_man','withdraw_method')->where(['disbursement_id'=>$id])
            ->when(isset($key) , function($q) use($key){
                $q->whereHas('delivery_man', function ($q) use($key){
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
                });
            })
            ->when((isset($delivery_man_id) && is_numeric($delivery_man_id)), function ($query) use ($delivery_man_id){
                $query->where('delivery_man_id', $delivery_man_id);
            })
            ->when((isset($payment_method_id) && is_numeric($payment_method_id)), function ($query) use ($payment_method_id){
                $query->whereHas('withdraw_method', function ($q) use($payment_method_id){
                    return $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->latest();
        $dm_ids = json_encode($disbursements->pluck('delivery_man_id')->toArray());
        $disbursement_delivery_mans = $disbursements->paginate(config('default_pagination'));
        return view('admin-views.dm-disbursement.view', compact('disbursement','disbursement_delivery_mans','delivery_man_id','dm_ids','payment_method_id'));
    }
    public function export(Request $request,$id,$type='excel')
    {
        $key = explode(' ', $request['search']);
        $delivery_man_id = $request->query('delivery_man_id', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');
        $disbursement = Disbursement::findOrFail($id);
        $disbursements=DisbursementDetails::where(['disbursement_id'=>$id])
            ->when(isset($key) , function($q) use($key){
                $q->whereHas('delivery_man', function ($q) use($key){
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
                });
            })
            ->when((isset($delivery_man_id) && is_numeric($delivery_man_id)), function ($query) use ($delivery_man_id){
                $query->where('delivery_man_id', $delivery_man_id);
            })
            ->when((isset($payment_method_id) && is_numeric($payment_method_id)), function ($query) use ($payment_method_id){
                $query->whereHas('withdraw_method', function ($q) use($payment_method_id){
                    return $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->latest()->get();
        $data=[
            'type'=>'dm',
            'disbursement' =>$disbursement,
            'disbursements' =>$disbursements,
        ];
        if($type == 'pdf'){
            $mpdf_view = View::make('admin-views.dm-disbursement.pdf', compact('disbursement','disbursements')
            );
            Helpers::gen_mpdf(view: $mpdf_view,file_prefix: 'Disbursement',file_postfix: $id);
        }elseif($type == 'csv'){
            return Excel::download(new DisbursementExport($data), 'Disbursement.csv');
        }
        return Excel::download(new DisbursementExport($data), 'Disbursement.xlsx');
    }

    public function status(Request $request)
    {
        $disbursements=DisbursementDetails::where(['disbursement_id'=>$request->disbursement_id])->whereIn('delivery_man_id',$request->delivery_man_ids)->get();

        foreach ($disbursements as $disbursement){

            if ( (string)  $disbursement->delivery_man?->wallet?->total_earning <  (string) ($disbursement->delivery_man?->wallet?->total_withdrawn + $disbursement->delivery_man?->wallet?->pending_withdraw) ) {
                return response()->json([
                    'status' => 'error',
                    'message'=> translate('messages.Balance_mismatched_total_earning_is_too_low_for').' '.$disbursement->delivery_man?->name,
                ]);
            }


            if($request->status == 'completed') {
                if ($disbursement->status != 'completed') {
                    $provide_dm_earning = new ProvideDMEarning();
                    $provide_dm_earning->delivery_man_id = $disbursement->delivery_man_id;
                    $provide_dm_earning->method = $disbursement?->withdraw_method?->method_name;
                    $provide_dm_earning->ref = $disbursement->id;
                    $provide_dm_earning->amount = $disbursement['disbursement_amount'];


                    // if((string)  $disbursement->delivery_man?->wallet?->pending_withdraw  >=   (string) $disbursement['disbursement_amount']){
                    //     $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
                    //     $disbursement->delivery_man?->wallet?->increment('total_withdrawn', $disbursement['disbursement_amount']);
                    // } else{
                    //     return response()->json([
                    //         'status' => 'error',
                    //         'message'=> translate('messages.Balance_mismatched_for').' '.$disbursement->delivery_man?->f_name,
                    //     ]);
                    // }


                    if($disbursement->status== 'canceled'){
                        $disbursement->delivery_man?->wallet?->increment('total_withdrawn', $disbursement['disbursement_amount']);
                        } else{
                            $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
                            $disbursement->delivery_man?->wallet?->increment('total_withdrawn', $disbursement['disbursement_amount']);
                        }


                    $provide_dm_earning->save();

                }
            }elseif ($request->status == 'canceled'){
                if($disbursement->status == 'completed'){
                    return response()->json([
                        'status' => 'error',
                        'message'=> translate('messages.can_not_cancel_completed_disbursement_,_uncheck_completed_disbursements')
                    ]);
                }


                $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
                // if((string)  $disbursement->delivery_man?->wallet?->pending_withdraw  >=  (string)  $disbursement['disbursement_amount']){
                //   $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
                //     } else{
                //         return response()->json([
                //             'status' => 'error',
                //             'message'=> translate('messages.Balance_mismatched_for').' '.$disbursement->delivery_man?->f_name,
                //         ]);
                //     }
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

            if ((string) $disbursement->delivery_man?->wallet?->total_earning <  (string)($disbursement->delivery_man?->wallet?->total_withdrawn + $disbursement->delivery_man?->wallet?->pending_withdraw) ) {
                Toastr::error(translate('messages.Balance_mismatched_total_earning_is_too_low_for').' '.$disbursement->delivery_man?->f_name);
                return back();
            }


        if($status == 'completed'){
            $provide_dm_earning = new ProvideDMEarning();
            $provide_dm_earning->delivery_man_id = $disbursement->delivery_man_id;
            $provide_dm_earning->method = $disbursement?->withdraw_method?->method_name;
            $provide_dm_earning->ref = $id;
            $provide_dm_earning->amount = $disbursement['disbursement_amount'];

            // if((string)  $disbursement->delivery_man?->wallet?->pending_withdraw  >=  (string) $disbursement['disbursement_amount']){
            //     $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
            //     $disbursement->delivery_man?->wallet?->increment('total_withdrawn', $disbursement['disbursement_amount']);
            // } else{
            //     Toastr::error(translate('messages.Balance_mismatched_for').' '.$disbursement->delivery_man?->f_name);
            //     return back();
            // }



            if($disbursement->status== 'canceled'){
                $disbursement->delivery_man?->wallet?->increment('total_withdrawn', $disbursement['disbursement_amount']);
                } else{
                    $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
                    $disbursement->delivery_man?->wallet?->increment('total_withdrawn', $disbursement['disbursement_amount']);
                }



            $provide_dm_earning->save();

        }elseif ($status == 'canceled'){
            if($disbursement->status == 'completed'){
                Toastr::error(translate('messages.can_not_cancel_completed_disbursement_,_uncheck_completed_disbursements'));
                return back();
            }

            $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
            // if((string) $disbursement->delivery_man?->wallet?->pending_withdraw  >=   (string) $disbursement['disbursement_amount']){

            //     $disbursement->delivery_man?->wallet?->decrement('pending_withdraw', $disbursement['disbursement_amount']);
            //     } else{
            //         Toastr::error(translate('messages.Balance_mismatched_for').' '.$disbursement->delivery_man?->f_name);
            //         return back();
            //     }



        }elseif ($status == 'pending'){
            if($disbursement->status == 'completed'){
                $withdraw = ProvideDMEarning::where('ref',$id)->where('delivery_man_id', $disbursement->delivery_man_id)->first();
                if ($withdraw){
                    $withdraw->delete();
                }
            }
            $disbursement->delivery_man?->wallet?->decrement('total_withdrawn', $disbursement['disbursement_amount']);
            $disbursement->delivery_man?->wallet?->increment('pending_withdraw', $disbursement['disbursement_amount']);
        }

        $disbursement->status = $status;
        $disbursement->save();

        self::check_status($disbursement->disbursement_id);

        Toastr::success(translate('messages.status_updated'));
        return back();
    }
    public function generate_disbursement()
    {
        $delivery_mans = DeliveryMan::where('type' ,'zone_wise')->where('earning',1)->get();
        $disbursement_details = [];
        $total_amount = 0;

        $disbursement = new Disbursement();
        $disbursement->id = 1000 + Disbursement::count() + 1;
        if (Disbursement::find($disbursement->id)) {
            $disbursement->id = Disbursement::orderBy('id', 'desc')->first()->id + 1;
        }
        $disbursement->title = 'Disbursement # '.$disbursement->id;
        $minimum_amount = BusinessSetting::where(['key' => 'dm_disbursement_min_amount'])->first()?->value;
        foreach ($delivery_mans as $delivery_man){
            if(isset($delivery_man->wallet)){

                $total_earning = $delivery_man->wallet?$delivery_man->wallet->total_earning:0;
                $total_withdraw = ($delivery_man->wallet?$delivery_man->wallet->total_withdrawn:0) + ($delivery_man->wallet?$delivery_man->wallet->pending_withdraw:0);
                $total_cash_in_hand = $delivery_man->wallet?$delivery_man->wallet->collected_cash:0;
                $disbursement_amount = ( (string) $total_earning >  (string)  ($total_withdraw+$total_cash_in_hand))?  ($total_earning - ($total_withdraw+$total_cash_in_hand)):0;

                if ($disbursement_amount>$minimum_amount && $delivery_man->disbursement_method){
                    $res_d = [
                        'disbursement_id' => $disbursement->id,
                        'delivery_man_id' => $delivery_man->id,
                        'disbursement_amount' => $disbursement_amount,
                        'payment_method' => $delivery_man->disbursement_method->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $disbursement_details[] = $res_d;
                    $total_amount += $res_d['disbursement_amount'];
                    $delivery_man->wallet->pending_withdraw = $delivery_man->wallet->pending_withdraw + $disbursement_amount;
                    $delivery_man->wallet->save();
                }
            }

        }

        if ($total_amount > 0){
            $disbursement->total_amount = $total_amount;
            $disbursement->created_for = 'delivery_man';
            $disbursement->save();

            DisbursementDetails::insert($disbursement_details);
        }

        info("DM-----Disbursement");
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
