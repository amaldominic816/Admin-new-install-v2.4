<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreConfig extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'store_id' => 'integer',
        'is_recommended' => 'boolean',
        'is_recommended_deleted' => 'boolean',
    ];

    public function Store()
    {
        return $this->belongsTo(Store::class);
    }
}
