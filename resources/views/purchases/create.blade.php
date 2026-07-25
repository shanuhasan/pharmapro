<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Purchase') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('purchases.store') }}">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div>
                                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch <span class="text-red-500">*</span></label>
                                @if(auth()->user()->role === 'admin')
                                    <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" value="{{ auth()->user()->branch->name ?? 'N/A' }}" readonly>
                                @endif
                            </div>

                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier <span class="text-red-500">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="invoice_number" class="block text-sm font-medium text-gray-700">Invoice Number <span class="text-red-500">*</span></label>
                                <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>

                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date <span class="text-red-500">*</span></label>
                                <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>
                        </div>

                        <hr class="mb-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Purchase Items</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border" id="items_table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">Medicine</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">HSN Code</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Batch</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[150px]">Expiry</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">Strip</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">Qty</th>
                                    </tr>
                                    <tr class="bg-gray-100">
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strip Price</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Purchase Price</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strip Sale Price</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Sale Price</th>
                                        <th colspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="items_tbody">
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="add_row" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Item
                            </button>
                        </div>

                        <div class="mt-6 flex justify-end border-t pt-4">
                            <a href="{{ route('purchases.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 mr-3">Cancel</a>
                            <button type="submit" class="bg-medical-primary border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-blue-700">
                                Save Purchase & Update Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Medicine template for JS -->
    <select id="medicine_template" class="hidden">
        <option value="" data-medicines-per-strip="1" data-hsn-code="">Select Medicine</option>
        @foreach($medicines as $medicine)
            <option value="{{ $medicine->id }}" data-medicines-per-strip="{{ $medicine->medicines_per_strip ?? 1 }}" data-hsn-code="{{ $medicine->hsn_code ?? '' }}">{{ $medicine->name }}</option>
        @endforeach
    </select>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            let rowIdx = 0;

            function addRow() {
                let medicineOptions = $('#medicine_template').html();
                
                let tr = `
                <tr id="row_${rowIdx}_1" data-row-idx="${rowIdx}" class="item-row-1 border-t-2 border-gray-300">
                    <td class="px-3 py-2"><select name="items[${rowIdx}][medicine_id]" class="medicine_select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" required>${medicineOptions}</select></td>
                    <td class="px-3 py-2"><input type="text" name="items[${rowIdx}][hsn_code]" placeholder="HSN Code" class="hsn_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm"></td>
                    <td class="px-3 py-2"><input type="text" name="items[${rowIdx}][batch_number]" placeholder="Batch No." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm"></td>
                    <td class="px-3 py-2"><input type="date" name="items[${rowIdx}][expiry_date]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" required></td>
                    <td class="px-3 py-2"><input type="number" name="items[${rowIdx}][strip]" placeholder="Strips" class="strip_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" step="any"></td>
                    <td class="px-3 py-2"><input type="number" name="items[${rowIdx}][quantity]" placeholder="Qty" class="qty_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="1" value="1" required></td>
                </tr>
                <tr id="row_${rowIdx}_2" data-row-idx="${rowIdx}" class="item-row-2 bg-gray-50 border-b border-gray-200">
                    <td class="px-3 py-2"><input type="number" step="0.01" placeholder="Strip Price" class="strip_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" name="items[${rowIdx}][purchase_price]" placeholder="Unit Pur. Price" class="purchase_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00" required></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" placeholder="Strip Sale" class="strip_sale_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" name="items[${rowIdx}][sale_price]" placeholder="Unit Sale Price" class="sale_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00" required></td>
                    <td colspan="2" class="px-3 py-2 text-center"><button type="button" class="remove_row inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"><i class="fas fa-trash mr-1"></i> Remove</button></td>
                </tr>`;
                
                $('#items_tbody').append(tr);
                rowIdx++;
            }

            // Add first row on load
            addRow();

            // Add row button
            $('#add_row').click(function() {
                addRow();
            });

            // Remove row button
            $(document).on('click', '.remove_row', function() {
                if ($('#items_tbody tr').length > 2) {
                    let rowIdx = $(this).closest('tr').data('row-idx');
                    $(`#row_${rowIdx}_1, #row_${rowIdx}_2`).remove();
                } else {
                    alert("At least one item is required.");
                }
            });

            // When medicine is selected, store perStrip data
            $(document).on('change', '.medicine_select', function() {
                let rowIdx = $(this).closest('tr').data('row-idx');
                let itemContainer = $(`#row_${rowIdx}_1, #row_${rowIdx}_2`);
                let tr1 = $(`#row_${rowIdx}_1`);
                let perStrip = $(this).find('option:selected').data('medicines-per-strip') || 1;
                let hsnCode = $(this).find('option:selected').data('hsn-code') || '';
                
                tr1.data('medicines-per-strip', perStrip);
                
                // Set HSN code
                if (hsnCode && !itemContainer.find('.hsn_input').val()) {
                    itemContainer.find('.hsn_input').val(hsnCode);
                }
                
                // Recalculate strip based on current qty
                let qty = parseFloat(itemContainer.find('.qty_input').val());
                if (!isNaN(qty)) {
                    let strips = qty / perStrip;
                    itemContainer.find('.strip_input').val(Number.isInteger(strips) ? strips : strips.toFixed(2));
                }

                // Recalculate strip price based on current unit price
                let unitPrice = parseFloat(itemContainer.find('.purchase_price_input').val());
                if (!isNaN(unitPrice) && unitPrice > 0) {
                    let stripPrice = unitPrice * perStrip;
                    itemContainer.find('.strip_price_input').val(stripPrice.toFixed(2));
                }

                // Recalculate strip sale price based on current unit sale price
                let unitSalePrice = parseFloat(itemContainer.find('.sale_price_input').val());
                if (!isNaN(unitSalePrice) && unitSalePrice > 0) {
                    let stripSalePrice = unitSalePrice * perStrip;
                    itemContainer.find('.strip_sale_price_input').val(stripSalePrice.toFixed(2));
                }
            });

            // When strip is entered, calculate qty
            $(document).on('input change keyup', '.strip_input', function() {
                let rowIdx = $(this).closest('tr').data('row-idx');
                let itemContainer = $(`#row_${rowIdx}_1, #row_${rowIdx}_2`);
                let tr1 = $(`#row_${rowIdx}_1`);
                let perStrip = tr1.data('medicines-per-strip') || tr1.find('.medicine_select option:selected').data('medicines-per-strip') || 1;
                let strips = parseFloat($(this).val());
                if (!isNaN(strips)) {
                    let qty = Math.round(strips * perStrip);
                    itemContainer.find('.qty_input').val(qty);
                } else {
                    itemContainer.find('.qty_input').val('');
                }
            });

            // When qty is entered, calculate strip
            $(document).on('input change keyup', '.qty_input', function() {
                let rowIdx = $(this).closest('tr').data('row-idx');
                let itemContainer = $(`#row_${rowIdx}_1, #row_${rowIdx}_2`);
                let tr1 = $(`#row_${rowIdx}_1`);
                let perStrip = tr1.data('medicines-per-strip') || tr1.find('.medicine_select option:selected').data('medicines-per-strip') || 1;
                let qty = parseFloat($(this).val());
                if (!isNaN(qty)) {
                    let strips = qty / perStrip;
                    itemContainer.find('.strip_input').val(Number.isInteger(strips) ? strips : strips.toFixed(2));
                } else {
                    itemContainer.find('.strip_input').val('');
                }
            });

            // When strip price is entered, calculate unit purchase price
            $(document).on('input change keyup', '.strip_price_input', function() {
                let rowIdx = $(this).closest('tr').data('row-idx');
                let itemContainer = $(`#row_${rowIdx}_1, #row_${rowIdx}_2`);
                let tr1 = $(`#row_${rowIdx}_1`);
                let perStrip = tr1.data('medicines-per-strip') || tr1.find('.medicine_select option:selected').data('medicines-per-strip') || 1;
                let stripPrice = parseFloat($(this).val());
                if (!isNaN(stripPrice)) {
                    let unitPrice = stripPrice / perStrip;
                    itemContainer.find('.purchase_price_input').val(unitPrice.toFixed(2));
                } else {
                    itemContainer.find('.purchase_price_input').val('');
                }
            });

            // When unit purchase price is entered, calculate strip price
            $(document).on('input change keyup', '.purchase_price_input', function() {
                let rowIdx = $(this).closest('tr').data('row-idx');
                let itemContainer = $(`#row_${rowIdx}_1, #row_${rowIdx}_2`);
                let tr1 = $(`#row_${rowIdx}_1`);
                let perStrip = tr1.data('medicines-per-strip') || tr1.find('.medicine_select option:selected').data('medicines-per-strip') || 1;
                let unitPrice = parseFloat($(this).val());
                if (!isNaN(unitPrice)) {
                    let stripPrice = unitPrice * perStrip;
                    itemContainer.find('.strip_price_input').val(stripPrice.toFixed(2));
                } else {
                    itemContainer.find('.strip_price_input').val('');
                }
            });

            // When strip sale price is entered, calculate unit sale price
            $(document).on('input change keyup', '.strip_sale_price_input', function() {
                let rowIdx = $(this).closest('tr').data('row-idx');
                let itemContainer = $(`#row_${rowIdx}_1, #row_${rowIdx}_2`);
                let tr1 = $(`#row_${rowIdx}_1`);
                let perStrip = tr1.data('medicines-per-strip') || tr1.find('.medicine_select option:selected').data('medicines-per-strip') || 1;
                let stripSalePrice = parseFloat($(this).val());
                if (!isNaN(stripSalePrice)) {
                    let unitSalePrice = stripSalePrice / perStrip;
                    itemContainer.find('.sale_price_input').val(unitSalePrice.toFixed(2));
                } else {
                    itemContainer.find('.sale_price_input').val('');
                }
            });

            // When unit sale price is entered, calculate strip sale price
            $(document).on('input change keyup', '.sale_price_input', function() {
                let rowIdx = $(this).closest('tr').data('row-idx');
                let itemContainer = $(`#row_${rowIdx}_1, #row_${rowIdx}_2`);
                let tr1 = $(`#row_${rowIdx}_1`);
                let perStrip = tr1.data('medicines-per-strip') || tr1.find('.medicine_select option:selected').data('medicines-per-strip') || 1;
                let unitSalePrice = parseFloat($(this).val());
                if (!isNaN(unitSalePrice)) {
                    let stripSalePrice = unitSalePrice * perStrip;
                    itemContainer.find('.strip_sale_price_input').val(stripSalePrice.toFixed(2));
                } else {
                    itemContainer.find('.strip_sale_price_input').val('');
                }
            });
        });
    </script>
</x-admin-layout>
