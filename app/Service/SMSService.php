<?php


namespace App\Service;


use Illuminate\Support\Facades\Http;

class SMSService
{
    public function sendMessage($sms)
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post('https://new.smsgorod.ru/apiSms/create', [
                "apiKey" => env('SMS_API_KEY'),
                "sms"=> $sms,
            ]);

        return $response->json();
    }
}
