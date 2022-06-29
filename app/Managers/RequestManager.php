<?php

namespace App\Managers;

use Log;
use GuzzleHttp\Client as GuzzleClient;

class RequestManager
{
    protected $aConfig;
    protected $oHttpClient;

    /**
     * Obtiene el cliente HTTP por default (guzzle).
     *
     * @return Client
     */
    protected function getDefaultHttpClient()
    {
        return new GuzzleClient();
    }

    public function __construct(GuzzleClient $oGuzzleClient = null)
    {
        $this->oHttpClient = $oGuzzleClient ?? $this->getDefaultHttpClient();
        $this->aConfig = config('systems');
    }

    /**
     * Envía mensaje a una aplicación y retorna la respuesta
     *
     * @var string Aplicación a la cual mandar el mensaje
     * @var string $sUrl URL al cual enviar el mensaje
     * @var string $sMetodo Método
     * @var $sToken
     * @var string $sMensaje Mensaje a enviar
     * @var array $rJson Raw Json a envir
     *
     * @return \stdClass Objeto de respuesta
     */
    public function send($sTypeAuth, string $sAplicacion, string $sUrl, string $sMethod = 'GET', string $sToken = '', string $sUser = '', string $sPassword = '', string $sMessage = '', array $aMessage = [], array $rJson = [])
    {
        try {
            $aResponseResult = [];
            switch ($sTypeAuth) {
                case 'bearer':
                    $aGuzzleRequestOptions = [
                        'headers' => [
                            'Accept-Language' => 'en-us, es, en',
                            'Content-Type' => 'application/json',
                            'Content-Length' => strlen($sMessage),
                            'Cache-Control' => 'no-cache',
                            'Connection' => 'Keep-Alive',
                        ],
                        'body' => $sMessage,
                        'timeout' => 25,
                        'http_errors' => false,
                    ];
                    if (!empty($this->aConfig[$sAplicacion]['url'])) {
                        $aGuzzleRequestOptions['base_uri'] = $this->aConfig[$sAplicacion]['url'];
                    }
                    $aGuzzleRequestOptions['headers']['Authorization'] = 'Bearer ' . $sToken;
                    // Envía request
                    $oGuzzleResponse = $this->oHttpClient->request($sMethod, $this->aConfig[$sAplicacion]['url'].$sUrl, $aGuzzleRequestOptions);
                    // Formatea respuesta
                    $aResponseResult = [
                        'status' => 'success',
                        'status_message' => 'Successful request.',
                        'status_code' => $oGuzzleResponse->getStatusCode(),
                        'response' => $oGuzzleResponse->getBody()->getContents(),
                    ];

                    break;
                case 'basic':
                    $aGuzzleRequestOptions = [
                        'form_params' => $aMessage,
                        'body' => $sMessage,
                        'timeout' => 25,
                        'http_errors' => false,
                    ];
                    if ($rJson != null) {
                        $aGuzzleRequestOptions['json'] = $rJson;
                    }
                    if (!empty($this->aConfig[$sAplicacion]['url'])) {
                        $aGuzzleRequestOptions['base_uri'] = (env('APP_ENV') === 'PRODUCTION') ? $this->aConfig[$sAplicacion]['url'] : $this->aConfig[$sAplicacion]['url'];
                    }
                    if ($sUser == '' || $sUser == null) {
                        $sUser = $this->aConfig[$sAplicacion]['user'];
                    }
                    if ($sPassword == '' || $sPassword == null) {
                        $sPassword = $this->aConfig[$sAplicacion]['pass'];
                    }
                    $aGuzzleRequestOptions['headers']['Accept'] = 'application/json';
                    $aGuzzleRequestOptions['headers']['Authorization'] = 'Basic ' . base64_encode($sUser. ':' .$sPassword);

                    $url = (env('APP_ENV') === 'PRODUCTION') ? $this->aConfig[$sAplicacion]['url'].$sUrl : $this->aConfig[$sAplicacion]['url'].$sUrl;

                    // Envía request
                    $oGuzzleResponse = $this->oHttpClient->request($sMethod, $url, $aGuzzleRequestOptions);
                    // Formatea respuesta
                    $aResponseResult = [
                        'status' => 'success',
                        'status_message' => 'Successful request.',
                        'status_code' => $oGuzzleResponse->getStatusCode(),
                        'response' => $oGuzzleResponse->getBody()->getContents(),
                    ];
                    break;
                default:
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $sErrorMessage = $e->getMessage();
            Log::error($e);
            $aResponseResult = [
                'status' => 'fail',
                'status_message' => 'ClientException: ' . $sErrorMessage,
                'status_code' => '504',
                'response' => null,
            ];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $sErrorMessage = $e->getMessage();
            Log::error($e);
            if (strpos($sErrorMessage, 'cURL error 28') !== false) {
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => 'Gateway Timeout.',
                    'status_code' => '504',
                    'response' => null,
                ];
            } else {
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => 'Error de conexión, bad gateway: ' . $sErrorMessage,
                    'status_code' => '502',
                    'response' => null,
                ];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $sErrorMessage = $e->getMessage();
            Log::error($e);
            $aResponseResult = [
                'status' => 'fail',
                'status_message' => 'Error desconocido en request: ' . $sErrorMessage,
                'status_code' => '520',
                'response' => null,
            ];
        } catch (Exception $e) {
            $sErrorMessage = $e->getMessage();
            Log::error($e);
            $aResponseResult = [
                'status' => 'fail',
                'status_message' => 'Error desconocido: ' . $sErrorMessage,
                'status_code' => '520',
                'response' => null,
            ];
        }

        return (object) $aResponseResult;
    }
}
