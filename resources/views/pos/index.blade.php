<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Point of Sale (POS)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-6">
                
                <!-- Left Side: Cart & Search -->
                <div class="md:w-2/3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        @if(auth()->user()->role === 'admin')
                        <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <label class="block text-sm font-bold text-blue-800 mb-2">Admin: Select Active Branch for POS</label>
                            <select id="pos_branch_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <h3 class="text-lg font-bold mb-4">Search Medicine</h3>
                        <div class="relative mb-6">
                            <input type="text" id="medicine_search" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3" placeholder="Search by name or generic name (e.g. Paracetamol)...">
                            <div id="medicine_results" class="absolute z-10 w-full bg-white border border-gray-200 mt-1 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                                <!-- Results populate here -->
                            </div>
                        </div>

                        <h3 class="text-lg font-bold mb-4">Current Cart</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="cart_table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Strip</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="cart_body">
                                    <!-- Cart Items -->
                                    <tr id="empty_cart"><td colspan="7" class="px-4 py-4 text-center text-gray-500">Cart is empty</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Checkout & Payment -->
                <div class="md:w-1/3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-4">Customer Details</h3>
                        <div class="relative mb-2">
                            <input type="text" id="customer_search" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" placeholder="Search Customer (Name/Phone)...">
                            <div id="customer_results" class="absolute z-10 w-full bg-white border border-gray-200 mt-1 rounded-md shadow-lg hidden max-h-40 overflow-y-auto">
                                <!-- Results populate here -->
                            </div>
                        </div>
                        <div class="text-right mb-4">
                            <button type="button" id="toggle_new_customer" class="text-xs text-blue-600 hover:text-blue-800"><i class="fas fa-plus"></i> Add New Manually</button>
                        </div>
                        
                        <!-- Manual Customer Entry Form -->
                        <div id="new_customer_div" class="hidden mb-6 p-3 bg-gray-50 border border-gray-200 rounded-md">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-gray-700">New Customer</span>
                                <button type="button" id="cancel_new_customer" class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-times"></i> Cancel</button>
                            </div>
                            <input type="text" id="new_customer_name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 mb-2" placeholder="Customer Name">
                            <input type="text" id="new_customer_phone" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 mb-2" placeholder="Phone Number (Optional)">
                            <textarea id="new_customer_address" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" placeholder="Customer Address (Optional)" rows="2"></textarea>
                        </div>
                        <div id="selected_customer_div" class="hidden mb-6 p-3 bg-blue-50 border border-blue-200 rounded-md flex justify-between items-center">
                            <div>
                                <p class="text-sm font-bold text-blue-800" id="cust_name_disp"></p>
                                <p class="text-xs text-blue-600" id="cust_phone_disp"></p>
                            </div>
                            <button type="button" id="remove_customer" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                        </div>
                        <input type="hidden" id="selected_customer_id">

                        <hr class="my-4">
                        <h3 class="text-lg font-bold mb-4">Doctor Details (Optional)</h3>
                        <div class="mb-6 p-3 bg-gray-50 border border-gray-200 rounded-md">
                            <input type="text" id="doctor_name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 mb-2" placeholder="Doctor Name">
                            <textarea id="doctor_address" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" placeholder="Doctor Address" rows="2"></textarea>
                        </div>

                        <hr class="my-4">
                        <h3 class="text-lg font-bold mb-4">Invoice Details</h3>
                        <div class="mb-6 p-3 bg-gray-50 border border-gray-200 rounded-md">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
                            <input type="date" id="sale_date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" value="{{ date('Y-m-d') }}">
                        </div>

                        <hr class="my-4">
                        <h3 class="text-lg font-bold mb-4">Payment Summary</h3>
                        
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-bold" id="summary_subtotal">{{ setting('currency_symbol', '₹') }}0.00</span>
                        </div>
                        
                        <div class="flex justify-between mb-2 items-center">
                            <span class="text-gray-600">Discount (%)</span>
                            <div class="flex items-center">
                                <input type="number" id="summary_discount_pct" value="0" min="0" max="100" step="any" class="w-16 text-right rounded-md border-gray-300 shadow-sm sm:text-sm mr-2">
                                <span class="text-sm font-bold text-gray-700 w-20 text-right" id="summary_discount_amt_display">{{ setting('currency_symbol', '₹') }}0.00</span>
                                <input type="hidden" id="summary_discount" value="0">
                            </div>
                        </div>

                        <div class="flex justify-between mb-2 items-center">
                            <span class="text-gray-600">SGST (%)</span>
                            <div class="flex items-center">
                                <input type="number" id="summary_sgst_pct" value="{{ setting('sgst_percentage', 0) }}" min="0" step="any" class="w-16 text-right rounded-md border-gray-300 shadow-sm sm:text-sm mr-2">
                                <span class="text-sm font-bold text-gray-700 w-20 text-right" id="summary_sgst_amt_display">{{ setting('currency_symbol', '₹') }}0.00</span>
                                <input type="hidden" id="summary_sgst" value="0">
                            </div>
                        </div>

                        <div class="flex justify-between mb-4 items-center">
                            <span class="text-gray-600">CGST (%)</span>
                            <div class="flex items-center">
                                <input type="number" id="summary_cgst_pct" value="{{ setting('cgst_percentage', 0) }}" min="0" step="any" class="w-16 text-right rounded-md border-gray-300 shadow-sm sm:text-sm mr-2">
                                <span class="text-sm font-bold text-gray-700 w-20 text-right" id="summary_cgst_amt_display">{{ setting('currency_symbol', '₹') }}0.00</span>
                                <input type="hidden" id="summary_cgst" value="0">
                            </div>
                        </div>
                        <input type="hidden" id="summary_tax" value="0">

                        <div class="flex justify-between mb-6 pt-4 border-t-2 border-gray-200">
                            <span class="text-xl font-bold text-gray-800">Total</span>
                            <span class="text-xl font-bold text-green-600" id="summary_total">{{ setting('currency_symbol', '₹') }}0.00</span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select id="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <option value="cash">Cash</option>
                                <option value="card">Credit Card</option>
                                <option value="online">Online Transfer</option>
                            </select>
                        </div>

                        <div class="flex justify-between mb-4 items-center">
                            <span class="text-gray-800 font-bold">Paid Amount</span>
                            <input type="number" id="paid_amount" value="0" min="0" class="w-32 text-right rounded-md border-gray-300 shadow-sm font-bold text-lg">
                        </div>

                        <div class="flex justify-between mb-6 items-center">
                            <span class="text-gray-600 font-bold">Change Due</span>
                            <span class="text-xl font-bold text-orange-500" id="change_amount">{{ setting('currency_symbol', '₹') }}0.00</span>
                        </div>

                        <button type="button" id="complete_sale" class="w-full bg-medical-primary hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md shadow-lg text-lg">
                            <i class="fas fa-check-circle mr-2"></i> Complete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Selection Modal -->
    <div id="batchModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="batch_modal_title">Select Batch</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Batch</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Expiry</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Available Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Sale Price</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="batch_tbody">
                                <!-- Batches populate here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="close_batch_modal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const currSym = "{{ setting('currency_symbol', '₹') }}";
        
        $(document).ready(function() {
            // Setup CSRF
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            let cart = [];
            let subtotal = 0;
            let total = 0;

            // --- 1. MEDICINE SEARCH ---
            $('#medicine_search').on('input', function() {
                let query = $(this).val();
                if(query.length < 2) {
                    $('#medicine_results').addClass('hidden');
                    return;
                }

                $.get('/api/medicine/search', {q: query}, function(data) {
                    let html = '';
                    if(data.length === 0) {
                        html = '<div class="p-3 text-gray-500">No medicines found.</div>';
                    } else {
                        data.forEach(med => {
                            html += `<div class="p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 med-result" data-id="${med.id}" data-name="${med.name}">
                                        <p class="font-bold text-gray-800">${med.name} <span class="text-xs font-normal text-gray-500">(${med.generic_name || 'N/A'})</span></p>
                                     </div>`;
                        });
                    }
                    $('#medicine_results').html(html).removeClass('hidden');
                });
            });

            // Hide dropdowns when clicking outside
            $(document).click(function(e) {
                if(!$(e.target).closest('#medicine_search, #medicine_results').length) {
                    $('#medicine_results').addClass('hidden');
                }
                if(!$(e.target).closest('#customer_search, #customer_results').length) {
                    $('#customer_results').addClass('hidden');
                }
            });

            // --- 2. SELECT MEDICINE -> SHOW BATCHES ---
            $(document).on('click', '.med-result', function() {
                let medId = $(this).data('id');
                let medName = $(this).data('name');
                $('#medicine_search').val('');
                $('#medicine_results').addClass('hidden');

                // Check Stock
                let branchId = $('#pos_branch_id').length ? $('#pos_branch_id').val() : null;
                let payload = {medicine_id: medId};
                if(branchId) payload.branch_id = branchId;

                $.get('/api/stock/check', payload, function(batches) {
                    if(batches.length === 0) {
                        alert('Out of stock for ' + medName + ' in this branch!');
                        return;
                    }

                    $('#batch_modal_title').text('Select Batch for ' + medName);
                    let html = '';
                    batches.forEach(b => {
                        let isExpiring = false;
                        let bgClass = '';
                        if(b.expiry_date) {
                            let exp = new Date(b.expiry_date);
                            let now = new Date();
                            let diffTime = Math.abs(exp - now);
                            let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                            if(diffDays <= 30) {
                                isExpiring = true;
                                bgClass = 'bg-orange-50';
                            }
                        }
                        
                        html += `<tr class="${bgClass}">
                                    <td class="px-4 py-3">${b.batch_number}</td>
                                    <td class="px-4 py-3 ${isExpiring ? 'text-red-500 font-bold' : ''}">${b.expiry_date || 'N/A'} ${isExpiring ? '(!)' : ''}</td>
                                    <td class="px-4 py-3 font-bold">${b.quantity}</td>
                                    <td class="px-4 py-3 text-green-600">${currSym}${parseFloat(b.sale_price).toFixed(2)}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm add-batch-to-cart" 
                                            data-med-id="${medId}" data-med-name="${medName}" 
                                            data-stock-id="${b.id}" data-batch="${b.batch_number}" 
                                            data-price="${b.sale_price}" data-max-qty="${b.quantity}"
                                            data-medicines-per-strip="${b.medicine ? b.medicine.medicines_per_strip : 1}">
                                            Add
                                        </button>
                                    </td>
                                </tr>`;
                    });
                    $('#batch_tbody').html(html);
                    $('#batchModal').removeClass('hidden');
                });
            });

            $('#close_batch_modal').click(function() { $('#batchModal').addClass('hidden'); });

            // --- 3. ADD TO CART ---
            $(document).on('click', '.add-batch-to-cart', function() {
                let stockId = $(this).data('stock-id');
                
                // Check if already in cart
                let existing = cart.find(i => i.stock_id === stockId);
                if(existing) {
                    if(existing.quantity < $(this).data('max-qty')) {
                        existing.quantity++;
                        existing.total = existing.quantity * existing.price;
                    } else {
                        alert('Cannot exceed available stock!');
                    }
                } else {
                    cart.push({
                        stock_id: stockId,
                        medicine_id: $(this).data('med-id'),
                        name: $(this).data('med-name'),
                        batch: $(this).data('batch'),
                        price: parseFloat($(this).data('price')),
                        quantity: 1,
                        max_qty: $(this).data('max-qty'),
                        medicines_per_strip: parseFloat($(this).data('medicines-per-strip')) || 1,
                        total: parseFloat($(this).data('price'))
                    });
                }
                
                $('#batchModal').addClass('hidden');
                renderCart();
            });

            // --- 4. RENDER CART ---
            function renderCart() {
                let html = '';
                subtotal = 0;

                if(cart.length === 0) {
                    html = '<tr id="empty_cart"><td colspan="7" class="px-4 py-4 text-center text-gray-500">Cart is empty</td></tr>';
                } else {
                    cart.forEach((item, index) => {
                        subtotal += item.total;
                        let strips = item.quantity / item.medicines_per_strip;
                        let stripsDisplay = Number.isInteger(strips) ? strips : strips.toFixed(2);
                        html += `<tr>
                                    <td class="px-4 py-3 text-sm font-bold">${item.name}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">${item.batch}</td>
                                    <td class="px-4 py-3 text-sm">${currSym}${item.price.toFixed(2)}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <input type="number" class="w-16 rounded border-gray-300 p-1 text-sm update-strip" data-index="${index}" value="${stripsDisplay}" min="0" step="any">
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <input type="number" class="w-16 rounded border-gray-300 p-1 text-sm update-qty" data-index="${index}" value="${item.quantity}" min="1" max="${item.max_qty}">
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-green-600 row-total">${currSym}${item.total.toFixed(2)}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <button type="button" class="text-red-500 hover:text-red-700 remove-cart-item" data-index="${index}"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>`;
                    });
                }

                $('#cart_body').html(html);
                updateTotals();
            }

            function recalculateCartSubtotal() {
                subtotal = 0;
                cart.forEach(item => subtotal += item.total);
                updateTotals();
            }

            // Update Qty
            $(document).on('input', '.update-qty', function() {
                let index = $(this).data('index');
                let tr = $(this).closest('tr');
                let val = parseInt($(this).val());
                
                if (isNaN(val)) return; // wait for valid input
                
                if(val < 1) val = 1;
                if(val > cart[index].max_qty) {
                    val = cart[index].max_qty;
                    $(this).val(val);
                }
                
                let strips = val / cart[index].medicines_per_strip;
                tr.find('.update-strip').val(Number.isInteger(strips) ? strips : strips.toFixed(2));

                cart[index].quantity = val;
                cart[index].total = cart[index].price * val;
                tr.find('.row-total').text(currSym + cart[index].total.toFixed(2));
                recalculateCartSubtotal();
            });

            // Update Qty on blur to enforce limits if typed quickly
            $(document).on('change', '.update-qty', function() {
                let index = $(this).data('index');
                if (isNaN(parseInt($(this).val())) || parseInt($(this).val()) < 1) {
                    $(this).val(1).trigger('input');
                }
            });

            // Update Strip
            $(document).on('input', '.update-strip', function() {
                let index = $(this).data('index');
                let tr = $(this).closest('tr');
                let strips = parseFloat($(this).val());
                
                if (isNaN(strips)) return; // wait for valid input
                
                if(strips < 0) strips = 0;
                
                let qty = Math.round(strips * cart[index].medicines_per_strip);
                
                if(qty < 1 && strips > 0) qty = 1;
                if(qty < 1) qty = 1;

                if(qty > cart[index].max_qty) {
                    qty = cart[index].max_qty;
                    let adjustedStrip = qty / cart[index].medicines_per_strip;
                    $(this).val(Number.isInteger(adjustedStrip) ? adjustedStrip : adjustedStrip.toFixed(2));
                }
                
                tr.find('.update-qty').val(qty);
                
                cart[index].quantity = qty;
                cart[index].total = cart[index].price * qty;
                tr.find('.row-total').text(currSym + cart[index].total.toFixed(2));
                recalculateCartSubtotal();
            });

            // Update Strip on blur to enforce limits
            $(document).on('change', '.update-strip', function() {
                let index = $(this).data('index');
                if (isNaN(parseFloat($(this).val())) || parseFloat($(this).val()) <= 0) {
                    let minStrips = 1 / cart[index].medicines_per_strip;
                    $(this).val(Number.isInteger(minStrips) ? minStrips : minStrips.toFixed(2)).trigger('input');
                }
            });

            // Remove Item
            $(document).on('click', '.remove-cart-item', function() {
                let index = $(this).data('index');
                cart.splice(index, 1);
                renderCart();
            });

            // --- 5. CALCULATE TOTALS ---
            function updateTotals() {
                $('#summary_subtotal').text(currSym + subtotal.toFixed(2));
                
                let discountPct = parseFloat($('#summary_discount_pct').val()) || 0;
                let sgstPct = parseFloat($('#summary_sgst_pct').val()) || 0;
                let cgstPct = parseFloat($('#summary_cgst_pct').val()) || 0;
                
                let discountAmt = (subtotal * discountPct) / 100;
                let taxableAmount = subtotal - discountAmt;
                
                let sgstAmt = (taxableAmount * sgstPct) / 100;
                let cgstAmt = (taxableAmount * cgstPct) / 100;
                let taxAmt = sgstAmt + cgstAmt;
                
                $('#summary_discount').val(discountAmt.toFixed(2));
                $('#summary_sgst').val(sgstAmt.toFixed(2));
                $('#summary_cgst').val(cgstAmt.toFixed(2));
                $('#summary_tax').val(taxAmt.toFixed(2));
                
                $('#summary_discount_amt_display').text(currSym + discountAmt.toFixed(2));
                $('#summary_sgst_amt_display').text(currSym + sgstAmt.toFixed(2));
                $('#summary_cgst_amt_display').text(currSym + cgstAmt.toFixed(2));
                
                total = subtotal - discountAmt + taxAmt;
                if(total < 0) total = 0;
                
                $('#summary_total').text(currSym + total.toFixed(2));
                
                $('#paid_amount').val(total.toFixed(2));
                
                calculateChange();
            }

            $('#summary_discount_pct, #summary_sgst_pct, #summary_cgst_pct').on('input', updateTotals);
            $('#paid_amount').on('input', calculateChange);

            function calculateChange() {
                let paid = parseFloat($('#paid_amount').val()) || 0;
                let change = paid - total;
                if(change < 0) change = 0;
                $('#change_amount').text(currSym + change.toFixed(2));
            }

            // --- 6. CUSTOMER SEARCH ---
            $('#customer_search').on('input', function() {
                let query = $(this).val();
                if(query.length < 2) {
                    $('#customer_results').addClass('hidden');
                    return;
                }

                $.get('/api/customer/search', {q: query}, function(data) {
                    let html = '';
                    if(data.length === 0) {
                        html = '<div class="p-3 text-gray-500">No customers found.</div>';
                    } else {
                        data.forEach(c => {
                            html += `<div class="p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 cust-result" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone || 'No Phone'}">
                                        <p class="font-bold text-gray-800">${c.name} <span class="text-sm font-normal text-gray-500">(${c.phone || 'No Phone'})</span></p>
                                     </div>`;
                        });
                    }
                    $('#customer_results').html(html).removeClass('hidden');
                });
            });

            $(document).on('click', '.cust-result', function() {
                $('#selected_customer_id').val($(this).data('id'));
                $('#cust_name_disp').text($(this).data('name'));
                $('#cust_phone_disp').text($(this).data('phone'));
                
                $('#customer_search').val('').hide();
                $('#toggle_new_customer').hide();
                $('#customer_results').addClass('hidden');
                $('#selected_customer_div').removeClass('hidden');
            });

            $('#toggle_new_customer').click(function() {
                $('#customer_search').val('').hide();
                $('#toggle_new_customer').hide();
                $('#selected_customer_div').addClass('hidden');
                $('#selected_customer_id').val('');
                $('#new_customer_div').removeClass('hidden');
                $('#new_customer_name').focus();
            });

            $('#cancel_new_customer').click(function() {
                $('#new_customer_div').addClass('hidden');
                $('#new_customer_name').val('');
                $('#new_customer_phone').val('');
                $('#new_customer_address').val('');
                $('#customer_search').show();
                $('#toggle_new_customer').show();
            });

            $('#remove_customer').click(function() {
                $('#selected_customer_id').val('');
                $('#selected_customer_div').addClass('hidden');
                $('#customer_search').val('').show();
                $('#toggle_new_customer').show();
            });

            // --- 7. COMPLETE SALE ---
            $('#complete_sale').click(function() {
                if(cart.length === 0) {
                    alert("Cart is empty!");
                    return;
                }

                let paid = parseFloat($('#paid_amount').val()) || 0;
                // Use a small epsilon to account for JS floating point precision issues
                if((total - paid) > 0.005) {
                    alert("Paid amount is less than total amount!\nTotal: " + total.toFixed(2) + "\nPaid: " + paid.toFixed(2));
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');

                let payload = {
                    branch_id: $('#pos_branch_id').length ? $('#pos_branch_id').val() : null,
                    customer_id: $('#selected_customer_id').val() || null,
                    new_customer_name: $('#new_customer_name').val(),
                    new_customer_phone: $('#new_customer_phone').val(),
                    new_customer_address: $('#new_customer_address').val(),
                    doctor_name: $('#doctor_name').val(),
                    doctor_address: $('#doctor_address').val(),
                    sale_date: $('#sale_date').val(),
                    subtotal: subtotal.toFixed(2),
                    discount: parseFloat($('#summary_discount').val()) || 0,
                    tax: parseFloat($('#summary_tax').val()) || 0,
                    total_amount: total.toFixed(2),
                    paid_amount: paid.toFixed(2),
                    change_amount: Math.max(0, paid - total).toFixed(2),
                    payment_method: $('#payment_method').val(),
                    items: cart.map(i => ({
                        medicine_id: i.medicine_id,
                        stock_id: i.stock_id,
                        quantity: i.quantity,
                        sale_price: i.price,
                        total: i.total
                    }))
                };

                $.ajax({
                    url: '/sales',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(response) {
                        alert(response.message + " Invoice: " + response.invoice_number);
                        window.open('/invoice/' + response.sale_id + '/print', '_blank');
                        window.location.reload();
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                        $('#complete_sale').prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> Complete Sale');
                    }
                });
            });
        });
    </script>
</x-admin-layout>
