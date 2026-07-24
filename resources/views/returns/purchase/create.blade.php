<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Process Purchase Return') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Select Purchase</label>
                            <select id="purchase_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-- Choose Purchase --</option>
                                @foreach($purchases as $p)
                                    <option value="{{ $p->id }}">Purchase #{{ $p->id }} - {{ $p->supplier->name ?? 'Unknown' }} ({{ $p->purchase_date }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="items_section" class="hidden">
                        <hr class="my-6">
                        <h3 class="text-lg font-bold mb-4">Items in Purchase</h3>
                        
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Medicine</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Batch</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Original Qty</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-blue-600">Return Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="items_body" class="bg-white divide-y divide-gray-200">
                                    <!-- Items load here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Reason for Return <span class="text-red-500">*</span></label>
                            <textarea id="reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="E.g. Expired batch, defective packaging..."></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" id="submit_return" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow">
                                Process Return
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#purchase_id').change(function() {
                let id = $(this).val();
                if(!id) {
                    $('#items_section').addClass('hidden');
                    return;
                }

                $.get('/api/purchase/' + id + '/items', function(data) {
                    let html = '';
                    data.purchase_items.forEach(item => {
                        html += `<tr>
                                    <td class="px-4 py-3">${item.medicine.name}</td>
                                    <td class="px-4 py-3">${item.batch_number}</td>
                                    <td class="px-4 py-3">${item.quantity}</td>
                                    <td class="px-4 py-3">$${parseFloat(item.purchase_price).toFixed(2)}</td>
                                    <td class="px-4 py-3">
                                        <input type="number" class="w-24 rounded border-gray-300 p-1 return-qty" data-id="${item.id}" max="${item.quantity}" min="0" value="0">
                                    </td>
                                </tr>`;
                    });
                    $('#items_body').html(html);
                    $('#items_section').removeClass('hidden');
                });
            });

            $('#submit_return').click(function() {
                let itemsToReturn = [];
                $('.return-qty').each(function() {
                    let qty = parseInt($(this).val());
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
                    purchase_id: $('#purchase_id').val(),
                    reason: reason,
                    items: itemsToReturn
                };

                $(this).prop('disabled', true).text('Processing...');

                $.ajax({
                    url: '{{ route('returns.purchase.store') }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(response) {
                        alert(response.message);
                        window.location.reload();
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                        $('#submit_return').prop('disabled', false).text('Process Return');
                    }
                });
            });
        });
    </script>
</x-admin-layout>
