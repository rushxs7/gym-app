<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'balance',
        'type',
        'description'
    ];

    public function members()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
