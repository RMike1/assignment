<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class AttendanceReportMail extends Mailable
{
    use Queueable, SerializesModels;


    public $filePath;
    public $todayReport;
    public $excelFilePath;

    public function __construct($fileUrlPdf,$pdfTodayReport,$fileUrlExcel)
    {
        $this->filePath = $fileUrlPdf;
        $this->todayReport = $pdfTodayReport;
        $this->excelFilePath = $fileUrlExcel;
        // $this->excelFileName = $excelFileName;
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
                'todayReport'=>$this->todayReport,
                'filepath' =>$this->filePath,
                'excelFilePath' => $this->excelFilePath,
                // 'excelFilePath' => url('reports/' . $this->excelFilePath),
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
