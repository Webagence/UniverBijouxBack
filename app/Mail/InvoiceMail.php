<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
        $this->subject = 'Facture ' . $invoice->invoice_number . ' - ' . ($invoice->order?->reference ?? '');
    }

    public function build(): self
    {
        $pdfPath = storage_path('app/public/' . $this->invoice->pdf_path);

        if (file_exists($pdfPath)) {
            $this->attach($pdfPath, [
                'as' => $this->invoice->invoice_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $this->view('emails.invoice')
            ->with([
                'invoice' => $this->invoice,
                'order' => $this->invoice->order,
                'user' => $this->invoice->user,
            ]);
    }
}
