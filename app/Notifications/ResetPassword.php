<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // URL absolue pour le logo (doit être accessible publiquement)
        $appUrl = config('app.url', url('/'));
        // Utiliser url() pour générer une URL absolue complète
        $logoUrl = url('/images/true-logo.png');

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe - Lome+')
            ->view('emails.reset-password', [
                'url' => $url,
                'logoUrl' => $logoUrl,
                'appUrl' => $appUrl,
                'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
    }
}
