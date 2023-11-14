<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflinePayments extends Model
{
    use HasFactory;
    protected $casts = [
        'order_id'=>'integer',
    ];
    protected $guarded = ['id'];
}
