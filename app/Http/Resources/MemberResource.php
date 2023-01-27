<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $diffInDays = Carbon::today()->diffInDays(Carbon::parse($this->end_of_membership), false);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'end_of_membership' => $this->end_of_membership,
            'ends_in_days' => $diffInDays,
            'visits' => $this->visits,
            'payments' => $this->payments
        ];
    }
}
