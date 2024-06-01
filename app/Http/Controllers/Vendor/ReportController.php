<?php

namespace App\Http\Controllers\Vendor;

use App\Exports\DisbursementVendorReportExport;
use App\Models\DisbursementDetails;
use App\Models\Expense;
use App\Models\WithdrawalMethod;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\ExpenseReportExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;

class ReportController extends Controller
{

    public function set_date(Request $request)
    {
        session()->put('from_date', date('Y-m-d', strtotime($request['from'])));
        session()->put('to_date', date('Y-m-d', strtotime($request['to'])));
        return back();
    }

    public function expense_report(Request $request)
    {
        $from =  null;
        $to = null;
        $filter = $request->query('filter', 'all_time');
        if($filter == 'custom'){
            $from = $request->from ?? null;
            $to = $request->to ?? null;
        }
        $key = explode(' ', $request['search']);
        $expense = Expense::with('order')->where('created_by','vendor')->where('store_id',Helpers::get_store_id())
        ->when(isset($from) && isset($to) && $from != null && $to != null && $filter == 'custom', function ($query) use ($from, $to) {
            return $query->whereBetween('created_at', [$from . " 00:00:00", $to . " 23:59:59"]);
        })
        ->when(isset($filter) && $filter == 'this_year', function ($query) {
            return $query->whereYear('created_at', now()->format('Y'));
        })
        ->when(isset($filter) && $filter == 'this_month', function ($query) {
            return $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
        })
        ->when(isset($filter) && $filter == 'this_month', function ($query) {
            return $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
        })
        ->when(isset($filter) && $filter == 'previous_year', function ($query) {
            return $query->whereYear('created_at', date('Y') - 1);
        })
        ->when(isset($filter) && $filter == 'this_week', function ($query) {
            return $query->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
        })
        ->when( isset($key), function($query) use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('type', 'like', "%{$value}%")->orWhere('order_id', 'like', "%{$value}%");
                }
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(config('default_pagination'))->withQueryString();
        return view('vendor-views.report.expense-report', compact('expense','from','to','filter'));
    }




    public function expense_export(Request $request)
    {
        $from =  null;
        $to = null;
        $filter = $request->query('filter', 'all_time');
        if($filter == 'custom'){
            $from = $request->from ?? null;
            $to = $request->to ?? null;
        }
        $key = explode(' ', $request['search']);
        $expense = Expense::with('order')->where('created_by','vendor')->where('store_id',Helpers::get_store_id())
        ->when(isset($from) && isset($to) && $from != null && $to != null && $filter == 'custom', function ($query) use ($from, $to) {
            return $query->whereBetween('created_at', [$from . " 00:00:00", $to . " 23:59:59"]);
        })
        ->when(isset($filter) && $filter == 'this_year', function ($query) {
            return $query->whereYear('created_at', now()->format('Y'));
        })
        ->when(isset($filter) && $filter == 'this_month', function ($query) {
            return $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
        })
        ->when(isset($filter) && $filter == 'this_month', function ($query) {
            return $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
        })
        ->when(isset($filter) && $filter == 'previous_year', function ($query) {
            return $query->whereYear('created_at', date('Y') - 1);
        })
        ->when(isset($filter) && $filter == 'this_week', function ($query) {
            return $query->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
        })
        ->when( isset($key), function($query) use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('type', 'like', "%{$value}%")->orWhere('order_id', 'like', "%{$value}%");
                }
            });
        })
        ->orderBy('created_at', 'desc')
        ->get();


        $data = [
            'expenses'=>$expense,
            'search'=>$request->search??null,
            'from'=>(($filter == 'custom') && $from)?$from:null,
            'to'=>(($filter == 'custom') && $to)?$to:null,
            'zone'=>Helpers::get_zones_name(Helpers::get_store_data()->zone_id),
            'store'=>Helpers::get_stores_name(Helpers::get_store_id()),
            // 'customer'=>is_numeric($customer_id)?Helpers::get_customer_name($customer_id):null,
            // 'module'=>request('module_id')?Helpers::get_module_name(request('module_id')):null,
            'filter'=>$filter,
            'type'=> 'store',
        ];

        if ($request->type == 'excel') {
            return Excel::download(new ExpenseReportExport($data), 'ExpenseReport.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new ExpenseReportExport($data), 'ExpenseReport.csv');
        }

        // if ($request->type == 'excel') {
        //     return (new FastExcel(Helpers::export_expense_wise_report($expense)))->download('ExpenseReport.xlsx');
        // } elseif ($request->type == 'csv') {
        //     return (new FastExcel(Helpers::export_expense_wise_report($expense)))->download('ExpenseReport.csv');
        // }
    }

    public function disbursement_report(Request $request)
    {
        $from =  null;
        $to = null;
        $filter = $request->query('filter', 'all_time');
        if($filter == 'custom'){
            $from = $request->from ?? null;
            $to = $request->to ?? null;
        }
        $key = explode(' ', $request['search']);
        $store_id = Helpers::get_store_id();
        $withdrawal_methods = WithdrawalMethod::ofStatus(1)->get();
        $status = $request->query('status', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');

        $dis = DisbursementDetails::where('store_id',$store_id)
            ->when((isset($payment_method_id) && ($payment_method_id != 'all')), function ($query) use ($payment_method_id) {
                return $query->whereHas('withdraw_method',function($q)use ($payment_method_id){
                    $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->when((isset($status) && ($status != 'all')), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when(isset($filter) , function ($query) use ($filter,$from, $to) {
                return $query->applyDateFilter($filter, $from, $to);
            })
            ->when(isset($key), function ($q) use ($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('disbursement_id', 'like', "%{$value}%")
                            ->orWhere('status', 'like', "%{$value}%");
                    }
                });
            })
            ->latest();

        $total_disbursements= $dis->get();

        $disbursements= $dis->paginate(config('default_pagination'))->withQueryString();

        $pending =(float) $total_disbursements->where('status','pending')->sum('disbursement_amount');
        $completed =(float) $total_disbursements->where('status','completed')->sum('disbursement_amount');
        $canceled =(float) $total_disbursements->where('status','canceled')->sum('disbursement_amount');

        return view('vendor-views.report.disbursement-report', compact('disbursements','pending', 'completed','canceled','filter','from','to','withdrawal_methods','status','payment_method_id'));

    }

    public function disbursement_report_export(Request $request,$type)
    {
        $from = null;
        $to = null;
        $filter = $request->query('filter', 'all_time');
        if($filter == 'custom'){
            $from = $request->from ?? null;
            $to = $request->to ?? null;
        }
        $key = explode(' ', $request['search']);
        $store_id = Helpers::get_store_id();
        $withdrawal_methods = WithdrawalMethod::ofStatus(1)->get();
        $status = $request->query('status', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');

        $disbursements = DisbursementDetails::where('store_id',$store_id)
            ->when((isset($payment_method_id) && ($payment_method_id != 'all')), function ($query) use ($payment_method_id) {
                return $query->whereHas('withdraw_method',function($q)use ($payment_method_id){
                    $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->when((isset($status) && ($status != 'all')), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when(isset($filter) , function ($query) use ($filter,$from, $to) {
                return $query->applyDateFilter($filter, $from, $to);
            })
            ->when(isset($key), function ($q) use ($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('disbursement_id', 'like', "%{$value}%")
                            ->orWhere('status', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->get();

        $data=[
            'disbursements' =>$disbursements,
            'search'=>$request->search??null,
            'status'=>$status,
            'filter'=>$filter,
            'from'=>(($filter == 'custom') && $from)?$from:null,
            'to'=>(($filter == 'custom') && $to)?$to:null,
            'pending' =>(float) $disbursements->where('status','pending')->sum('disbursement_amount'),
            'completed' =>(float) $disbursements->where('status','completed')->sum('disbursement_amount'),
            'canceled' =>(float) $disbursements->where('status','canceled')->sum('disbursement_amount'),
        ];
        if($type == 'csv'){
            return Excel::download(new DisbursementVendorReportExport($data), 'DisbursementReport.csv');
        }
        return Excel::download(new DisbursementVendorReportExport($data), 'DisbursementReport.xlsx');

    }

}
