<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

trait ActivationClass
{
    public function dmvf($request)
    {
		Session::put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
		Session::put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
		return base64_decode('c3RlcDM=');//s3
    }

    public function actch()
    {
		return response()->json([
			'active' => 1
		]);
    }

    public function is_local(): bool
    {
		return true;
    }
}

