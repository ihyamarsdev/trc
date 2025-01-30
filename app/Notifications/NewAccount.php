<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewAccount extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected string $password, protected ?Model $tenant = null)
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage())
                ->subject("Akun Anda Baru Saja Dibuat di $appName")
                ->line("Berikut Informasi Detail:")
                ->line(new HtmlString("<strong>Email</strong> : {$notifiable->email}"))
                ->line(new HtmlString("<strong>Password Sementara</strong> : {$this->password}"))
                ->line("Kamu Akan di Minta untuk mengganti Password baru untuk login pertama kali.")
                ->action('Pergi Ke Aplikasi', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
