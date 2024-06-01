<?php

namespace App\Http\Controllers\Vendor;



use App\Models\Admin;
use App\Library\Payer;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Models\Store;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\WithdrawRequest;
use App\Models\StoreWallet;
use App\Models\WithdrawalMethod;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\DisbursementDetails;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Payment as PaymentInfo;
use Illuminate\Support\Facades\Validator;
use App\Exports\DisbursementHistoryExport;

class WalletController extends Controller
{
    public function index()
    {
        $data =  data_get($this->getWithdrawMethods() , 'data' , [] );
        $withdrawal_methods =  data_get($this->getWithdrawMethods() , 'withdrawal_methods' , [] );
        $withdraw_req = WithdrawRequest::with(['vendor','method'])->where('vendor_id', Helpers::get_vendor_id())->latest()->paginate(config('default_pagination'));
        return view('vendor-views.wallet.index', compact('withdraw_req','withdrawal_methods','data'));
    }
    public function w_request(Request $request)
    {
        $method = WithdrawalMethod::find($request['withdraw_method']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }

        $w = StoreWallet::where('vendor_id', Helpers::get_vendor_id())->first();
        if ((string) $w->balance >=  (string)$request['amount'] && (string)$request['amount'] > .01) {
            $data = [
                'vendor_id' => Helpers::get_vendor_id(),
                'amount' => $request['amount'],
                'transaction_note' => null,
                'withdrawal_method_id' => $request['withdraw_method'],
                'withdrawal_method_fields' => json_encode($method_data),
                'approved' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
            DB::table('withdraw_requests')->insert($data);
            StoreWallet::where('vendor_id', Helpers::get_vendor_id())->increment('pending_withdraw', $request['amount']);
            try
            {
                $admin= Admin::where('role_id', 1)->first();
                $mail_status = Helpers::get_mail_status('withdraw_request_mail_status_admin');
                if(config('mail.status') && $mail_status == '1') {
                    $wallet_transaction = WithdrawRequest::where('vendor_id',Helpers::get_vendor_id())->latest()->first();
                    Mail::to($admin['email'])->send(new \App\Mail\WithdrawRequestMail('pending',$wallet_transaction));
                }
            }
            catch(\Exception $e)
            {
                info($e->getMessage());
            }
            Toastr::success('Withdraw request has been sent.');
            return redirect()->back();
        }

        Toastr::error('invalid request.!');
        return redirect()->back();
    }

    public function close_request($id)
    {
        $wr = WithdrawRequest::find($id);
        if ($wr->approved == 0) {
            StoreWallet::where('vendor_id', Helpers::get_vendor_id())->decrement('pending_withdraw', $wr['amount']);
        }
        $wr->delete();
        Toastr::success('request closed!');
        return back();
    }


    public function method_list(Request $request)
    {
        $method = WithdrawalMethod::ofStatus(1)->where('id', $request->method_id)->first();

        return response()->json(['content'=>$method], 200);
    }


    public function make_wallet_adjustment(){
        $wallet = StoreWallet::firstOrNew(
            ['vendor_id' =>Helpers::get_vendor_id()]
        );

        $wallet_earning =  $wallet->total_earning -($wallet->total_withdrawn + $wallet->pending_withdraw);
        $adj_amount =  $wallet->collected_cash - $wallet_earning;

        if($wallet->collected_cash == 0 || $wallet_earning == 0 || ($wallet_earning  == $wallet->balance ) ){
            Toastr::info(translate('Already_Adjusted'));
            return back();
        }

        if($adj_amount > 0 ){
            $wallet->total_withdrawn =  $wallet->total_withdrawn + $wallet_earning ;
            $wallet->collected_cash =   $wallet->collected_cash - $wallet_earning ;

            $data = [
                'vendor_id' => Helpers::get_vendor_id(),
                'amount' => $wallet_earning,
                'transaction_note' => "Store_wallet_adjustment_partial",
                'withdrawal_method_id' => null,
                'withdrawal_method_fields' => null,
                'approved' => 1,
                'type' => 'adjustment',
                'created_at' => now(),
                'updated_at' => now()
            ];

        } else{

            $data = [
                'vendor_id' => Helpers::get_vendor_id(),
                'amount' => $wallet->collected_cash ,
                'transaction_note' => "Store_wallet_adjustment_full",
                'withdrawal_method_id' => null,
                'withdrawal_method_fields' => null,
                'approved' => 1,
                'type' => 'adjustment',
                'created_at' => now(),
                'updated_at' => now()
            ];
            $wallet->total_withdrawn =  $wallet->total_withdrawn + $wallet->collected_cash ;
            $wallet->collected_cash =   0;

        }

        $wallet->save();
        DB::table('withdraw_requests')->insert($data);
        Toastr::success(translate('store_wallet_adjustment_successfull'));
        return back();
    }

    Public function make_payment(Request $request){
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'payment_gateway' => 'required',
            'amount' => 'required|min:0.001',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $store =Store::findOrfail($request->store_id);

        $payer = new Payer(
            $store->name ,
            $store->email,
            $store->phone,
            ''
        );
        $additional_data = [
            'business_name' => BusinessSetting::where(['key'=>'business_name'])->first()?->value,
            'business_logo' => asset('storage/app/public/business') . '/' .BusinessSetting::where(['key' => 'logo'])->first()?->value
        ];
        $payment_info = new PaymentInfo(
            success_hook: 'collect_cash_success',
            failure_hook: 'collect_cash_fail',
            currency_code: Helpers::currency_code(),
            payment_method: $request->payment_gateway,
            payment_platform: 'web',
            payer_id: $store->vendor->id,
            receiver_id: '100',
            additional_data:  $additional_data,
            payment_amount: $request->amount ,
            external_redirect_link:  route('vendor.wallet.index'),
            attribute: 'store_collect_cash_payments',
            attribute_id: $store->vendor->id,
        );

        $receiver_info = new Receiver('Admin','example.png');
        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);

        return redirect($redirect_link);

    }

    public function wallet_payment_list(Request $request){

        $data =  data_get($this->getWithdrawMethods() , 'data' , [] );
        $withdrawal_methods =  data_get($this->getWithdrawMethods() , 'withdrawal_methods' , [] );

        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $account_transaction = AccountTransaction::
        when(isset($key), function ($query) use ($key) {
            return $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('ref', 'like', "%{$value}%");
                }
            });
        })
            ->where('type', 'collected')
            ->where('created_by' , 'store')
            ->where('from_id', Helpers::get_vendor_id())
            ->where('from_type', 'store')
            ->latest()->paginate(config('default_pagination'));
        return view('vendor-views.wallet.payment_list', compact('account_transaction','withdrawal_methods','data'));
    }
    public function getDisbursementList(Request $request){

        $data =  data_get($this->getWithdrawMethods() , 'data' , [] );
        $withdrawal_methods =  data_get($this->getWithdrawMethods() , 'withdrawal_methods' , [] );

        $key = isset($request['search']) ? explode(' ', $request['search']) : [];

        $disbursements=DisbursementDetails::with('store','withdraw_method')
            ->where('store_id', Helpers::get_store_id())
            ->when(isset($key), function ($q) use ($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('disbursement_id', 'like', "%{$value}%")
                            ->orWhere('status', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->paginate(config('default_pagination'));
        return view('vendor-views.wallet.disbursement', compact('disbursements','withdrawal_methods','data'));
    }
    private function getWithdrawMethods(){
        $withdrawal_methods = WithdrawalMethod::ofStatus(1)->get();

        $published_status =0;
        $payment_published_status = config('get_payment_publish_status');
        if (isset($payment_published_status[0]['is_published'])) {
            $published_status = $payment_published_status[0]['is_published'];
        }

        $methods = DB::table('addon_settings')->where('is_active',1)->where('settings_type', 'payment_config')

            ->when($published_status == 0, function($q){
                $q->whereIn('key_name', ['ssl_commerz','paypal','stripe','razor_pay','senang_pay','paytabs','paystack','paymob_accept','paytm','flutterwave','liqpay','bkash','mercadopago']);
            })
            ->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additional_data?->gateway_title,
                    'gateway_image' => $additional_data?->gateway_image
                ];
            }
        }

        $result = [
            'data' => $data ,
            'withdrawal_methods' => $withdrawal_methods ,
        ];

        return  $result;
    }
    public function getDisbursementExport(Request $request)
    {

        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $disbursements = DisbursementDetails::with('store', 'withdraw_method')
            ->where('store_id', Helpers::get_store_id())
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('disbursement_id', 'like', "%{$value}%")
                            ->orWhere('status', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->get();

        $data = [
            'disbursements' => $disbursements,
            'search' => $request->search ?? null,
            'store' => Helpers::get_store_data()->name,
            'type' => 'store',
        ];

        if ($request->type == 'excel') {
            return Excel::download(new DisbursementHistoryExport($data), 'Disbursementlist.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new DisbursementHistoryExport($data), 'Disbursementlist.csv');
        }
    }

}
