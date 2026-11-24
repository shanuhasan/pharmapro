<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Point of Sale') }}</h2>
            <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Register open
            </span>
        </div>
    </x-slot>

    @php($cur = setting('currency_symbol', '₹'))

    <style>
        .pos-card { background:#fff; border:1px solid #eef2f6; border-radius:18px; box-shadow:0 1px 2px rgba(15,23,42,.04),0 6px 20px -8px rgba(15,23,42,.10); }
        .pos-label { display:block; font-size:.6875rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#64748b; margin-bottom:.4rem; }
        .pos-input { display:block; width:100%; border-radius:10px; border:1px solid #d8dee6; background:#fff; padding:.6rem .8rem; font-size:.875rem; color:#0f172a; }
        .pos-input::placeholder { color:#94a3b8; }
        .pos-dropdown { position:absolute; z-index:40; left:0; right:0; margin-top:.4rem; background:#fff; border:1px solid #e2e8f0; border-radius:14px; box-shadow:0 18px 40px -14px rgba(15,23,42,.3); max-height:20rem; overflow-y:auto; padding:.35rem; }
        .pos-result { display:flex; align-items:center; gap:.75rem; padding:.6rem .7rem; border-radius:10px; cursor:pointer; }
        .pos-result:hover { background:#f0fdfa; }
        .pay-opt { flex:1; display:flex; flex-direction:column; align-items:center; gap:.3rem; padding:.65rem .5rem; border:1px solid #e2e8f0; border-radius:12px; font-size:.75rem; font-weight:600; color:#475569; background:#fff; transition:all .15s; }
        .pay-opt i { font-size:1rem; color:#94a3b8; transition:color .15s; }
        .pay-opt:hover { border-color:#99f6e4; }
        .pay-opt.is-active { border-color:#0d9488; background:#f0fdfa; color:#0f766e; box-shadow:0 0 0 3px rgba(13,148,136,.12); }
        .pay-opt.is-active i { color:#0d9488; }
        .app-theme .app-main #cart_table thead th { background:#f8fafc; }
        .app-theme .app-main #cart_table input[type="number"] { width:4.5rem; padding:.35rem .5rem; text-align:center; border-radius:8px; }
        .pct-input { width:4.25rem !important; padding:.3rem .5rem !important; text-align:right; font-size:.8125rem !important; border-radius:8px !important; }
        kbd.pos-kbd { font-family:inherit; font-size:.6875rem; font-weight:600; color:#64748b; background:#f1f5f9; border:1px solid #e2e8f0; border-bottom-width:2px; border-radius:6px; padding:.05rem .4rem; }
    </style>

    <div class="py-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

            <!-- ============ Left: search, cart, bill details ============ -->
            <div class="xl:col-span-8 space-y-6">

                @if(auth()->user()->role === 'admin')
                <div class="pos-card px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex items-center gap-3 sm:w-1/2">
                        <span class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 grid place-items-center shrink-0"><i class="fas fa-code-branch"></i></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Active branch</p>
                            <p class="text-xs text-slate-500">Stock and invoice will use this branch</p>
                        </div>
                    </div>
                    <div class="sm:w-1/2">
                        <select id="pos_branch_id" class="pos-input">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Search + Cart -->
                <div class="pos-card">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <div class="relative">
                            <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-teal-600"></i>
                            <input type="text" id="medicine_search" autocomplete="off"
                                   class="pos-input !py-3.5 !pl-11 !pr-20 !text-base !rounded-xl !border-slate-200 bg-slate-50/60"
                                   placeholder="Search medicine by name or generic (e.g. Paracetamol)…">
                            <span class="hidden sm:block absolute right-4 top-1/2 -translate-y-1/2"><kbd class="pos-kbd">F2</kbd></span>
                            <div id="medicine_results" class="pos-dropdown hidden">
                                <!-- Results populate here -->
                            </div>
                        </div>
                    </div>

                    <div class="px-5 sm:px-6 pt-5 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-basket-shopping text-teal-600"></i> Current Cart
                            <span id="cart_count" class="ml-1 inline-flex items-center justify-center min-w-[1.5rem] h-6 px-2 rounded-full bg-teal-50 text-teal-700 text-xs font-bold">0</span>
                        </h3>
                        <span class="text-xs text-slate-400 hidden sm:block">Edit strips or quantity inline</span>
                    </div>

                    <div class="p-5 sm:p-6 pt-3 overflow-x-auto">
                        <table class="min-w-full" id="cart_table">
                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Batch</th>
                                    <th>Price</th>
                                    <th class="!text-center">Strip</th>
                                    <th class="!text-center">Qty</th>
                                    <th class="!text-right">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cart_body">
                                <tr id="empty_cart"><td colspan="7">
                                    <div class="py-12 text-center">
                                        <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 grid place-items-center text-slate-300 text-2xl"><i class="fas fa-cart-shopping"></i></div>
                                        <p class="mt-3 text-sm font-semibold text-slate-700">Cart is empty</p>
                                        <p class="text-xs text-slate-400 mt-1">Search a medicine above to start billing</p>
                                    </div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bill details -->
                <div class="pos-card p-5 sm:p-6">
                    <h3 class="text-base font-semibold text-slate-800 flex items-center gap-2 mb-5"><i class="fas fa-file-invoice text-teal-600"></i> Bill Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Customer -->
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="pos-label">Customer</span>
                                <button type="button" id="toggle_new_customer" class="text-xs font-semibold text-teal-700 hover:text-teal-800 mb-1.5"><i class="fas fa-plus text-[10px]"></i> Add manually</button>
                            </div>
                            <div class="relative">
                                <input type="text" id="customer_search" autocomplete="off" class="pos-input" placeholder="Search name or phone…">
                                <div id="customer_results" class="pos-dropdown hidden">
                                    <!-- Results populate here -->
                                </div>
                            </div>

                            <div id="new_customer_div" class="hidden mt-1 space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-slate-700">New customer</span>
                                    <button type="button" id="cancel_new_customer" class="text-xs text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i> Cancel</button>
                                </div>
                                <input type="text" id="new_customer_name" class="pos-input" placeholder="Customer name">
                                <input type="text" id="new_customer_phone" class="pos-input" placeholder="Phone (optional)">
                                <textarea id="new_customer_address" class="pos-input" placeholder="Address (optional)" rows="2"></textarea>
                            </div>

                            <div id="selected_customer_div" class="hidden mt-1 rounded-xl border border-teal-200 bg-teal-50/70 p-3 flex justify-between items-center gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-400 to-emerald-600 text-white grid place-items-center shrink-0"><i class="fas fa-user text-xs"></i></span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-teal-900 truncate" id="cust_name_disp"></p>
                                        <p class="text-xs text-teal-700" id="cust_phone_disp"></p>
                                    </div>
                                </div>
                                <button type="button" id="remove_customer" class="w-7 h-7 rounded-lg text-teal-700 hover:bg-white hover:text-rose-600 grid place-items-center"><i class="fas fa-times"></i></button>
                            </div>
                            <input type="hidden" id="selected_customer_id">
                            <p class="mt-2 text-[11px] text-slate-400">Leave empty for a walk-in customer.</p>
                        </div>

                        <!-- Doctor -->
                        <div class="space-y-2">
                            <span class="pos-label">Doctor <span class="normal-case tracking-normal font-normal text-slate-400">(optional)</span></span>
                            <input type="text" id="doctor_name" class="pos-input" placeholder="Doctor name">
                            <textarea id="doctor_address" class="pos-input" placeholder="Doctor address / clinic" rows="2"></textarea>
                        </div>

                        <!-- Invoice -->
                        <div>
                            <span class="pos-label">Invoice date</span>
                            <input type="date" id="sale_date" class="pos-input" value="{{ date('Y-m-d') }}">
                            <p class="mt-2 text-[11px] text-slate-400">Invoice number is generated automatically.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ Right: payment panel ============ -->
            <div class="xl:col-span-4 xl:sticky xl:top-[5.5rem]">
                <div class="pos-card overflow-hidden">
                    <!-- Grand total -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-900 to-teal-900 px-6 py-6 text-white">
                        <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-teal-400/20 blur-2xl"></div>
                        <p class="relative text-[11px] font-semibold uppercase tracking-[.16em] text-teal-200/80">Amount payable</p>
                        <p class="relative mt-1 text-4xl font-bold tracking-tight tabular-nums" id="summary_total">{{ $cur }}0.00</p>
                        <div class="relative mt-4 flex items-center justify-between text-sm">
                            <span class="text-slate-300">Change due</span>
                            <span class="font-semibold text-amber-300 tabular-nums" id="change_amount">{{ $cur }}0.00</span>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- Breakdown -->
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-semibold text-slate-800 tabular-nums" id="summary_subtotal">{{ $cur }}0.00</span>
                            </div>

                            <div class="flex justify-between items-center gap-3">
                                <span class="text-slate-500">Discount</span>
                                <div class="flex items-center gap-2">
                                    <div class="relative"><input type="number" id="summary_discount_pct" value="0" min="0" max="100" step="any" class="pct-input pos-input !pr-6"><span class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] text-slate-400">%</span></div>
                                    <span class="w-24 text-right font-semibold text-rose-600 tabular-nums" id="summary_discount_amt_display">{{ $cur }}0.00</span>
                                    <input type="hidden" id="summary_discount" value="0">
                                </div>
                            </div>

                            <div class="flex justify-between items-center gap-3">
                                <span class="text-slate-500">SGST</span>
                                <div class="flex items-center gap-2">
                                    <div class="relative"><input type="number" id="summary_sgst_pct" value="{{ setting('sgst_percentage', 0) }}" min="0" step="any" class="pct-input pos-input !pr-6"><span class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] text-slate-400">%</span></div>
                                    <span class="w-24 text-right font-semibold text-slate-700 tabular-nums" id="summary_sgst_amt_display">{{ $cur }}0.00</span>
                                    <input type="hidden" id="summary_sgst" value="0">
                                </div>
                            </div>

                            <div class="flex justify-between items-center gap-3">
                                <span class="text-slate-500">CGST</span>
                                <div class="flex items-center gap-2">
                                    <div class="relative"><input type="number" id="summary_cgst_pct" value="{{ setting('cgst_percentage', 0) }}" min="0" step="any" class="pct-input pos-input !pr-6"><span class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] text-slate-400">%</span></div>
                                    <span class="w-24 text-right font-semibold text-slate-700 tabular-nums" id="summary_cgst_amt_display">{{ $cur }}0.00</span>
                                    <input type="hidden" id="summary_cgst" value="0">
                                </div>
                            </div>
                            <input type="hidden" id="summary_tax" value="0">
                            <p class="text-[11px] text-slate-400">Prices are tax-inclusive; GST is shown from the net amount.</p>
                        </div>

                        <div class="h-px bg-slate-100"></div>

                        <!-- Payment method -->
                        <div>
                            <span class="pos-label">Payment method</span>
                            <select id="payment_method" class="hidden">
                                <option value="cash">Cash</option>
                                <option value="card">Credit Card</option>
                                <option value="online">Online Transfer</option>
                            </select>
                            <div class="flex gap-2" role="radiogroup" aria-label="Payment method">
                                <button type="button" class="pay-opt is-active" data-method="cash" role="radio" aria-checked="true"><i class="fas fa-money-bill-wave"></i>Cash</button>
                                <button type="button" class="pay-opt" data-method="card" role="radio" aria-checked="false"><i class="far fa-credit-card"></i>Card</button>
                                <button type="button" class="pay-opt" data-method="online" role="radio" aria-checked="false"><i class="fas fa-qrcode"></i>Online</button>
                            </div>
                        </div>

                        <!-- Paid amount -->
                        <div>
                            <span class="pos-label">Amount received</span>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-semibold">{{ $cur }}</span>
                                <input type="number" id="paid_amount" value="0" min="0" class="pos-input !pl-9 !py-3 !text-lg font-bold text-right tabular-nums">
                            </div>
                        </div>

                        <button type="button" id="complete_sale" class="w-full inline-flex items-center justify-center gap-2 rounded-xl py-3.5 px-4 text-base font-semibold text-white bg-gradient-to-b from-teal-500 to-teal-600 shadow-lg shadow-teal-600/30 hover:brightness-105 active:translate-y-px transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="fas fa-check-circle"></i> Complete Sale
                        </button>
                        <p class="text-center text-[11px] text-slate-400">The invoice opens for printing after checkout.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Selection Modal -->
    <div id="batchModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="batch_modal_title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm" aria-hidden="true" id="batch_backdrop"></div>
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 grid place-items-center"><i class="fas fa-layer-group"></i></span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900" id="batch_modal_title">Select Batch</h3>
                            <p class="text-xs text-slate-500">Choose the batch to bill from. Batches expiring soon are highlighted.</p>
                        </div>
                    </div>
                    <button type="button" class="w-8 h-8 grid place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" onclick="document.getElementById('close_batch_modal').click()" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <div class="px-6 py-4 overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Expiry</th>
                                <th>Available</th>
                                <th>Sale Price</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="batch_tbody">
                            <!-- Batches populate here -->
                        </tbody>
                    </table>
                </div>
                <div class="bg-slate-50 px-6 py-3 flex justify-end">
                    <button type="button" id="close_batch_modal" class="inline-flex justify-center rounded-lg border border-slate-200 px-4 py-2 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        const currSym = @json(setting('currency_symbol', '₹'));
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const emptyCartHtml = $('#empty_cart').prop('outerHTML');
        
        $(document).ready(function() {
            // Setup CSRF
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            let cart = [];
            let subtotal = 0;
            let total = 0;

            // Payment method buttons drive the (hidden) select
            $('.pay-opt').on('click', function() {
                $('.pay-opt').removeClass('is-active').attr('aria-checked', 'false');
                $(this).addClass('is-active').attr('aria-checked', 'true');
                $('#payment_method').val($(this).data('method'));
            });

            // Keyboard shortcuts: F2 = search medicine, Esc = close batch picker
            $(document).on('keydown', function(e) {
                if (e.key === 'F2') { e.preventDefault(); $('#medicine_search').trigger('focus'); }
                if (e.key === 'Escape' && !$('#batchModal').hasClass('hidden')) { $('#batchModal').addClass('hidden'); }
            });
            $('#batch_backdrop').on('click', function() { $('#batchModal').addClass('hidden'); });

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
                        html = '<div class="px-3 py-6 text-center text-sm text-slate-500"><i class="fas fa-circle-info text-slate-300 mr-1"></i> No medicines found.</div>';
                    } else {
                        data.forEach(med => {
                            html += `<div class="pos-result med-result" data-id="${esc(med.id)}" data-name="${esc(med.name)}">
                                        <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 grid place-items-center shrink-0"><i class="fas fa-capsules text-sm"></i></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-slate-800 truncate">${esc(med.name)}</p>
                                            <p class="text-xs text-slate-500 truncate">${esc(med.generic_name || 'N/A')}</p>
                                        </div>
                                        <i class="fas fa-plus text-xs text-slate-300"></i>
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

                    $('#batch_modal_title').text('Select batch · ' + medName);
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
                                bgClass = 'bg-amber-50/60';
                            }
                        }
                        
                        html += `<tr class="${bgClass}">
                                    <td><span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 rounded-md px-2 py-1">${esc(b.batch_number || '—')}</span></td>
                                    <td>${isExpiring
                                        ? `<span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-xs font-semibold"><i class="fas fa-triangle-exclamation text-[10px]"></i> ${esc(b.expiry_date)}</span>`
                                        : `<span class="text-slate-600">${esc(b.expiry_date || 'N/A')}</span>`}</td>
                                    <td class="font-semibold text-slate-800 tabular-nums">${esc(b.quantity)}</td>
                                    <td class="font-semibold text-teal-700 tabular-nums">${currSym}${parseFloat(b.sale_price).toFixed(2)}</td>
                                    <td class="text-right">
                                        <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-b from-teal-500 to-teal-600 hover:brightness-105 text-white px-3 py-1.5 text-xs font-semibold shadow-sm add-batch-to-cart"
                                            data-med-id="${esc(medId)}" data-med-name="${esc(medName)}"
                                            data-stock-id="${esc(b.id)}" data-batch="${esc(b.batch_number)}"
                                            data-price="${esc(b.sale_price)}" data-max-qty="${esc(b.quantity)}"
                                            data-medicines-per-strip="${b.medicine ? esc(b.medicine.medicines_per_strip) : 1}">
                                            <i class="fas fa-plus text-[10px]"></i> Add
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
                    html = emptyCartHtml;
                } else {
                    cart.forEach((item, index) => {
                        subtotal += item.total;
                        let strips = item.quantity / item.medicines_per_strip;
                        let stripsDisplay = Number.isInteger(strips) ? strips : strips.toFixed(2);
                        html += `<tr>
                                    <td>
                                        <p class="font-semibold text-slate-800">${esc(item.name)}</p>
                                        <p class="text-[11px] text-slate-400">${item.medicines_per_strip} per strip · max ${item.max_qty}</p>
                                    </td>
                                    <td><span class="font-mono text-xs text-slate-600 bg-slate-100 rounded-md px-2 py-1">${esc(item.batch || '—')}</span></td>
                                    <td class="tabular-nums text-slate-600">${currSym}${item.price.toFixed(2)}</td>
                                    <td class="text-center">
                                        <input type="number" class="rounded-md border-gray-300 text-sm update-strip" data-index="${index}" value="${stripsDisplay}" min="0" step="any">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" class="rounded-md border-gray-300 text-sm update-qty" data-index="${index}" value="${item.quantity}" min="1" max="${item.max_qty}">
                                    </td>
                                    <td class="text-right font-semibold text-slate-900 tabular-nums row-total">${currSym}${item.total.toFixed(2)}</td>
                                    <td class="text-right">
                                        <button type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 grid place-items-center ml-auto remove-cart-item" data-index="${index}" title="Remove"><i class="fas fa-trash-can text-sm"></i></button>
                                    </td>
                                </tr>`;
                    });
                }

                $('#cart_body').html(html);
                $('#cart_count').text(cart.length);
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
                let cart_total = 0;
                cart.forEach(item => cart_total += item.total);
                
                let discountPct = parseFloat($('#summary_discount_pct').val()) || 0;
                let sgstPct = parseFloat($('#summary_sgst_pct').val()) || 0;
                let cgstPct = parseFloat($('#summary_cgst_pct').val()) || 0;
                let total_tax_pct = sgstPct + cgstPct;
                
                let discountAmt = (cart_total * discountPct) / 100;
                let net_total = cart_total - discountAmt;
                
                let taxable_value = net_total / (1 + (total_tax_pct / 100));
                let taxAmt = net_total - taxable_value;
                let sgstAmt = taxAmt / 2;
                let cgstAmt = taxAmt / 2;
                
                subtotal = cart_total - taxAmt;
                
                $('#summary_subtotal').text(currSym + cart_total.toFixed(2));
                
                $('#summary_discount').val(discountAmt.toFixed(2));
                $('#summary_sgst').val(sgstAmt.toFixed(2));
                $('#summary_cgst').val(cgstAmt.toFixed(2));
                $('#summary_tax').val(taxAmt.toFixed(2));
                
                $('#summary_discount_amt_display').text(currSym + discountAmt.toFixed(2));
                $('#summary_sgst_amt_display').text(currSym + sgstAmt.toFixed(2));
                $('#summary_cgst_amt_display').text(currSym + cgstAmt.toFixed(2));
                
                total = net_total;
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
                        html = '<div class="px-3 py-5 text-center text-sm text-slate-500">No customers found.</div>';
                    } else {
                        data.forEach(c => {
                            html += `<div class="pos-result cust-result" data-id="${esc(c.id)}" data-name="${esc(c.name)}" data-phone="${esc(c.phone || 'No Phone')}">
                                        <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 grid place-items-center shrink-0 text-xs font-bold">${esc((c.name || '?').charAt(0).toUpperCase())}</span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-800 truncate">${esc(c.name)}</p>
                                            <p class="text-xs text-slate-500">${esc(c.phone || 'No Phone')}</p>
                                        </div>
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

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing…');

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
                        $('#complete_sale').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Complete Sale');
                    }
                });
            });
        });
    </script>
</x-admin-layout>
