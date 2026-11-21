<?php

namespace App\Mail;

use App\Models\DecisionNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AcademicDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly DecisionNotification $notice) {}

    public function envelope(): Envelope
    {
        $type = $this->notice->event_type === 'corrected' ? 'Correction to academic decision' : 'Academic decision published';

        return new Envelope(
            subject: "ICHES 2027 — {$type} | {$this->notice->paper_reference}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.academic-decision',
            with: ['notice' => $this->notice],
        );
    }
}
