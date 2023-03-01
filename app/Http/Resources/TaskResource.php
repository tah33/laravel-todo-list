<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => (int)$this->id,
            'name'          => $this->name,
            'is_completed'  => (bool)$this->is_completed
        ];
    }
}
