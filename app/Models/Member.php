<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfid_code',
        'name',
        'address',
        'email',
        'phone',
        'gender',
        'active',
        'end_of_membership'
    ];

    public function visits()
    {
        return $this->hasMany(Visit::class, 'member_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'member_id', 'id');
    }
}
