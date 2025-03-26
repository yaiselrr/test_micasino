<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function logs():HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }
    
    public function currency():BelongsTo
    {
        return $this->belongsTo(Currency::class);
    } 
}
