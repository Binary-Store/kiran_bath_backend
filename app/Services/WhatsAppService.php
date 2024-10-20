<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $token;
    protected $phoneId;
    protected $apiUrl;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneId = config('services.whatsapp.phone_id');
        $this->apiUrl = "https://graph.facebook.com/v20.0/{$this->phoneId}/messages";
    }

    public function sendOtp($phone, $otp)
    {

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
              'name' => 'otp',
              'language' => [ 'code' => 'en' ],
              'components' => [
                [
                  'type' => 'body',
                  'parameters' => [
                    [ 'type' => 'text', 'text' => $otp ],
                  ],
                ],
                [
                  'type' => 'button',
                  'sub_type' => 'url',
                  'index' => '0',
                  'parameters' => [
                    [ 'type' => 'text', 'text' => $otp ],
                  ],
                ],
              ],
            ],
        ];

        $response = Http::withToken($this->token)->post($this->apiUrl, $data);

        if ($response->failed()) {
            \Log::error('Failed to send OTP: ' . $response->body());
            return false;
        }

        return true;
    }
}
