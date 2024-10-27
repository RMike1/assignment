<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAttendanceNotification extends Notification
{
    use Queueable;

    
    public $adminmessage;

    public function __construct($adminmessage)
    {
        $this->adminmessage=$adminmessage;
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
                        ->subject('Clock Attendance')
                        ->view('email.admin-clock',[
                            'data'=>$this->adminmessage,
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
