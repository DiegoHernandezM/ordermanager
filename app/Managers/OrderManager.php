<?php

namespace App\Managers;

use Carbon\Carbon;
use Log;
use Validator;

class OrderManager
{
  public static function hasAccess()
  {
    return true;
  }
}