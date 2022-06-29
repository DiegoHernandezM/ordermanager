<?php

namespace App\Repositories;

use App\Log as Logger;
use Illuminate\Support\Facades\Auth;

class BaseRepository
{
    public function select(array $columns = ['*'])
    {
        return (new $this->model)->select($columns);
    }

    public function get($columns = ['*'])
    {
        return (new $this->model)->limit(10)->get($columns);
    }

    public function getOrdered($field, $order = 'ASC', $columns = ['*'])
    {
        return (new $this->model)->orderBy($field, $order)->get($columns);
    }

    public function all($columns = ['*'])
    {
        return (new $this->model)->all($columns);
    }

    public function paginate($pages = 20)
    {
        return (new $this->model)->paginate($pages);
    }

    public function count()
    {
        return (new $this->model)->count();
    }

    public function find($id)
    {
        return (new $this->model)->find($id);
    }

    public function findOrFail($id)
    {
        return (new $this->model)->findOrFail($id);
    }

    public function create(array $data)
    {
        return (new $this->model)->create($data);
    }

    public function delete($id)
    {
        $this->find($id)->delete();
        return ['message' => 'Recurso eliminado.'];
    }

    public function update($model, array $data = [])
    {
        $model->update($data);
        return $model;
    }

    public function save($model)
    {
        return $model->save();
    }

    public function getRules()
    {
        $model = new $this->model;

        return $model::$rules;
    }

    public function getUpdateRules()
    {
        $model = new $this->model;

        return $model::$updateRules;
    }

    public function log($message, $resourceId)
    {
        $logData = [
            'message'       => $message,
            'loggable_type' => $this->model,
            'loggable_id'   => $resourceId,
            'user_id'       => Auth::id(),
        ];
        $log = new Logger;
        $log->create($logData);
    }
}
