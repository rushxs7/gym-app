<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'stock',
        'price',
        'thumbnail_original',
        'thumbnail_large',
        'thumbnail_small',
        'stockable',
        'taxable',
    ];

    public function payments()
    {
        return $this->belongsTo(Payment::class, 'product_id', 'id');
    }
}
