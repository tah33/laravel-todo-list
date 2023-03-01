<?php

namespace App\Repositories;

use App\Models\TodoList;

class TodoListRepository
{
    public function paginate($relation = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return TodoList::with($relation)->where('user_id',jwtUser()->id)->latest()->paginate(10);
    }
    public function create($data)
    {
        return TodoList::create($data);
    }

    public function find($id,$relation = []): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        return TodoList::with($relation)->find($id);
    }
    public function update($data,$id)
    {
        $todo = $this->find($id);

        return $todo->update($data);
    }
    public function delete($id): int
    {
        return TodoList::destroy($id);
    }
}
