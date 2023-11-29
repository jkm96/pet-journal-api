<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $transformedData = collect($this->resource)->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        });

        return $transformedData->all();
    }
}
