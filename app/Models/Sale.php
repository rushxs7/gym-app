<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'subtotal',
        'discount',
        'total'
    ];

    public function members()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function sale_items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'id');
    }
}
