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

                        <div class="mt-4 flex flex-wrap gap-4 items-center">
                            <button type="button" id="add_row" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Item
                            </button>
                            
                            <div class="flex items-center space-x-2 border-l border-gray-300 pl-4">
                                <input type="file" id="import_file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <button type="button" id="import_btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center whitespace-nowrap">
                                    <i class="fas fa-file-import mr-2"></i> Import Excel/CSV
                                </button>
                                <span id="import_loading" class="hidden text-sm text-gray-500"><i class="fas fa-spinner fa-spin mr-1"></i> Importing...</span>
                            </div>
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

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <link href="{{ asset('vendor/select2/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
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
                    <td class="hidden"><input type="hidden" name="items[${rowIdx}][item_total]" class="item_total_input" value=""></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" placeholder="Strip Price" class="strip_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" name="items[${rowIdx}][purchase_price]" placeholder="Unit Pur. Price" class="purchase_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00" required></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" placeholder="Strip Sale" class="strip_sale_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" name="items[${rowIdx}][sale_price]" placeholder="Unit Sale Price" class="sale_price_input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" min="0" value="0.00" required></td>
                    <td colspan="2" class="px-3 py-2 text-center"><button type="button" class="remove_row inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"><i class="fas fa-trash mr-1"></i> Remove</button></td>
                </tr>`;
                
                $('#items_tbody').append(tr);
                
                // Initialize select2 on the new dropdown
                $(`#row_${rowIdx}_1 .medicine_select`).select2({
                    width: '100%',
                    placeholder: 'Select Medicine'
                });
                
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

            // Import Excel/CSV functionality
            $('#import_btn').click(function() {
                let fileInput = $('#import_file')[0];
                if (fileInput.files.length === 0) {
                    alert('Please select a file to import.');
                    return;
                }

                let formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $('#import_btn').prop('disabled', true);
                $('#import_loading').removeClass('hidden');

                $.ajax({
                    url: '{{ route("purchases.import_file") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#import_btn').prop('disabled', false);
                        $('#import_loading').addClass('hidden');

                        if (response.success) {
                            // Populate invoice details
                            if (response.supplier_id) {
                                $('#supplier_id').val(response.supplier_id).trigger('change');
                            } else if (response.supplier_name) {
                                alert("Supplier '" + response.supplier_name + "' not found in database. Please select manually.");
                            }

                            if (response.invoice_number) {
                                $('#invoice_number').val(response.invoice_number);
                            }

                            if (response.purchase_date) {
                                $('#purchase_date').val(response.purchase_date);
                            }

                            // Clear existing empty rows if any
                            let firstRowMedicine = $(`#row_0_1 .medicine_select`).val();
                            if ($('#items_tbody tr').length <= 2 && !firstRowMedicine) {
                                $('#items_tbody').empty();
                                rowIdx = 0;
                            }

                            // Add imported items
                            let missingMedicines = [];
                            response.items.forEach(function(item) {
                                addRow();
                                let currentRowIdx = rowIdx - 1;
                                let tr1 = $(`#row_${currentRowIdx}_1`);
                                let tr2 = $(`#row_${currentRowIdx}_2`);

                                if (item.medicine_id) {
                                    let selectEl = tr1.find('.medicine_select');
                                    if (selectEl.find("option[value='" + item.medicine_id + "']").length === 0) {
                                        let newOption = new Option(item.medicine_name, item.medicine_id, false, false);
                                        $(newOption).attr('data-medicines-per-strip', item.medicines_per_strip);
                                        $(newOption).attr('data-hsn-code', item.hsn_code);
                                        selectEl.append(newOption);
                                        $('#medicine_template').append($(newOption).clone());
                                    }
                                    selectEl.val(item.medicine_id).trigger('change');
                                } else {
                                    missingMedicines.push(item.medicine_name);
                                }

                                tr1.find('.hsn_input').val(item.hsn_code);
                                tr1.find('input[name="items[' + currentRowIdx + '][batch_number]"]').val(item.batch_number);
                                if (item.expiry_date) {
                                    tr1.find('input[name="items[' + currentRowIdx + '][expiry_date]"]').val(item.expiry_date);
                                }
                                
                                // Set Strips and let the inputs trigger changes for qty
                                let stripInput = tr1.find('.strip_input');
                                stripInput.val(item.quantity).trigger('input');

                                // Set Prices (Excel provides Strip Rate and Strip MRP)
                                let stripPriceInput = tr2.find('.strip_price_input');
                                stripPriceInput.val(item.purchase_price.toFixed(2)).trigger('input');

                                let stripSalePriceInput = tr2.find('.strip_sale_price_input');
                                stripSalePriceInput.val(item.sale_price.toFixed(2)).trigger('input');
                                
                                // Set item total (Discounted total)
                                if (item.item_total !== undefined) {
                                    tr2.find('.item_total_input').val(item.item_total);
                                }
                            });

                            if (missingMedicines.length > 0) {
                                alert("Some medicines were not found in the database and need to be selected manually:\\n" + missingMedicines.join(", "));
                            }
                            
                            // Reset file input
                            $('#import_file').val('');
                            alert("Import successful! Please review the items.");
                        }
                    },
                    error: function(xhr) {
                        $('#import_btn').prop('disabled', false);
                        $('#import_loading').addClass('hidden');
                        let errorMessage = 'An error occurred during import.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
</x-admin-layout>
