<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Helpers\StringHelpers;
use App\Managers\SqsMessagesManager;
use App\Wave;
use Carbon\Carbon;
use DateTime;
use App\Log as Logger;
use Storage;

class AdminBackupDBManager
{
    protected $mWave;
    protected $aConfig;
    protected $aStringHelper;
    protected $cSqsMessages;

    public function __construct()
    {
        $this->mWave = new Wave();
        $this->aConfig = config('filesystems');
        $this->aStringHelper = new StringHelpers();
        $this->cSqsMessages = new SqsMessagesManager();
    }

    /**
     * @return bool
     */
    public function runBackup()
    {
        try {
            $waves = $this->mWave->whereIn('status', [Wave::PICKING, Wave::PICKED, Wave::SORTING])->get();
            if (count($waves) === 0) {
                $filename = "backup-" . Carbon::now()->format('Y-m-d') . ".gz";
                $exec = "echo ". $this->aConfig['disks']['omsserver']['sudopassword'] ." | sudo -S";
                $command = $exec ." mysqldump -u " . $this->aConfig['disks']['omsserver']['userdatabase'] ." -p" . $this->aConfig['disks']['omsserver']['passdatabase'] . " " . $this->aConfig['disks']['omsserver']['database'] . "  | gzip > " . storage_path() . "/app/backup/" . $filename;
                $returnVar = null;
                $output  = null;

                exec($command, $output, $returnVar);

                if ($returnVar === 0) {
                    $filePath =  "/backups/" . $filename;
                    Storage::disk('s3')->put($filePath, file_get_contents(storage_path() . "/app/backup/" .$filename));
                    $dataSqs = $filename;
                    $this->cSqsMessages->sendSQSMessage('sqs-mirror', $dataSqs);
                    $this->deleteBackup();
                }

                return true;
            }

            $logData = [
                'message'       => "No se realiza el respaldo a causa de olas en PICKING o SORTING",
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'System',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);

            return true;
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'System',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
            return false;
        }
    }

    /**
     * @return bool
     */
    private function deleteBackup()
    {
        try {
            $files = Storage::disk('s3')->files('backups');
            $filesS3 = [];

            foreach ($files as $file) {
                $date = $this->aStringHelper->between('p-', '.', $file);
                $date = Carbon::parse($date)->format('Y-m-d');
                $filesS3[] = $date;
            }

            $nameS3 = 'backups/backup-';
            $getOldsS3 = $this->getOldsFiles($filesS3, $nameS3);

            if (count($getOldsS3) > 0) {
                foreach ($getOldsS3 as $fileS3) {
                    Storage::disk('s3')->delete($fileS3);
                }
            }

            $filteredArray = [];
            $nameLocal = 'backup-';
            $path = storage_path('app/backup/');

            foreach (glob($path."backup*") as $filename) {
                $date = $this->aStringHelper->between('p-', '.', $filename);
                $date = Carbon::parse($date)->format('Y-m-d');
                $filteredArray[] = $date;
            }

            $getOlsLocal = $this->getOldsFiles($filteredArray, $nameLocal);

            if (count($getOlsLocal) > 0) {
                foreach ($getOlsLocal as $name) {
                    unlink($path.$name);
                }
            }

            return true;
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'System',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
            return false;
        }
    }

    /**
     * @param $aDatesFiles
     * @param $name
     * @return array
     */
    private function getOldsFiles($aDatesFiles, $name)
    {

        $aOldFiles = [];
        $aFiles = $aDatesFiles;

        if (count($aDatesFiles) > 10) {
            do {
                $old = min($aFiles);
                $key = array_search($old, $aFiles);
                array_push($aOldFiles, $old);
                unset($aFiles[$key]);
            } while (count($aFiles) > 10);
        }

        if (count($aOldFiles) > 0) {
            foreach ($aOldFiles as $key => $aOldFile) {
                $aOldFiles[$key] = $name.''. $aOldFile .'.gz';
            }
        }

        return $aOldFiles;
    }

    public function restoreBackupDB()
    {
        try {
            $message = $this->cSqsMessages->receiveMessage('sqs-mirror', true);
            if ($message) {

                $fileName = $message["Body"];
                $filePath =  "/backups/" . $fileName;

                $s3_file = Storage::disk('s3')->get($filePath);
                $s3 = Storage::disk('public');
                $s3->put($filePath, $s3_file);

                     if (!empty($s3_file)) {
                           $s3 = Storage::disk('public');
                           $s3->put($filePath, $s3_file);

                           $exec = "echo ". $this->aConfig['disks']['omsserver']['sudopassword'] ." | sudo -S";
                           $output  = null;
                           $returnVar = null;

                           $command = $exec." gunzip < ". storage_path() . "/app/public/backups/" . $fileName. " | mysql -u " . $this->aConfig['disks']['omsserver']['userdatabase']. " -p".$this->aConfig['disks']['omsserver']['passdatabase']." ".$this->aConfig['disks']['omsserver']['database'];
                           exec($command, $output, $returnVar);
                           unlink(storage_path()."/app/public".$filePath);
                     }
            }

        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'System',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
            return false;
        }
    }
}
