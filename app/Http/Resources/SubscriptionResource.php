<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            "id" => $this->id,
            "purchase_token" => $this->purchase_token,
            "product_id" => $this->product_id,
            "purchase_time" => $this->purchase_time,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "user" => new UserResource($this->whenLoaded('user'))
        ];
    }
}
