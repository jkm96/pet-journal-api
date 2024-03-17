<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $creationDate = Carbon::parse($this->created_at)->startOfDay();
        $expirationDate = $creationDate->addDays(config('auth.email_verification_grace_period'));
        $daysLeft = Carbon::now()->diffInDays($expirationDate);

        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'profile_url' => $this->profile_url,
            'profile_cover_url' => $this->profile_cover_url,
            'status' => $this->status,
            'is_email_verified' => $this->is_email_verified,
            'email_verified_at' => $this->email_verified_at,
            'is_subscribed' => $this->is_subscribed,
            'is_active' => $this->is_active,
            'is_admin' => false,
            'created_at' => $this->created_at,
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->pluck('value')->toArray();
            }),
            'grace_period_count' => $daysLeft,
            'is_grace_period_expired' => $daysLeft <= 0,
        ];
    }
}
