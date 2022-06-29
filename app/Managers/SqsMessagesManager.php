<?php


namespace App\Managers;

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

class SqsMessagesManager
{
    protected $aConfig;

    public function __construct()
    {
        $this->aConfig = config('queue');
    }


    public function sendSQSMessage($connection, $data)
    {
        try {
            $resources = $this->getCredentials($connection);

            $client = new SqsClient([
                'version'     => 'latest',
                'region'      => 'us-west-2',
                'credentials' => $resources[0]
            ]);

            $params = [
                'DelaySeconds' => 10,
                'MessageBody' => $data,
                'QueueUrl' => $resources[1]
            ];

            $result = $client->sendMessage($params);

            return $result;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function receiveMessage($connection, $delete = false)
    {
        $resources = $this->getCredentials($connection);

        $client = new SqsClient([
            'region' => 'us-west-2',
            'version' => 'latest',
            'credentials' => $resources[0]
        ]);

        $result = $client->receiveMessage(array(
            'AttributeNames' => ['SentTimestamp'],
            'MaxNumberOfMessages' => 1,
            'MessageAttributeNames' => ['All'],
            'QueueUrl' => $resources[1],
            'WaitTimeSeconds' => 0,
        ));

        if (!empty($result->get('Messages'))) {

            $message = $result->get('Messages')[0];

            if ($delete === true) {
                $client->deleteMessage([
                    'QueueUrl' => $resources[1],
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle']
                ]);
            }

            return $message;

        } else {
            return false;
        }
    }

    private function getCredentials($connection)
    {
        $key = $this->aConfig['connections'][$connection]['key'];
        $secret = $this->aConfig['connections'][$connection]['secret'];
        $url = $this->aConfig['connections'][$connection]['prefix'].$this->aConfig['connections'][$connection]['queue'];

        $credentials = new \Aws\Credentials\Credentials($key, $secret);

        return [$credentials, $url];
    }


}