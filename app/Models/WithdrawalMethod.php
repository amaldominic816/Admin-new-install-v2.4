<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalMethod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'method_fields' => 'array',
        'is_active' => 'integer',
        'is_default' => 'integer',
    ];

    protected function scopeOfStatus($query, $status)
    {
        $query->where('is_active', $status);
    }
}
