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
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $transformedData = collect($this->resource)->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        });

        return array_merge($transformedData->all(), [
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->pluck('value')->toArray();
            }),
        ]);
    }
}
