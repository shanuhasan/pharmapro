<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            @if(setting('pharmacy_logo'))
                @php
                    $imagePath = public_path(setting('pharmacy_logo'));
                    $embedLogo = '';
                    if (file_exists($imagePath) && is_file($imagePath)) {
                        $embedLogo = $message->embed($imagePath);
                    }
                @endphp
                @if($embedLogo)
                    <img src="{{ $embedLogo }}" alt="{{ setting('pharmacy_name', config('app.name')) }} Logo" style="max-height: 50px; display: inline-block; vertical-align: middle; margin-right: 10px;">
                @endif
            @endif
            <span style="display: inline-block; vertical-align: middle;">
                {{ strtoupper(setting('pharmacy_name', config('app.name'))) }} - INVOICE #{{ $sale->invoice_number }}
            </span>
        </x-mail::header>
    </x-slot:header>

Dear {{ $sale->customer->name ?? 'Customer' }},

Thank you for your recent purchase at **{{ setting('pharmacy_name', config('app.name')) }}**. 
We have attached the PDF version of your invoice for your records.

**Invoice Summary:**
- **Date:** {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
- **Total Amount:** {{ setting('currency_symbol', '₹') }}{{ number_format($sale->total_amount, 2) }}

If you have any questions about this invoice, please don't hesitate to contact us.

Thanks,<br>
{{ setting('pharmacy_name', config('app.name')) }}

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ setting('pharmacy_name', config('app.name')) }}. All rights reserved.
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
