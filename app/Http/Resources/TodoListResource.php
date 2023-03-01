<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TodoListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function () {
                return [
                    [
                        'id'    => (int)$this->id,
                        'name'  => $this->name,
                        'tasks' => TaskResource::collection($this->tasks),
                    ]
                ];
            }),
            'total_rows' => $this->total(),
            'count' => $this->count(),
            'page_size' => $this->perPage(),
            'current_page' => $this->currentPage(),
            'total_pages' => $this->lastPage(),
            'last_page' => $this->lastPage(),
            'next_page_url' => $this->nextPageUrl(),
            'has_more_data' => $this->hasMorePages(),

        ];
    }
}
