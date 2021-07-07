<?php

namespace App\Models;

use App\Service\SMSService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'password',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'two_factor_expires_at',
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'remember_token',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor',
    ];

    public function chats()
    {
        return $this->belongsTo(Chat::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function twoFactorCode()
    {
        $code=rand(100000, 999999);
        $this->timestamps = false;
        $this->two_factor_code = bcrypt($code);
        $this->two_factor_expires_at = now()->addMinutes(10);
        $this->save();
        Log::info($code);
        $this->sendTwoFactorCodeBySms($code);
    }

    /**
     * send Two Factor Code to user phone by sms
     */
    private function sendTwoFactorCodeBySms($code){
        $sms = new SMSService();
        $sms->sendMessage([
            [
                "channel"=>"char",
                "sender"=> "VIRTA",
                "text"=> 'Code: '. $code,
                "phone"=> $this->phone
            ]
        ]);
    }
}
