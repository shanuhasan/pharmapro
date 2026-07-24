<x-mail::message>
# Invoice #{{ $sale->invoice_number }}

Dear {{ $sale->customer->name ?? 'Customer' }},

Thank you for your recent purchase at {{ config('app.name') }}. 
We have attached the PDF version of your invoice for your records.

**Invoice Summary:**
- **Date:** {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
- **Total Amount:** {{ setting('currency_symbol', '₹') }}{{ number_format($sale->total_amount, 2) }}
- **Payment Method:** {{ ucfirst($sale->payment_method) }}

If you have any questions about this invoice, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
