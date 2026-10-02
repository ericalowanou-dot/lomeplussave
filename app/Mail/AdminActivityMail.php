<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Mail envoyé à l'administrateur lors d'une action d'un vendeur.
 * Volontairement non "ShouldQueue" : aucun worker de file ne tourne en production.
 */
class AdminActivityMail extends Mailable
{
    use Queueable;

    /**
     * @param  array<string, string>  $details  Libellé => valeur
     */
    public function __construct(
        public string $subjectLine,
        public string $heading,
        public array $details,
        public ?string $bodyText,
        public string $actionUrl,
        public string $actionLabel,
        public ?string $replyToAddress = null,
        public ?string $replyToName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress, $this->replyToName)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-activity');
    }
}
