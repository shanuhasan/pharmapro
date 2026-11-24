<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>M PharmaPro — Pharmacy Management, Refined</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo-mark.svg') }}">
        <meta name="description" content="M PharmaPro is a complete pharmacy management suite: fast POS billing, batch-wise inventory with expiry tracking, purchases, returns, multi-branch control and insightful reports.">
        <meta name="theme-color" content="#0b1120">
        <meta property="og:title" content="M PharmaPro — Pharmacy Management, Refined">
        <meta property="og:description" content="Fast POS, batch-wise stock, expiry alerts, multi-branch control and clear reports.">
        <meta property="og:type" content="website">

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|instrument-serif:400,400i&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

        @vite(['resources/css/app.css'])

        <style>
            :root { --ink: #0b1120; --line: rgba(148, 163, 184, .12); }
            body { background: var(--ink); color: #cbd5e1; }
            .font-display { font-family: 'Instrument Serif', ui-serif, Georgia, serif; letter-spacing: -.01em; }
            .hairline { border-color: var(--line); }
            .text-gradient {
                background: linear-gradient(100deg, #5eead4 0%, #a7f3d0 45%, #fde68a 100%);
                -webkit-background-clip: text; background-clip: text; color: transparent;
            }
            .bg-grain {
                background-image:
                    radial-gradient(60rem 40rem at 50% -10%, rgba(20, 184, 166, .18), transparent 60%),
                    radial-gradient(40rem 30rem at 100% 20%, rgba(16, 185, 129, .08), transparent 60%),
                    linear-gradient(to right, rgba(148,163,184,.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(148,163,184,.05) 1px, transparent 1px);
                background-size: auto, auto, 56px 56px, 56px 56px;
            }
            .mask-fade { -webkit-mask-image: linear-gradient(to bottom, #000 60%, transparent); mask-image: linear-gradient(to bottom, #000 60%, transparent); }
            .card {
                background: linear-gradient(180deg, rgba(30, 41, 59, .55), rgba(15, 23, 42, .55));
                border: 1px solid var(--line);
                box-shadow: inset 0 1px 0 rgba(255,255,255,.04);
            }
            .card:hover { border-color: rgba(94, 234, 212, .28); }
            .btn-primary {
                background: linear-gradient(180deg, #2dd4bf, #0d9488);
                box-shadow: 0 1px 0 rgba(255,255,255,.35) inset, 0 10px 30px -10px rgba(20, 184, 166, .7);
            }
            .btn-primary:hover { filter: brightness(1.08); }
            .reveal { opacity: 0; transform: translateY(16px); transition: opacity .7s ease, transform .7s ease; }
            .reveal.is-visible { opacity: 1; transform: none; }
            @media (prefers-reduced-motion: reduce) {
                .reveal { opacity: 1; transform: none; transition: none; }
                html { scroll-behavior: auto; }
            }
        </style>
    </head>
    <body class="font-sans antialiased selection:bg-teal-500/30 selection:text-white min-h-screen flex flex-col">

        <!-- Navbar -->
        <header class="fixed inset-x-0 top-0 z-50 border-b hairline bg-[#0b1120]/70 backdrop-blur-xl">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 h-16 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <x-brand-mark class="w-9 h-9 drop-shadow-[0_6px_14px_rgba(13,148,136,.45)]" />
                    <span class="font-semibold text-lg tracking-tight text-white">M PharmaPro</span>
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm text-slate-400">
                    <a href="#features" class="hover:text-white transition-colors">Features</a>
                    <a href="#workflow" class="hover:text-white transition-colors">How it works</a>
                    <a href="#security" class="hover:text-white transition-colors">Security</a>
                </nav>

                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition">
                            Dashboard <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white border hairline bg-white/5 hover:bg-white/10 transition">
                            Log in
                        </a>
                    @endauth
                @endif
            </div>
        </header>

        <main class="flex-grow">
            <!-- Hero -->
            <section class="relative overflow-hidden bg-grain pt-36 pb-20 lg:pt-44 lg:pb-28">
                <div class="relative max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-14 items-center">
                    <div class="lg:col-span-6 reveal">
                        <span class="inline-flex items-center gap-2 rounded-full border hairline bg-white/5 px-3 py-1 text-xs font-medium text-teal-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Built for modern pharmacies
                        </span>
                        <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl leading-[1.02] text-white mt-6">
                            Run your pharmacy<br>with <em class="text-gradient">quiet precision.</em>
                        </h1>
                        <p class="mt-6 text-lg text-slate-400 max-w-xl leading-relaxed">
                            Billing, batch-wise stock, expiry alerts, purchases and reports — one calm, fast workspace for every branch you run.
                        </p>

                        <div class="mt-10 flex flex-col sm:flex-row gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="btn-primary inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-semibold text-white transition">
                                        Open Dashboard <i class="fas fa-arrow-right text-sm"></i>
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn-primary inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-semibold text-white transition">
                                        <i class="fas fa-lock text-sm"></i> Secure Login
                                    </a>
                                @endauth
                            @endif
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-slate-200 border hairline hover:bg-white/5 transition">
                                Explore features
                            </a>
                        </div>

                        <dl class="mt-12 grid grid-cols-3 max-w-md divide-x divide-slate-800 text-sm">
                            <div class="pr-4"><dt class="text-slate-500">Billing</dt><dd class="text-white font-medium mt-1">PDF · Print · Email</dd></div>
                            <div class="px-4"><dt class="text-slate-500">Stock</dt><dd class="text-white font-medium mt-1">Batch &amp; expiry</dd></div>
                            <div class="pl-4"><dt class="text-slate-500">Scale</dt><dd class="text-white font-medium mt-1">Multi-branch</dd></div>
                        </dl>
                    </div>

                    <!-- Product preview (illustrative) -->
                    <div class="lg:col-span-6 reveal" aria-hidden="true">
                        <div class="relative">
                            <div class="absolute -inset-6 bg-teal-500/10 blur-3xl rounded-[2rem]"></div>
                            <div class="relative card rounded-2xl p-2 shadow-2xl shadow-black/50">
                                <div class="flex items-center gap-1.5 px-3 py-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                                    <span class="ml-3 text-[11px] text-slate-500">Point of Sale</span>
                                </div>
                                <div class="rounded-xl bg-[#0e1628] border hairline p-5 grid sm:grid-cols-5 gap-5">
                                    <div class="sm:col-span-3 space-y-2.5">
                                        <div class="flex items-center gap-2 rounded-lg border hairline bg-white/[.03] px-3 py-2 text-xs text-slate-500">
                                            <i class="fas fa-search"></i> Search medicine or batch…
                                        </div>
                                        @foreach ([['Paracetamol 650', 'B-2291', '2 × 30.00', false], ['Amoxicillin 500', 'B-1180', '1 × 112.50', false], ['Cetirizine 10', 'B-0947', '3 × 18.00', true]] as $row)
                                            <div class="flex items-center justify-between rounded-lg bg-white/[.03] px-3 py-2.5">
                                                <div>
                                                    <div class="text-sm text-slate-200">{{ $row[0] }}</div>
                                                    <div class="text-[11px] text-slate-500">Batch {{ $row[1] }}
                                                        @if ($row[3]) <span class="ml-1 text-amber-300/90">· expires soon</span> @endif
                                                    </div>
                                                </div>
                                                <div class="text-xs text-slate-400 tabular-nums">{{ $row[2] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="sm:col-span-2 rounded-lg border hairline p-4 flex flex-col">
                                        <div class="text-[11px] uppercase tracking-wider text-slate-500">Invoice</div>
                                        <div class="mt-3 space-y-1.5 text-xs tabular-nums">
                                            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="text-slate-300">₹226.50</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Discount</span><span class="text-slate-300">−₹6.50</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Tax</span><span class="text-slate-300">₹0.00</span></div>
                                        </div>
                                        <div class="mt-4 pt-3 border-t hairline flex justify-between items-baseline">
                                            <span class="text-xs text-slate-400">Total</span>
                                            <span class="text-xl font-semibold text-white tabular-nums">₹220.00</span>
                                        </div>
                                        <div class="mt-auto pt-4">
                                            <div class="rounded-md bg-gradient-to-b from-teal-400 to-teal-600 text-center text-xs font-semibold text-white py-2">Complete sale</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden sm:flex absolute -bottom-6 -left-6 card rounded-xl px-4 py-3 items-center gap-3 shadow-xl shadow-black/40">
                                <span class="w-8 h-8 rounded-lg bg-amber-400/10 text-amber-300 grid place-items-center"><i class="fas fa-hourglass-half text-sm"></i></span>
                                <div>
                                    <div class="text-xs text-white font-medium">Expiry alert</div>
                                    <div class="text-[11px] text-slate-500">Batches expiring within 30 days</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="relative py-24 lg:py-32 border-t hairline">
                <div class="max-w-7xl mx-auto px-5 sm:px-8">
                    <div class="max-w-2xl reveal">
                        <p class="text-sm font-medium text-teal-300">Everything in one place</p>
                        <h2 class="font-display text-4xl sm:text-5xl text-white mt-3 leading-tight">The essentials of a pharmacy, thoughtfully engineered.</h2>
                    </div>

                    <div class="mt-14 grid md:grid-cols-6 gap-4">
                        <article class="card rounded-2xl p-7 md:col-span-4 transition-colors reveal">
                            <i class="fas fa-cash-register text-teal-300 text-xl"></i>
                            <h3 class="text-white font-semibold text-lg mt-5">Lightning-fast POS</h3>
                            <p class="text-slate-400 mt-2 max-w-lg">Search by medicine or batch, sell from the right stock lot, and issue invoices as PDF, print or email in seconds — with walk-in or registered customers and doctor details.</p>
                        </article>
                        <article class="card rounded-2xl p-7 md:col-span-2 transition-colors reveal">
                            <i class="fas fa-layer-group text-emerald-300 text-xl"></i>
                            <h3 class="text-white font-semibold text-lg mt-5">Batch-wise stock</h3>
                            <p class="text-slate-400 mt-2">Every batch tracked with expiry and prices, and every movement logged.</p>
                        </article>
                        <article class="card rounded-2xl p-7 md:col-span-2 transition-colors reveal">
                            <i class="fas fa-file-import text-sky-300 text-xl"></i>
                            <h3 class="text-white font-semibold text-lg mt-5">Smart purchase import</h3>
                            <p class="text-slate-400 mt-2">Upload distributor bills and stock is updated automatically.</p>
                        </article>
                        <article class="card rounded-2xl p-7 md:col-span-2 transition-colors reveal">
                            <i class="fas fa-code-branch text-violet-300 text-xl"></i>
                            <h3 class="text-white font-semibold text-lg mt-5">Multi-branch control</h3>
                            <p class="text-slate-400 mt-2">Transfer stock between branches and see the whole business at a glance.</p>
                        </article>
                        <article class="card rounded-2xl p-7 md:col-span-2 transition-colors reveal">
                            <i class="fas fa-rotate-left text-amber-300 text-xl"></i>
                            <h3 class="text-white font-semibold text-lg mt-5">Returns, done right</h3>
                            <p class="text-slate-400 mt-2">Sale and purchase returns that keep stock accurate.</p>
                        </article>
                        <article class="card rounded-2xl p-7 md:col-span-6 transition-colors reveal">
                            <div class="grid md:grid-cols-2 gap-8 items-center">
                                <div>
                                    <i class="fas fa-chart-line text-teal-300 text-xl"></i>
                                    <h3 class="text-white font-semibold text-lg mt-5">Reports that answer questions</h3>
                                    <p class="text-slate-400 mt-2">Sales, purchases, stock, expiry, customers, medicine-wise sales and profit &amp; loss — filter by date or branch and export to Excel.</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['Sales', 'Purchases', 'Stock', 'Expiry', 'Customers', 'Medicine sales', 'Profit & Loss', 'Expenses'] as $tag)
                                        <span class="rounded-full border hairline bg-white/[.03] px-3 py-1.5 text-sm text-slate-300">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Workflow -->
            <section id="workflow" class="py-24 lg:py-32 border-t hairline">
                <div class="max-w-7xl mx-auto px-5 sm:px-8">
                    <div class="max-w-2xl reveal">
                        <p class="text-sm font-medium text-teal-300">How it works</p>
                        <h2 class="font-display text-4xl sm:text-5xl text-white mt-3 leading-tight">From supplier to shelf to customer.</h2>
                    </div>
                    <ol class="mt-14 grid md:grid-cols-3 gap-10">
                        @foreach ([
                            ['01', 'Purchase', 'Record or import supplier bills. Batches, expiry dates and prices go straight into stock.'],
                            ['02', 'Sell', 'Bill at the counter with the POS. Stock is deducted from the exact batch sold.'],
                            ['03', 'Understand', 'Watch expiry, low stock and profit with reports built for daily decisions.'],
                        ] as $step)
                            <li class="reveal">
                                <div class="font-display text-5xl text-teal-300/70">{{ $step[0] }}</div>
                                <div class="mt-4 h-px bg-gradient-to-r from-teal-400/40 to-transparent"></div>
                                <h3 class="text-white font-semibold text-lg mt-5">{{ $step[1] }}</h3>
                                <p class="text-slate-400 mt-2">{{ $step[2] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <!-- Security + CTA -->
            <section id="security" class="py-24 lg:py-32 border-t hairline">
                <div class="max-w-7xl mx-auto px-5 sm:px-8">
                    <div class="relative overflow-hidden rounded-3xl card p-10 sm:p-14 reveal">
                        <div class="absolute -top-24 -right-24 w-96 h-96 bg-teal-500/15 blur-3xl rounded-full"></div>
                        <div class="relative grid lg:grid-cols-2 gap-10 items-center">
                            <div>
                                <h2 class="font-display text-4xl sm:text-5xl text-white leading-tight">Private by design.<br><span class="text-slate-400">Ready when you are.</span></h2>
                                <ul class="mt-8 space-y-3 text-slate-300">
                                    <li class="flex gap-3"><i class="fas fa-check text-teal-300 mt-1"></i> Each pharmacy's data is fully isolated</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-teal-300 mt-1"></i> Role-based access for admins, managers and cashiers</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-teal-300 mt-1"></i> Complete audit trail for every stock change</li>
                                </ul>
                            </div>
                            <div class="lg:justify-self-end flex flex-col sm:flex-row lg:flex-col gap-3 w-full sm:w-auto">
                                @if (Route::has('login'))
                                    @auth
                                        <a href="{{ url('/dashboard') }}" class="btn-primary inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl font-semibold text-white transition">
                                            Go to Dashboard <i class="fas fa-arrow-right text-sm"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="btn-primary inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl font-semibold text-white transition">
                                            <i class="fas fa-lock text-sm"></i> Log in to your pharmacy
                                        </a>
                                    @endauth
                                @endif
                                <a href="https://musheeda.com/" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-semibold text-slate-200 border hairline hover:bg-white/5 transition">
                                    Request access
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t hairline">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
                <div class="flex items-center gap-2">
                    <x-brand-mark class="w-6 h-6" />
                    <span class="text-slate-300 font-medium">M PharmaPro</span>
                </div>
                <p>&copy; {{ date('Y') }} M PharmaPro. Developed by <a href="https://musheeda.com/" class="text-teal-300 hover:text-teal-200 transition-colors">Musheeda</a>.</p>
            </div>
        </footer>

        <script>
            (function () {
                var els = document.querySelectorAll('.reveal');
                if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('is-visible'); }); return; }
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); } });
                }, { rootMargin: '0px 0px -10% 0px' });
                els.forEach(function (e) { io.observe(e); });
            })();
        </script>
    </body>
</html>
