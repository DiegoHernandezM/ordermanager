<?php
namespace App\Classes\Eks;

use App\Managers\RequestManager;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client as GuzzleClient;
use Log;

class EksApi
{

    /**
     * @return bool
     */
    public function testEks()
    {
        try {
            $token = Redis::get('system:eks:token') ?? '';
            $message = new RequestManager();
            $user = config('systems.eks.usernameAuth');
            $pass = config('systems.eks.passwordAuth');
            $params = ['token' => $token] ;

            $validToken = $message->send('basic', 'eks', '/auth/oauth/check_token', 'POST', '', $user, $pass, '', $params, []);

            if ($validToken->status_code != 200) {
                return $this->getTokenEks();
            } else {
                return true;
            }
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getTokenEks()
    {
        try {
            $user = config('systems.eks.usernameAuth');
            $pass = config('systems.eks.passwordAuth');
            $message = new RequestManager();
            $params = [
                'grant_type' => 'password',
                'username'  => config('systems.eks.user'),
                'password'  => config('systems.eks.pass')
            ];

            $getToken = $message->send('basic', 'eks', '/auth/oauth/token', 'POST', '', $user, $pass, '', $params);
            $response = json_decode($getToken->response);
            $token =  $response->access_token;

            Redis::set('system:eks:token', $token);

            return $token;
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
