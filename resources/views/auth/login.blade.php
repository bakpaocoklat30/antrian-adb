<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - di-SAPA</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-sans p-4">

    <div class="bg-white p-8 md:p-10 rounded-2xl shadow-xl w-full max-w-md border border-gray-200 relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-2 bg-blue-600"></div>

        <div class="text-center mb-8">
            <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo ADB" class="w-24 h-24 mx-auto mb-4 object-contain drop-shadow-sm">
            
            <h1 class="text-3xl font-black text-gray-800">Login Petugas</h1>
            <p class="text-gray-500 mt-2 text-sm font-semibold">Sistem Antrian Pelayanan ADB (di-SAPA)</p>
        </div>

        <form method="POST" action="{{ url('/login') }}" class="space-y-5">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm font-bold flex items-center gap-2 animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1" for="email">Email Petugas</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Masukkan email..." class="pl-10 w-full p-3 bg-gray-50 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1" for="password">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" required placeholder="Masukkan password..." class="pl-10 pr-10 w-full p-3 bg-gray-50 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePasswordVisibility('password', 'eyeIcon')">
                        <i id="eyeIcon" class="fa-solid fa-eye text-gray-400 hover:text-blue-600 transition text-lg"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-lg py-3 rounded-lg transition shadow-lg mt-4 flex justify-center items-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> MASUK
            </button>
        </form>

        <div class="mt-5 text-center">
            <button type="button" onclick="lupaPassword()" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">
                <i class="fa-solid fa-circle-question mr-1"></i> Lupa Password?
            </button>
        </div>

        <div class="text-center mt-6 pt-6 border-t border-gray-100 text-xs text-gray-400 font-semibold">
            &copy; 2026 TEKAD Dev.<br>SMK Negeri 1 Adiwerna
        </div>
    </div>

    <script>
        // FUNGSI TOGGLE ICON MATA
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
                eyeIcon.classList.add("text-blue-600"); 
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
                eyeIcon.classList.remove("text-blue-600"); 
            }
        }

        // SCRIPT SWEETALERT LUPA PASSWORD
        function lupaPassword() {
            Swal.fire({
                title: '<span class="text-red-600 font-black">Lupa Password?</span>',
                html: `
                    <p class="text-sm text-gray-600 mb-4">Untuk alasan keamanan, reset sandi petugas OSIS/Loket hanya dapat dilakukan oleh Koordinator/Super Admin melalui menu Manajemen Akun.</p>
                    <a href="https://wa.me/6285890000074?text=Halo%20Admin,%20saya%20lupa%20password%20akun%20petugas%20di-SAPA%20saya.%20Mohon%20bantuannya%20untuk%20mereset." target="_blank" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-5 rounded-xl shadow-md transition mb-2 w-full border-b-4 border-green-700">
                        <i class="fa-brands fa-whatsapp text-xl align-middle mr-2"></i> Hubungi WA Admin
                    </a>
                `,
                icon: 'warning',
                showConfirmButton: false,
                showCloseButton: true,
                width: '400px'
            });
        }
    </script>
</body>
</html>