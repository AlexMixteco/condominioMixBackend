<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerificarCorreo extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        $temporarySignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );


        preg_match('/\/(\d+)\/([a-f0-9]+)\?(.+)$/', $temporarySignedUrl, $matches);

        if ($matches) {
            return $frontendUrl . '/verificar-email/' . $matches[1] . '/' . $matches[2] . '?' . $matches[3];
        }

        return $temporarySignedUrl;
    }
}
