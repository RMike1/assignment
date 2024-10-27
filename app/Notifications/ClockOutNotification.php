<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClockOutNotification extends Notification
{
    use Queueable;

    public $clock_out;
    // public $employee;
    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->clock_out=$data;
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
        return (new MailMessage)
                        ->from('attendance.app@example.com', 'John Doe')
                        ->subject('Clock-Out Confirmation')
                        ->view('email.clock-out',[
                            'data'=>$this->clock_out,
                            'name'=>$notifiable->name
                        ]);
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
