<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use JsonSerializable;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'profile_url' => $this->profile_url,
            'status' => $this->status,
            'is_email_verified' => $this->is_email_verified,
            'created_at' => $this->created_at,
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->pluck('value')->toArray();
            }),
        ];
    }
}
