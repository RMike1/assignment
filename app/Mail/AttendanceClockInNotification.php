<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class AttendanceClockInNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $usermessage;
    public $username;

    public function __construct($message,$username)
    {
        $this->usermessage=$message;
        $this->username=$username;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('attendance.app@example.com', 'John Doe'),
            subject: 'User Attendance Notification Clock-in',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.clock-in',
            with: [
                'username'=>$this->username,
                'usermessage'=>$this->usermessage,
            ],
                   
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
