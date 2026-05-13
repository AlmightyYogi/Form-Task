<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;

class VerifyEmailCustom extends VerifyEmail
{
    // public function send($notifiable)
    // {
    //     $verificationUrl = $this->verificationUrl($notifiable);

    //     Http::withHeaders([
    //         'Authorization' => 'Bearer ' . env('RESEND_KEY'),
    //         'Content-Type'  => 'application/json',
    //     ])->post('https://api.resend.com/emails', [
    //         'from' => env('MAIL_FROM_ADDRESS'),
    //         'to' => [$notifiable->email],
    //         'subject' => 'Verify your email',
    //         'html' => "
    //             <h2>Email Verification</h2>
    //             <p>Click the button below to verify your email:</p>
    //             <a href='{$verificationUrl}' style='padding:10px;background:#4CAF50;color:white;text-decoration:none;'>Verify Email</a>
    //         ",
    //     ]);

    //     return null;
    // }

    // public function via($notifiable)
    // {
    //     return [];
    // }
}