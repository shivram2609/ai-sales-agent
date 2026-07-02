<?php

namespace App\Mail;

use App\Models\OutboundEmailJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OutreachMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OutboundEmailJob $job)
    {
    }

    public function build(): self
    {
        // Outreach copy is already plain text (sanitized, no HTML), so we
        // send it as plain text to keep it looking human and to avoid any
        // HTML rendering quirks turning it back into "salesy" looking mail.
        return $this
            ->to($this->job->to_email, $this->job->to_name)
            ->subject($this->job->subject ?: '(no subject)')
            ->text('emails.outreach-plain')
            ->with(['body' => $this->job->body]);
    }
}
