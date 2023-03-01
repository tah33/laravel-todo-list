<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TodoListResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    [
                        'id'    => (int)$data->id,
                        'name'  => $data->name,
                        'tasks' => TaskResource::collection($data->tasks),
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
