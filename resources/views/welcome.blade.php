<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>M PharmaPro - Modern Pharmacy Management</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <!-- FontAwesome -->
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                background-color: #0f172a; /* Slate 900 */
                color: #e2e8f0; /* Slate 200 */
            }
            .glass-nav {
                background: rgba(15, 23, 42, 0.8);
                backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(10deg); }
            }
            @keyframes float-reverse {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-25px) rotate(-10deg); }
            }
            @keyframes pulse-glow {
                0%, 100% { box-shadow: 0 0 15px rgba(13, 148, 136, 0.4); }
                50% { box-shadow: 0 0 30px rgba(13, 148, 136, 0.8); }
            }
            .anim-float-1 { animation: float 6s ease-in-out infinite; }
            .anim-float-2 { animation: float-reverse 7s ease-in-out infinite; }
            .anim-float-3 { animation: float 5s ease-in-out infinite; }
            .anim-glow { animation: pulse-glow 3s infinite; }
            
            /* Medical Grid Pattern */
            .bg-medical-grid {
                background-size: 40px 40px;
                background-image: linear-gradient(to right, rgba(13, 148, 136, 0.05) 1px, transparent 1px),
                                  linear-gradient(to bottom, rgba(13, 148, 136, 0.05) 1px, transparent 1px);
            }
        </style>
    </head>
    <body class="font-sans antialiased selection:bg-medical-primary selection:text-white relative min-h-screen flex flex-col">
        
        <!-- Navbar -->
        <nav class="fixed w-full z-50 glass-nav transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-medical-primary to-medical-success flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-plus-square text-xl"></i>
                        </div>
                        <span class="font-bold text-2xl tracking-tight text-white">M PharmaPro</span>
                    </div>
                    <div>
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold rounded-full text-white bg-medical-primary hover:bg-teal-600 transition-all duration-300 anim-glow">
                                    Dashboard <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 border border-medical-primary text-sm font-semibold rounded-full text-medical-primary hover:bg-medical-primary hover:text-white transition-all duration-300">
                                    Log in
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow flex flex-col justify-center relative overflow-hidden bg-medical-grid pt-20">
            <!-- Background Gradient Glow -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-medical-primary opacity-20 blur-[120px] rounded-full pointer-events-none"></div>

            <!-- Floating Medical Elements -->
            <div class="absolute top-1/4 left-[10%] text-teal-400 opacity-30 text-5xl anim-float-1 pointer-events-none hidden md:block"><i class="fas fa-pills"></i></div>
            <div class="absolute top-1/3 right-[15%] text-emerald-400 opacity-20 text-6xl anim-float-2 pointer-events-none hidden md:block"><i class="fas fa-plus"></i></div>
            <div class="absolute bottom-1/4 left-[20%] text-blue-400 opacity-20 text-4xl anim-float-3 pointer-events-none hidden md:block"><i class="fas fa-heartbeat"></i></div>
            <div class="absolute bottom-1/3 right-[10%] text-teal-300 opacity-30 text-5xl anim-float-1 pointer-events-none hidden md:block"><i class="fas fa-prescription-bottle-alt"></i></div>

            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10 py-20 lg:py-32">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-slate-800 border border-slate-700 text-teal-300 text-sm font-medium mb-8">
                    <span class="flex h-2 w-2 rounded-full bg-medical-success mr-2 animate-pulse"></span>
                    Modern Pharmacy System
                </div>
                
                <h1 class="text-5xl md:text-7xl font-extrabold text-white tracking-tight leading-tight mb-8">
                    Simplify Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-medical-primary to-emerald-400">Pharmacy</span>
                </h1>
                
                <p class="text-xl md:text-2xl text-slate-400 mb-12 max-w-3xl mx-auto font-light leading-relaxed">
                    A clean, smart, and secure platform to manage inventory, process sales, and grow your pharmacy business efficiently.
                </p>
                
                <div class="flex justify-center">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-8 py-4 text-lg font-bold rounded-full text-white bg-gradient-to-r from-medical-primary to-medical-success hover:scale-105 transform transition-all duration-300 flex items-center justify-center anim-glow shadow-lg shadow-teal-500/30">
                                Open Dashboard <i class="fas fa-arrow-right ml-3"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-8 py-4 text-lg font-bold rounded-full text-white bg-gradient-to-r from-medical-primary to-medical-success hover:scale-105 transform transition-all duration-300 flex items-center justify-center anim-glow shadow-lg shadow-teal-500/30">
                                Secure Login <i class="fas fa-lock ml-3"></i>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>

            <!-- Simple Features -->
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24 z-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-6 hover:border-medical-primary transition-colors duration-300 text-center group">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-700 flex items-center justify-center text-medical-primary text-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">Smart Inventory</h3>
                        <p class="text-slate-400 text-sm">Track medicines, manage stock limits, and monitor expiry dates easily.</p>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-6 hover:border-medical-success transition-colors duration-300 text-center group">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-700 flex items-center justify-center text-medical-success text-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">Fast POS</h3>
                        <p class="text-slate-400 text-sm">Quick billing interface designed for high-speed pharmacy checkouts.</p>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-6 hover:border-blue-400 transition-colors duration-300 text-center group">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-700 flex items-center justify-center text-blue-400 text-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">Clear Reports</h3>
                        <p class="text-slate-400 text-sm">Understand your sales, profits, and stock movement instantly.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 border-t border-slate-800 py-6 text-center text-slate-500 text-sm relative z-20">
            <p>&copy; {{ date('Y') }} M PharmaPro. Developed by <a href="https://musheeda.com/" class="text-medical-primary hover:text-teal-400 transition-colors">Musheeda</a>.</p>
        </footer>
    </body>
</html>
