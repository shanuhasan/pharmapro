<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale, $pdfContent)
    {
        $this->sale = $sale;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $pharmacyName = setting('pharmacy_name', config('app.name'));
        
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), $pharmacyName),
            subject: 'Invoice ' . $this->sale->invoice_number . ' from ' . $pharmacyName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.sales.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'Invoice-'.$this->sale->invoice_number.'.pdf')
                    ->withMime('application/pdf'),
        ];
    }
}
