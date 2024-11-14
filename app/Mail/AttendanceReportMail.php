<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class AttendanceReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fileUrlPdf;
    public $pdfTodayReport;
    public $fileUrlExcel;

    public function __construct($fileUrlPdf, $pdfTodayReport, $fileUrlExcel)
    {
        $this->fileUrlPdf = $fileUrlPdf;
        $this->pdfTodayReport = $pdfTodayReport;
        $this->fileUrlExcel = $fileUrlExcel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('attendance.app@example.com', 'Apollo'),
            subject: 'Daily Attendance Report Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.daily-report',
            with: [
                'pdfTodayReport' => $this->pdfTodayReport,
                'fileUrlPdf' => $this->fileUrlPdf,
                'fileUrlExcel' => $this->fileUrlExcel,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
