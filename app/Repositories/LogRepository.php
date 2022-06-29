<?php

namespace App\Repositories;

use App\Log as Logger;
use App\Http\Controllers\ApiResponses;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class LogRepository extends BaseRepository
{
    protected $model = 'App\Log';
  /**
   * Crea una linea.
   *
   * @param  Array  $lineData
   * @return \Illuminate\Http\Response
   */
    public function makeLog(Array $logData)
    {
        $log = new Logger;
        $log->loggable_type = $logData['model'];
        $log->loggable_id = $logData['resourceId'];
        $log->message = $logData['message'];
        $log->user_id = $logData['user_id'];
        $log->save();
    }
}
