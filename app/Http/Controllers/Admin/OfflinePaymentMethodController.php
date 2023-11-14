<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use Brian2694\Toastr\Facades\Toastr;

class OfflinePaymentMethodController extends Controller
{

    protected OfflinePaymentMethod $OfflinePaymentMethod;

    public function __construct(OfflinePaymentMethod $OfflinePaymentMethod)
    {
        $this->OfflinePaymentMethod = $OfflinePaymentMethod;
    }

    public function index(Request $request)
    {
        if (request()->has('status') && (request('status') == 'active' || request('status') == 'inactive'))
        {
            $methods = OfflinePaymentMethod::when(request('status') == 'active', function($query){
                return $query->where('status', 1);
            })->when(request('status') == 'inactive', function($query){
                return $query->where('status', 0);
            })->paginate(10);
        } else if(request()->has('search')) {
            $methods = OfflinePaymentMethod::where(function ($query) {
                $query->orWhere('method_name', 'like', "%".request('search')."%");
            })->paginate(10);
        }else{
            $methods = OfflinePaymentMethod::paginate(10);
        }

        return view('admin-views.business-settings.offline-payment.index', compact('methods'));
    }


    public function create()
    {
        return view('admin-views.business-settings.offline-payment.new');
    }


    public function store(Request $request)
    {
        $request->validate([
            'method_name' => 'required|unique:offline_payment_methods',
            'input_name' => 'required|array',
            'input_data' => 'required|array',
            'customer_input' => 'required|array',
        ],[
            'input_name.required' => translate('Payment_information_details_required'),
            'input_data.required' => translate('Payment_information_details_required'),
            'customer_input.required' => translate('Customer_input_information_required')
        ]);

        $method_fields = [];
        if($request->has('input_name'))
        {
            foreach ($request->input_name as $key => $field_name) {
                $method_fields[] = [
                    'input_name' => strtolower(str_replace("'", '', preg_replace('/[^a-zA-Z0-9\']/', '_', $request->input_name[$key]))),
                    'input_data' => $request->input_data[$key],
                ];
            }
        }

        $method_informations = [];
        if($request->has('customer_input'))
        {
            foreach ($request->customer_input as $key => $field_name) {
                $method_informations[] = [
                    'customer_input' => strtolower(str_replace("'", '', preg_replace('/[^a-zA-Z0-9\']/', '_', $request->customer_input[$key]))),
                    'customer_placeholder' => $request->customer_placeholder[$key],
                    'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
                ];
            }
        }

        $this->OfflinePaymentMethod->insert([
            'method_name' => $request->method_name,
            'method_fields' => json_encode($method_fields),
            'method_informations' => json_encode($method_informations),
            'status'=>1,
            'created_at' => Carbon::now(),
        ]);

        Toastr::success(translate('offline_payment_method_added_successfully'));
        return to_route('admin.business-settings.offline');
    }


    public function edit($id)
    {
        $data = $this->OfflinePaymentMethod->where('id', $id)->first();

        if($data)
        {
            return view('admin-views.business-settings.offline-payment.edit', compact('data'));
        }else{
            Toastr::error(translate('offline_payment_method_not_found'));
            return to_route('admin.business-settings.offline');
        }
    }


    public function update(Request $request)
    {
        $request->validate([
            'method_name' => 'required|unique:offline_payment_methods,method_name,'.$request->id,
            'input_name' => 'required|array',
            'input_data' => 'required|array',
            'customer_input' => 'required|array',
        ],[
            'input_name.required' => translate('Payment_information_details_required'),
            'input_data.required' => translate('Payment_information_details_required'),
            'customer_input.required' => translate('Customer_input_information_required')
        ]);

        $method_fields = [];
        if($request->has('input_name'))
        {
            foreach ($request->input_name as $key => $field_name) {
                $method_fields[] = [
                    'input_name' => strtolower(str_replace(' ', "_", $request->input_name[$key])),
                    'input_data' => $request->input_data[$key],
                ];
            }
        }

        $method_informations = [];
        if($request->has('customer_input'))
        {
            foreach ($request->customer_input as $key => $field_name) {
                $method_informations[] = [
                    'customer_input' => strtolower(str_replace(' ', "_", $request->customer_input[$key])),
                    'customer_placeholder' => $request->customer_placeholder[$key],
                    'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
                ];
            }
        }

        $this->OfflinePaymentMethod->where('id', $request->id)->update([
            'method_name' => $request->method_name,
            'method_fields' => json_encode($method_fields),
            'method_informations' => json_encode($method_informations),
            'created_at' => Carbon::now(),
        ]);

        Toastr::success(translate('offline_payment_method_update_successfully'));
        return to_route('admin.business-settings.offline');
    }


    public function delete(Request $request)
    {
        $this->OfflinePaymentMethod->where('id', $request->id)->delete();
        Toastr::success(translate('offline_payment_method_delete_successfully'));
        return to_route('admin.business-settings.offline');
    }

    public function status($id)
    {
        $data = $this->OfflinePaymentMethod->where('id', $id)->first();
        $message = '';

        if (isset($data)) {
            $data->update([
                'status' => $data->status == 1 ? 0:1,
            ]);
            $message = translate("status_updated_successfully");
        } else {
            $message = translate("status_update_failed");
        }

        Toastr::success(translate($message));
        return to_route('admin.business-settings.offline');
    }
}
