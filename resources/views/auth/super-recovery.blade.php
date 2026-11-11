<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Darurat: Recovery Super Admin</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen font-sans p-4">

    <div class="bg-white p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-md border-t-8 border-red-600 relative overflow-hidden">
        
        <div class="text-center mb-6">
            <div class="mx-auto bg-red-100 w-16 h-16 flex items-center justify-center rounded-full mb-4">
                <i class="fa-solid fa-user-shield text-3xl text-red-600"></i>
            </div>
            <h1 class="text-2xl font-black text-gray-800 uppercase tracking-widest">Recovery Darurat</h1>
            <p class="text-red-500 mt-2 text-xs font-bold bg-red-50 py-2 rounded-lg border border-red-100">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Khusus reset sandi Super Admin.
            </p>
        </div>

        <form method="POST" action="{{ route('super.recovery.process') }}" class="space-y-4">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-xs font-bold mb-4">
                    @foreach ($errors->all() as $error) <div>- {{ $error }}</div> @endforeach
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm font-bold flex items-center gap-2 mb-4 animate-pulse">
                    <i class="fa-solid fa-shield-virus"></i> {{ session('error') }}
                </div>
            @endif

            <div>
                <label class="block text-xs font-black tracking-wider text-gray-600 uppercase mb-1" for="pin">PIN Akses Darurat (.env)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fa-solid fa-key text-gray-400"></i></div>
                    <input type="password" id="pin" name="pin" required autofocus placeholder="Masukkan 6 digit PIN..." class="pl-10 w-full p-3 bg-gray-50 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-red-500 transition font-mono tracking-[0.3em] text-center text-lg">
                </div>
            </div>

            <div class="pt-2 border-t mt-4">
                <label class="block text-xs font-black tracking-wider text-gray-600 uppercase mb-1" for="new_password">Password Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fa-solid fa-lock text-gray-400"></i></div>
                    
                    <input type="password" id="new_password" name="new_password" required placeholder="Minimal 6 karakter" class="pl-10 pr-10 w-full p-3 bg-gray-50 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition">
                    
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePasswordVisibility('new_password', 'eyeIcon1')">
                        <i id="eyeIcon1" class="fa-solid fa-eye text-gray-400 hover:text-blue-600 transition text-lg"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black tracking-wider text-gray-600 uppercase mb-1" for="new_password_confirmation">Ulangi Password Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fa-solid fa-check-double text-gray-400"></i></div>
                    
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required placeholder="Ketik ulang password baru" class="pl-10 pr-10 w-full p-3 bg-gray-50 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition">
                    
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePasswordVisibility('new_password_confirmation', 'eyeIcon2')">
                        <i id="eyeIcon2" class="fa-solid fa-eye text-gray-400 hover:text-blue-600 transition text-lg"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-3 rounded-lg transition shadow-lg mt-6 flex justify-center items-center gap-2 border-b-4 border-red-800">
                <i class="fa-solid fa-bolt"></i> RESET PAKSA SEKARANG
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ url('/login') }}" class="text-xs font-bold text-gray-400 hover:text-gray-600"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login Petugas</a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
                eyeIcon.classList.add("text-blue-600"); // Ubah warna saat terlihat
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
                eyeIcon.classList.remove("text-blue-600"); // Kembalikan abu-abu
            }
        }
    </script>
</body>
</html>