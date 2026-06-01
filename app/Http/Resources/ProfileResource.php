<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'employee_number' => $this->employee_number,
            'name'            => $this->name,
            'phone'           => $this->phone,
            'address'         => $this->address,
            'position'        => $this->position,
            'profile_photo'   => $this->profile_photo,
            'department'      => $this->whenLoaded('department', function () {
                return [
                    'id'   => $this->department?->id,
                    'name' => $this->department?->name,
                ];
            }),
            'user' => [
                'id'    => $this->user?->id,
                'email' => $this->user?->email,
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}