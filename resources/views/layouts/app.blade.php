<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>di-SAPA | Sistem Antrian Pelayanan ADB</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { overflow-x: hidden; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans text-gray-800">

    @php
        $brandUrl = url('/');
        if (Auth::check()) {
            $brandUrl = Auth::user()->role === 'scanner' ? route('admin.scanner') : route('admin.dashboard');
        }
    @endphp

    <nav class="bg-blue-600 text-white shadow-md relative z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-6">
                <a href="{{ $brandUrl }}" class="text-2xl font-black tracking-wider flex items-center gap-3 hover:opacity-80 transition cursor-pointer" title="Kembali ke Halaman Utama">
                    <div class="bg-white p-0.5 rounded-full shadow-sm flex items-center justify-center">
                        <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo ADB" class="w-8 h-8 rounded-full object-cover">
                    </div>
                    di-SAPA
                </a>
            </div>
            <div class="text-sm font-bold text-blue-200 hidden md:block">
                Sistem Antrian Pelayanan ADB
            </div>
        </div>
    </nav>

    <main class="flex-1 flex flex-col items-center justify-center p-4 md:p-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm font-semibold text-gray-500">
            &copy; {{ date('Y') }} TEKAD Dev - SMK Negeri 1 Adiwerna. <br class="md:hidden"> All Rights Reserved.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
