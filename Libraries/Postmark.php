<?php 
// app/Libraries/Postmark.php
namespace App\Libraries;

class Postmark
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = getenv('postmark_api_key'); // Ensure to set this in your .env file
    }

    public function sendEmail($from, $to, $replyto, $subject, $htmlBody, $textBody = '')
    {
        $url = 'https://api.postmarkapp.com/email';
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Postmark-Server-Token: ' . $this->apiKey,
        ];
        $data = [
            'From' => $from,
            'To' => $to,
            'ReplyTo'=>$replyto,
            'Subject' => $subject,
            'HtmlBody' => $htmlBody,
            'TextBody' => $textBody,
            'messageStream' => "broadcast",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode == 200;
    }
}

?>