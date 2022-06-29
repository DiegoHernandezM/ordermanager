<?php
namespace App\Classes\Mail;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Sichikawa\LaravelSendgridDriver\SendGrid;

class MailSendGrill
{
    use SendGrid;
    public function buid($view, $from, $to, $nameTo, $nameFrom, $subject, $body)
    {
        try {
            Mail::send($view, ['body' => $body], function (Message $message) use ($body, $from, $to, $nameTo, $nameFrom, $subject) {
                $message
                    ->to($to, $nameTo)
                    ->from($from, $nameFrom)
                    ->subject($subject)
                    ->embedData([
                        'categories' => ['user_group1'],
                    ], 'sendgrid/x-smtpapi');
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    public function buidAttachments($view, $from, $to, $nameTo, $nameFrom, $subject, $attachment, $textBody)
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($from, $nameFrom);
        $email->setSubject($subject);
        $email->addTo($to, $nameTo);
        $email->addContent("text/plain", $textBody);

        $file_encoded = base64_encode($attachment);
        $email->addAttachment(
            $file_encoded,
            "application/text",
            "ola.json",
            "attachment"
        );

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: '.  $e->getMessage(). "\n";
        }
    }
}
