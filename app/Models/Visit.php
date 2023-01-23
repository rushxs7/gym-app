<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'time_of_arrival',
        'time_of_departure',
    ];

    public $timestamps = false;

    public function members()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
