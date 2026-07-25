<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Process Sale Return (Customer Refund)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Enter Invoice Number</label>
                            <input type="text" id="invoice_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. INV-01-20260613-001">
                        </div>
                        <div>
                            <button type="button" id="search_invoice" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                                Search Invoice
                            </button>
                        </div>
                    </div>

                    <div id="items_section" class="hidden">
                        <div class="bg-gray-50 p-4 rounded mb-6 border">
                            <h4 class="font-bold text-gray-700">Customer Details: <span id="disp_customer" class="text-blue-600"></span></h4>
                            <p class="text-sm text-gray-500">Sale Date: <span id="disp_date"></span></p>
                        </div>

                        <h3 class="text-lg font-bold mb-4">Items on Invoice</h3>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Medicine</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Batch</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Original Qty</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-orange-600">Return Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="items_body" class="bg-white divide-y divide-gray-200">
                                    <!-- Items load here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Reason for Return <span class="text-red-500">*</span></label>
                            <textarea id="reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="E.g. Wrong item purchased, adverse reaction..."></textarea>
                        </div>
                        
                        <input type="hidden" id="sale_id">

                        <div class="flex justify-end items-center space-x-4">
                            <p class="text-sm text-gray-500">Total Refund: <span id="estimated_refund" class="font-bold text-xl text-green-600">$0.00</span></p>
                            <button type="button" id="submit_return" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded shadow">
                                Process Refund
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#search_invoice').click(function() {
                let invoice = $('#invoice_number').val().trim();
                if(!invoice) return;

                $(this).text('Searching...').prop('disabled', true);

                $.ajax({
                    url: '/api/sale/' + invoice + '/items',
                    type: 'GET',
                    success: function(data) {
                        $('#sale_id').val(data.id);
                        $('#disp_customer').text(data.customer ? data.customer.name : 'Walk-in Customer');
                        $('#disp_date').text(data.sale_date);

                        let html = '';
                        data.sale_items.forEach(item => {
                            html += `<tr>
                                        <td class="px-4 py-3">${item.medicine.name}</td>
                                        <td class="px-4 py-3">${item.batch_number}</td>
                                        <td class="px-4 py-3">${item.quantity}</td>
                                        <td class="px-4 py-3">$${parseFloat(item.sale_price).toFixed(2)}</td>
                                        <td class="px-4 py-3">
                                            <input type="number" class="w-24 rounded border-gray-300 p-1 return-qty" 
                                                data-id="${item.id}" data-price="${item.sale_price}" 
                                                max="${item.quantity}" min="0" value="0">
                                        </td>
                                    </tr>`;
                        });
                        $('#items_body').html(html);
                        $('#items_section').removeClass('hidden');
                        $('#estimated_refund').text('$0.00');
                    },
                    error: function() {
                        alert('Invoice not found or invalid format.');
                        $('#items_section').addClass('hidden');
                    },
                    complete: function() {
                        $('#search_invoice').text('Search Invoice').prop('disabled', false);
                    }
                });
            });

            // Calculate refund dynamically
            $(document).on('input', '.return-qty', function() {
                let max = parseInt($(this).attr('max'));
                let val = parseInt($(this).val()) || 0;
                if(val > max) $(this).val(max);
                if(val < 0) $(this).val(0);

                let totalRefund = 0;
                $('.return-qty').each(function() {
                    let qty = parseInt($(this).val()) || 0;
                    let price = parseFloat($(this).data('price'));
                    totalRefund += (qty * price);
                });
                $('#estimated_refund').text('$' + totalRefund.toFixed(2));
            });

            $('#submit_return').click(function() {
                let itemsToReturn = [];
                $('.return-qty').each(function() {
                    let qty = parseInt($(this).val()) || 0;
                    if(qty > 0) {
                        itemsToReturn.push({
                            item_id: $(this).data('id'),
                            return_qty: qty
                        });
                    }
                });

                if(itemsToReturn.length === 0) {
                    alert("Please specify a return quantity greater than 0 for at least one item.");
                    return;
                }

                let reason = $('#reason').val();
                if(!reason) {
                    alert("Reason is required.");
                    return;
                }

                let payload = {
                    sale_id: $('#sale_id').val(),
                    reason: reason,
                    items: itemsToReturn
                };

                $(this).prop('disabled', true).text('Processing...');

                $.ajax({
                    url: '{{ route('returns.sale.store') }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(response) {
                        alert(response.message);
                        window.location.reload();
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                        $('#submit_return').prop('disabled', false).text('Process Refund');
                    }
                });
            });
        });
    </script>
</x-admin-layout>
