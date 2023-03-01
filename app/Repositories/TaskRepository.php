<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository
{
    public function paginate($relation = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Task::with($relation)->latest()->paginate(10);
    }
    public function create($data)
    {
        return Task::create($data);
    }

    public function find($id,$relation = []): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        return Task::with($relation)->find($id);
    }
    public function update($data,$id)
    {
        $task = $this->find($id);

        return $task->update($data);
    }
    public function delete($id): int
    {
        return Task::destroy($id);
    }
}
