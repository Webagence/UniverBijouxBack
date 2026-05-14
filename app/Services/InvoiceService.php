<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generateForOrder(Order $order): Invoice
    {
        $order->load(['items', 'user']);

        $existing = $order->invoices()->first();
        if ($existing) {
            return $existing;
        }

        $settings = SiteSetting::where('key', 'general')->first()?->value ?? [];
        $brandName = $settings['siteName'] ?? 'UNIVER BIJOUX';
        $brandTagline = $settings['tagline'] ?? 'Grossiste bijoux français';
        $brandAddress = $settings['address'] ?? '';
        $brandSiret = $settings['siret'] ?? '';
        $brandLogo = $settings['logo'] ?? null;

        $buyer = $order->user;
        $buyerCompany = $buyer->company_name ?? '';
        $buyerContact = $buyer->contact_name ?? $buyer->name ?? '';
        $buyerSiret = $buyer->siret ?? '';
        $buyerVat = $buyer->vat_number ?? '';
        $buyerAddress = collect([
            $buyer->address ?? ($order->shipping_address['address'] ?? ''),
            trim(($buyer->postal_code ?? '') . ' ' . ($buyer->city ?? ($order->shipping_address['city'] ?? ''))),
            $buyer->country ?? ($order->shipping_address['country'] ?? ''),
        ])->filter()->join('<br>');

        $lines = $order->items->map(function ($item) {
            return [
                'name' => $item->product_name,
                'reference' => $item->product_reference ?? '',
                'quantity' => $item->quantity,
                'unit_price_ht' => number_format($item->unit_price_ht, 2, ',', ' '),
                'line_total_ht' => number_format($item->line_total_ht, 2, ',', ' '),
            ];
        })->toArray();

        $vatRate = $order->subtotal_ht > 0
            ? round(($order->vat_amount / $order->subtotal_ht) * 100)
            : 20;

        $invoiceNumber = 'FAC-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $date = now()->locale('fr')->isoFormat('DD MMMM YYYY');

        $logoPath = null;
        if ($brandLogo) {
            $logoRelative = str_replace('/storage/', '', parse_url($brandLogo, PHP_URL_PATH) ?? '');
            $logoFullPath = storage_path('app/public/' . $logoRelative);
            if (file_exists($logoFullPath)) {
                $logoPath = $logoFullPath;
            }
        }

        $html = view('invoices.pdf', [
            'invoiceNumber' => $invoiceNumber,
            'date' => $date,
            'order' => $order,
            'settings' => $settings,
            'brandName' => $brandName,
            'brandTagline' => $brandTagline,
            'brandAddress' => $brandAddress,
            'brandSiret' => $brandSiret,
            'logoPath' => $logoPath,
            'buyerCompany' => $buyerCompany,
            'buyerContact' => $buyerContact,
            'buyerSiret' => $buyerSiret,
            'buyerVat' => $buyerVat,
            'buyerAddress' => $buyerAddress,
            'lines' => $lines,
            'vatRate' => $vatRate,
        ])->render();

        $pdf = Pdf::loadHtml($html)
            ->setPaper('a4')
            ->setOption(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $pdfPath = 'invoices/' . $order->id . '/' . $invoiceNumber . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'pdf_path' => $pdfPath,
            'total_ht' => $order->subtotal_ht,
            'vat_amount' => $order->vat_amount,
            'total_ttc' => $order->total_ttc,
            'issued_at' => now(),
            'paid' => true,
        ]);

        return $invoice;
    }

    public function sendInvoiceByEmail(Invoice $invoice): void
    {
        $order = $invoice->order;
        $user = $order->user;

        if (!$user->email) {
            return;
        }

        $pdfPath = storage_path('app/public/' . $invoice->pdf_path);

        if (!file_exists($pdfPath)) {
            return;
        }

        Mail::to($user->email)->send(new \App\Mail\InvoiceMail($invoice));
    }
}
