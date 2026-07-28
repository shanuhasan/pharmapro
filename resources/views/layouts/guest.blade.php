<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>M PharmaPro | Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <!-- FontAwesome -->
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                background-color: #0f172a; /* Slate 900 */
                color: #e2e8f0; /* Slate 200 */
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(10deg); }
            }
            @keyframes float-reverse {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-25px) rotate(-10deg); }
            }
            .anim-float-1 { animation: float 6s ease-in-out infinite; }
            .anim-float-2 { animation: float-reverse 7s ease-in-out infinite; }
            
            /* Medical Grid Pattern */
            .bg-medical-grid {
                background-size: 40px 40px;
                background-image: linear-gradient(to right, rgba(13, 148, 136, 0.05) 1px, transparent 1px),
                                  linear-gradient(to bottom, rgba(13, 148, 136, 0.05) 1px, transparent 1px);
            }

            /* Dark Theme Overrides for Laravel Breeze Auth Components */
            .auth-card {
                background: rgba(30, 41, 59, 0.8) !important; /* slate-800 */
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
                border-radius: 1rem !important;
                padding: 2.5rem !important; /* Force padding */
                margin-top: 1.5rem;
                width: 100%;
                max-width: 28rem; /* sm:max-w-md */
                box-sizing: border-box;
            }
            .auth-card label, .auth-card span {
                color: #e2e8f0 !important; /* slate-200 */
                font-weight: 500;
                margin-bottom: 0.5rem;
                display: block;
            }
            .auth-card input[type="text"],
            .auth-card input[type="email"],
            .auth-card input[type="password"] {
                background-color: rgba(15, 23, 42, 0.6) !important; /* slate-900 with opacity */
                border: 1px solid #475569 !important; /* slate-600 */
                color: #f8fafc !important; /* slate-50 */
                padding: 0.75rem 1rem !important;
                border-radius: 0.5rem !important;
                width: 100%;
                box-sizing: border-box;
                margin-top: 0.25rem;
                transition: all 0.2s ease;
            }
            .auth-card input:focus {
                border-color: #14b8a6 !important; /* teal-500 */
                box-shadow: 0 0 0 2px rgba(20, 184, 166, 0.2) !important;
                outline: none;
            }
            .auth-card input[type="checkbox"] {
                background-color: rgba(15, 23, 42, 0.6) !important;
                border: 1px solid #475569 !important;
                border-radius: 0.25rem !important;
            }
            .auth-card input[type="checkbox"]:checked {
                background-color: #14b8a6 !important;
                border-color: #14b8a6 !important;
            }
            .auth-card .mt-4 {
                margin-top: 1.25rem !important;
            }
            .auth-card button {
                background: linear-gradient(to right, #0d9488, #10b981) !important; /* medical-primary to success */
                border: none !important;
                color: white !important;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 700;
                transition: all 0.3s ease;
                box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.3);
                padding: 0.75rem 1.5rem !important;
                border-radius: 0.5rem !important;
                cursor: pointer;
            }
            .auth-card button:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 15px -3px rgba(13, 148, 136, 0.4);
            }
            .auth-card a {
                color: #94a3b8 !important; /* slate-400 */
                transition: color 0.3s ease;
                text-decoration: none;
            }
            .auth-card a:hover {
                color: #2dd4bf !important; /* teal-400 */
            }
        </style>
    </head>
    <body class="font-sans text-gray-200 antialiased relative min-h-screen flex flex-col justify-center items-center overflow-hidden bg-medical-grid">
        
        <!-- Background Gradient Glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-medical-primary opacity-20 blur-[120px] rounded-full pointer-events-none"></div>

        <!-- Floating Medical Elements -->
        <div class="absolute top-1/4 left-[15%] text-teal-400 opacity-20 text-5xl anim-float-1 pointer-events-none hidden md:block"><i class="fas fa-pills"></i></div>
        <div class="absolute bottom-1/4 right-[15%] text-emerald-400 opacity-20 text-6xl anim-float-2 pointer-events-none hidden md:block"><i class="fas fa-plus"></i></div>
        <div class="absolute top-1/3 right-[20%] text-blue-400 opacity-10 text-4xl anim-float-1 pointer-events-none hidden md:block"><i class="fas fa-heartbeat"></i></div>

        <!-- Logo -->
        <div class="relative z-10 text-center mb-4">
            <a href="/" class="flex flex-col items-center gap-4 group">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-medical-primary to-medical-success flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-plus-square text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white">M PharmaPro</h1>
            </a>
        </div>

        <!-- Auth Card -->
        <div class="auth-card relative z-10 mx-4">
            {{ $slot }}
        </div>
        
    </body>
</html>
