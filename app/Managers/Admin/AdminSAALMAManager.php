<?php

namespace App\Managers\Admin;

use App\Managers\Admin\AdminOrderManager;
use App\Managers\MessagesManager;
use App\Managers\RequestManager;
use App\Style;
use Log;

class AdminSAALMAManager
{

    public function __construct()
    {
        $this->message = new RequestManager();
        $this->orderManager = new AdminOrderManager();
    }

    /**
     * @param  Array  $inventoryRequest
     * @return Array
     */
    public function getInventory(array $inventoryRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma', '/inventario/sku', 'POST', '', '', '', '', [], $inventoryRequest);
                if ($response->status_code == 200) {
                    try {
                        return $actualStock = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
                return $response;
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    public function getInventoryDev(array $inventoryRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma-dev', '/inventario/sku', 'POST', '', '', '', '', [], $inventoryRequest);
                if ($response->status_code == 200) {
                    try {
                        return $actualStock = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
                return $response;
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    public function getDevolutionTransfers($queryRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                //var_dump($queryRequest);
                $response = $this->message->send('basic', 'saalma-dev', '/inventario/devoluciones?' . $queryRequest, 'GET', '', '', '', '', [], []);
                if ($response->status_code == 200) {
                    try {
                        return $actualStock = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
                return $response;
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    public function registerDevolutionWave(array $queryRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma-dev', '/ola/devolucion', 'POST', '', '', '', '', [], $queryRequest);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
                return $response;
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param  Array $waveId
     * @return Array
     */
    public function registerWaveDev(array $registerWaveRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma-dev', '/ola', 'POST', '', '', '', '', [], $registerWaveRequest);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param  Array $waveId
     * @return Array
     */
    public function registerWave(array $registerWaveRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma', '/ola', 'POST', '', '', '', '', [], $registerWaveRequest);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param  Array $waveId
     * @return Array
     */
    public function orderFinished(array $orderFinishedRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma', '/ola/destino/fin', 'POST', '', '', '', '', [], $orderFinishedRequest);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param  Array $waveId
     * @return Array
     */
    public function allocationFinished(array $allocationFinishedRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma', '/allocation/fin', 'POST', '', '', '', '', [], $allocationFinishedRequest);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param  Array $waveId
     * @return Array
     */
    public function waveFinished(array $waveFinishedRequest)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma', '/caja/fin', 'POST', '', '', '', '', [], $waveFinishedRequest);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param  Array $cartons
     * @return Array
     */
    public function registerCartons(array $cartons)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma', '/caja', 'POST', '', '', '', '', [], $cartons);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param  Array $cartons
     * @return Array
     */
    public function registerCartonsDev(array $cartons)
    {
        try {
            $validToken = true;
            if ($validToken) {
                $response = $this->message->send('basic', 'saalma-dev', '/caja', 'POST', '', '', '', '', [], $cartons);
                if ($response->status_code == 200) {
                    try {
                        return $result = json_decode($response->response, true);
                    } catch (Exception $e) {
                        Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }
}
