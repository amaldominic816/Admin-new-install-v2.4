<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ReportFilter;

class DisbursementDetails extends Model
{
    use HasFactory, ReportFilter;

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class);
    }
    public function disbursement()
    {
        return $this->belongsTo(Disbursement::class);
    }

    public function withdraw_method()
    {
        return $this->belongsTo(DisbursementWithdrawalMethod::class,'payment_method','id');
    }
}
