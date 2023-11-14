<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyItemDetails extends Model
{
    use HasFactory;

    protected $casts = [
        'common_condition_id' => 'integer',
        'is_basic' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function common_condition()
    {
        return $this->belongsTo(CommonCondition::class);
    }
}
