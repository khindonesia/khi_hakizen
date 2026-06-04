<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpEmail extends Notification
{
    use Queueable;

    protected $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi 2FA Anda')
            ->line('Berikut adalah kode OTP untuk masuk ke akun Anda:')
            ->line($this->otp)
            ->line('Kode ini hanya berlaku selama 5 menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, segera amankan akun Anda.');
    }
}
