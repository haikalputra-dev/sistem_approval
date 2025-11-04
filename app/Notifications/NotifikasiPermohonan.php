<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifikasiPermohonan extends Notification
{
    use Queueable;

    protected $message;
    protected $url;

    /**
     * Create a new notification instance.
     *
     * @param string $message
     * @param string $url
     * @return void
     */
    public function __construct(string $message, string $url)
    {
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Kita akan kirim via database (untuk lonceng di web) dan email
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Notifikasi Sistem Approval Transaksi')
                    ->line($this->message)
                    ->action('Lihat Permohonan', $this->url)
                    ->line('Terima kasih telah menggunakan aplikasi ini.');
    }

    /**
     * Get the array representation of the notification.
     * (Ini yang akan disimpan di tabel 'notifications' untuk lonceng)
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'icon' => 'fa-file-invoice-dollar' // Contoh ikon Font Awesome
        ];
    }
}
