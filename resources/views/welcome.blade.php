<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Welcome to PharmaPro</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="text-center p-8 bg-white rounded-lg shadow-md">
            <h1 class="text-4xl font-bold text-gray-800 mb-8">Welcome to PharmaPro</h1>
            
            @if (Route::has('login'))
                <div class="space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-md font-medium hover:bg-blue-700 transition duration-150 ease-in-out">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-md font-medium hover:bg-blue-700 transition duration-150 ease-in-out">
                            Log in
                        </a>

                        <!-- @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-block bg-white text-blue-600 border border-blue-600 px-6 py-2.5 rounded-md font-medium hover:bg-blue-50 transition duration-150 ease-in-out">
                                Register
                            </a>
                        @endif -->
                    @endauth
                </div>
            @endif
        </div>
    </body>
</html>
