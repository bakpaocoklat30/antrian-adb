@extends('layouts.app')
@section('content')
<div class="min-h-screen w-full bg-gray-50 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl w-full mx-auto bg-white rounded-[2rem] shadow-xl border border-gray-200 overflow-hidden h-fit my-auto">
        
        <!-- HEADER -->
        <div class="bg-blue-700 p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 pointer-events-none"></div>
            <h2 class="relative z-10 text-2xl font-black text-white tracking-wide uppercase">Formulir Daftar Ulang</h2>
            <p class="relative z-10 text-blue-200 text-sm mt-1">Lengkapi data di bawah ini untuk mencetak dokumen.</p>
        </div>

        <div class="p-8">
            <div id="formSection">
                
                <!-- DATA KELULUSAN (TERKUNCI) -->
                <div class="mb-8 bg-gray-50 p-5 rounded-2xl border border-gray-200">
                    <h4 class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-4 flex items-center">
                        <i class="fa-solid fa-lock mr-2 text-gray-400"></i> Data Kelulusan (Terkunci)
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">KODE LOGIN</label>
                            <div class="font-mono font-bold text-gray-800 text-md mt-1">{{ $siswa->nisn }}</div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Lengkap</label>
                            <div class="font-black text-gray-800 uppercase text-md mt-1">{{ $siswa->nama }}</div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jalur Masuk</label>
                            <div class="font-black text-gray-800 uppercase text-md mt-1">{{ $siswa->jalur_pendaftaran }}</div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-blue-400 uppercase tracking-wider">Jurusan Diterima</label>
                            <div class="font-black text-blue-700 text-md mt-1 uppercase">{{ $siswa->jurusan }}</div>
                        </div>
                    </div>
                </div>

                <form id="biodataForm" onsubmit="submitBiodata(event)">
                    @csrf
                    <!-- Tetap kirimkan kode login original ke controller untuk identifikasi primary key -->
                    <input type="hidden" name="nisn" value="{{ $siswa->nisn }}">
                    
                    <input type="hidden" name="latitude" id="lat_input" value="">
                    <input type="hidden" name="longitude" id="lng_input" value="">
                    
                    <!-- BAGIAN I: IDENTITAS DIRI -->
                    <div class="space-y-5 mb-8">
                        <h3 class="text-sm font-black text-gray-800 uppercase border-b pb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-mono text-xs">I</span> Identitas Diri
                        </h3>

                        <div class="md:col-span-2 mt-2 mb-4">
                            <label class="block text-xs font-bold text-gray-700 mb-2">Upload Pas Foto 3x4 Berwarna (Resmi) *</label>
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-4 bg-blue-50/50 p-4 rounded-xl border border-blue-200 border-dashed">
                                <div id="foto_preview_container" class="hidden w-[90px] h-[120px] bg-white border-2 border-gray-300 shadow-sm shrink-0 rounded overflow-hidden">
                                    <img id="foto_preview" src="" alt="Preview" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 w-full">
                                    <input type="file" id="foto_upload" accept="image/jpeg, image/png, image/jpg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition cursor-pointer">
                                    <p class="text-[10px] text-gray-500 mt-2 font-medium leading-relaxed">
                                        <b class="text-blue-600">Sistem Pintar:</b> Format JPG/PNG. Foto otomatis dipotong (Crop) 3x4 dan langsung tercetak di PDF! <b class="text-gray-900 font-black">Pakai baju OSIS SMP asal.</b>
                                    </p>
                                    <input type="hidden" name="pas_foto" id="pas_foto_base64">
                                </div>
                            </div>
                        </div>
                        
                        <!-- 🔥 SMART AUTO-DETECT ENGINE (PHP) 🔥 -->
                        @php
                            $isNisnPure = preg_match('/^[0-9]{10}$/', $siswa->nisn);
                            $autoFillNisn = $isNisnPure ? $siswa->nisn : '';
                            $autoFillPend = !$isNisnPure ? $siswa->nisn : '';
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">NISN Asli (10 Digit Angka) *</label>
                                <input type="text" name="nisn_asli" value="{{ $autoFillNisn }}" required maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0,10)" placeholder="10 Digit Angka" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition font-mono bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">No Pendaftaran (16 Karakter) *</label>
                                <input type="text" name="no_pendaftaran" value="{{ $autoFillPend }}" required maxlength="16" oninput="this.value=this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0,16)" placeholder="Cth: 12345ABCD" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition font-mono bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">NIK Murid (16 Digit) *</label>
                                <input type="text" name="nik" required maxlength="16" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0,16)" placeholder="KTP/KK Siswa" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div><label class="block text-xs font-bold text-gray-700 mb-2">Nama Panggilan *</label><input type="text" name="nama_panggilan" required oninput="this.value = this.value.replace(/[^a-zA-Z\s\'\.\,]/g, '')" placeholder="" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase"></div>
                            <div><label class="block text-xs font-bold text-gray-700 mb-2">Tempat, Tgl Lahir *</label><input type="text" name="ttl" required placeholder="Tegal, 15 Agustus 2010" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase"></div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">Jenis Kelamin *</label>
                                <select name="jenis_kelamin" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
                                    <option value="">-- Pilih Jenis Kelamin--</option>
                                    <option value="Laki-Laki">Laki-Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">Agama *</label>
                                <select name="agama" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
                                    <option value="">-- Pilih Agama --</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen Protestan">Kristen Protestan</option>
                                    <option value="Kristen Katolik">Kristen Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Khonghucu">Khonghucu</option>
                                    <option value="Kepercayaan Terhadap Tuhan YME">Kepercayaan Terhadap Tuhan YME</option>
                                </select>
                            </div>
                        </div>

                        <div><label class="block text-xs font-bold text-gray-700 mb-2">Alamat Lengkap (Desa, RT/RW, Kec, Kab) *</label><textarea name="alamat" rows="2" required placeholder="" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase"></textarea></div>
                        
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-700 mb-2">No HP/WA Aktif *</label>
                            <input type="text" name="no_hp" required oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkHpLive();" placeholder="" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                            <div id="hp_warning" class="hidden mt-2 p-2 bg-red-50 border border-red-200 text-red-600 rounded-lg text-[10px] font-bold shadow-sm animate-fade-in flex items-center">
                                <i class="fa-solid fa-triangle-exclamation mr-2 text-lg"></i> Nomor HP Murid SAMA dengan HP Orang Tua/Wali. Silakan bedakan untuk melanjutkan!
                            </div>
                        </div>
                        
                        <div><label class="block text-xs font-bold text-gray-700 mb-2">Riwayat Penyakit (Isi jika ada)</label><input type="text" name="riwayat_kesehatan" value="-" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-2">Prestasi yang pernah diraih (Opsional)</label><input type="text" name="prestasi" value="-" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase"></div>
                    </div>

                    <!-- BAGIAN II: LATAR BELAKANG KELUARGA -->
                    <div class="space-y-5 mb-8">
                        <h3 class="text-sm font-black text-gray-800 uppercase border-b pb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-mono text-xs">II</span> Latar Belakang Keluarga
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">Saat ini tinggal bersama *</label>
                                <select id="tinggal_dengan_select" name="tinggal_dengan" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition bg-gray-50 cursor-pointer">
                                    <option value="" selected disabled>-- Pilih Status Tinggal --</option>
                                    <option value="Bersama orang tua">Bersama orang tua</option>
                                    <option value="Wali">Wali</option>
                                    <option value="Kost">Kost</option>
                                    <option value="Asrama">Asrama</option>
                                    <option value="Panti asuhan">Panti asuhan</option>
                                    <option value="Pesantren">Pesantren</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <input type="text" id="tinggal_dengan_input" class="hidden mt-2 w-full border border-blue-300 rounded-xl px-4 py-3 text-sm uppercase focus:ring-2 focus:ring-blue-500 outline-none transition bg-blue-50" placeholder="Ketik status lainnya...">
                            </div>

                            <div>
                                <!-- Limit HTML Maks 3 Saudara -->
                                <label class="block text-xs font-bold text-gray-700 mb-2">Jumlah Saudara Kandung *</label>
                                <input type="number" name="jumlah_saudara" id="jumlah_saudara" required min="0" max="3" oninput="if(this.value > 3) this.value = 3;" value="0" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                            </div>
                        </div>

                        <div id="saudara_container" class="hidden bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <label class="block text-xs font-bold text-blue-800 mb-3"><i class="fa-solid fa-list-ol mr-1"></i> Data Saudara Kandung</label>
                            <div id="saudara_list" class="space-y-3"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">Hobi *</label>
                                <select id="hobi_select" name="hobi" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition bg-gray-50 cursor-pointer">
                                    <option value="" selected disabled>-- Pilih Hobi --</option>
                                    <option value="Belanja">Belanja</option>
                                    <option value="Berkemah">Berkemah</option>
                                    <option value="Berlari">Berlari</option>
                                    <option value="Bermain Biola">Bermain Biola</option>
                                    <option value="Bermain Bola">Bermain Bola</option>
                                    <option value="Bermain Bola Tenis">Bermain Bola Tenis</option>
                                    <option value="Bermain Boneka">Bermain Boneka</option>
                                    <option value="Bermain Bulu Tangkis">Bermain Bulu Tangkis</option>
                                    <option value="Bermain Gitar">Bermain Gitar</option>
                                    <option value="Bermain Musik">Bermain Musik</option>
                                    <option value="Bermain Piano">Bermain Piano</option>
                                    <option value="Berselancar">Berselancar</option>
                                    <option value="Fitness">Fitness</option>
                                    <option value="Fotografi">Fotografi</option>
                                    <option value="Jogging">Jogging</option>
                                    <option value="Kesenian">Kesenian</option>
                                    <option value="Main Puzzle">Main Puzzle</option>
                                    <option value="Makan">Makan</option>
                                    <option value="Memancing">Memancing</option>
                                    <option value="Membaca">Membaca</option>
                                    <option value="Mendaki">Mendaki</option>
                                    <option value="Menggambar">Menggambar</option>
                                    <option value="Menjahit">Menjahit</option>
                                    <option value="Menulis">Menulis</option>
                                    <option value="Mewarnai">Mewarnai</option>
                                    <option value="Olah Raga">Olah Raga</option>
                                    <option value="Traveling">Traveling</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <input type="text" id="hobi_input" class="hidden mt-2 w-full border border-blue-300 rounded-xl px-4 py-3 text-sm uppercase focus:ring-2 focus:ring-blue-500 outline-none transition bg-blue-50" placeholder="Ketik Hobi Lainnya...">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">Cita-Cita *</label>
                                <select id="cita_cita_select" name="cita_cita" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition bg-gray-50 cursor-pointer">
                                    <option value="" selected disabled>-- Pilih Cita-Cita --</option>
                                    <option value="Arsitek">Arsitek</option>
                                    <option value="Astronot">Astronot</option>
                                    <option value="Atlet">Atlet</option>
                                    <option value="Atlet E-Sport Profesional">Atlet E-Sport Profesional</option>
                                    <option value="Atlit Olahraga">Atlit Olahraga</option>
                                    <option value="Bidan">Bidan</option>
                                    <option value="Content Creator">Content Creator</option>
                                    <option value="Da'i / Ustadz">Da'i / Ustadz</option>
                                    <option value="Designer">Designer</option>
                                    <option value="Dokter">Dokter</option>
                                    <option value="Entertainer / Pekerja Seni">Entertainer / Pekerja Seni</option>
                                    <option value="Guru/Dosen">Guru/Dosen</option>
                                    <option value="Koki">Koki</option>
                                    <option value="Masinis Kereta Api">Masinis Kereta Api</option>
                                    <option value="Pegawai Negeri Sipil / PNS">Pegawai Negeri Sipil / PNS</option>
                                    <option value="Pelaut">Pelaut</option>
                                    <option value="Pemadam Kebakaran">Pemadam Kebakaran</option>
                                    <option value="Pembalap">Pembalap</option>
                                    <option value="Pembawa Acara / Master Ceremony">Pembawa Acara / Master Ceremony</option>
                                    <option value="Pendeta">Pendeta</option>
                                    <option value="Pengacara">Pengacara</option>
                                    <option value="Penghafal Al-Qur'an">Penghafal Al-Qur'an</option>
                                    <option value="Pengusaha / Bisnismen">Pengusaha / Bisnismen</option>
                                    <option value="Penulis">Penulis</option>
                                    <option value="Penyiar Radio">Penyiar Radio</option>
                                    <option value="Perawat">Perawat</option>
                                    <option value="Perawat / Suster">Perawat / Suster</option>
                                    <option value="Pilot">Pilot</option>
                                    <option value="PNS">PNS</option>
                                    <option value="Polisi">Polisi</option>
                                    <option value="Politikus">Politikus</option>
                                    <option value="Presiden">Presiden</option>
                                    <option value="Seni/Lukis/Artis/Sejenis">Seni/Lukis/Artis/Sejenis</option>
                                    <option value="TNI/Polri">TNI/Polri</option>
                                    <option value="Translator">Translator</option>
                                    <option value="Vloger">Vloger</option>
                                    <option value="Wartawan">Wartawan</option>
                                    <option value="Wiraswasta">Wiraswasta</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <input type="text" id="cita_cita_input" class="hidden mt-2 w-full border border-blue-300 rounded-xl px-4 py-3 text-sm uppercase focus:ring-2 focus:ring-blue-500 outline-none transition bg-blue-50" placeholder="Ketik Cita-Cita Lainnya...">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Teman Dekat di SMKN 1 Adiwerna (Kosongi jika tidak ada)</label>
                            <div class="flex gap-3">
                                <div class="w-2/3">
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Nama Teman</label>
                                    <input type="text" name="teman_nama" placeholder="" oninput="this.value = this.value.replace(/[^a-zA-Z\s\'\.\,]/g, '')" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase">
                                </div>
                                <div class="w-1/3">
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">No HP / WA</label>
                                    <input type="text" name="teman_hp" placeholder="" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN III: IDENTITAS ORANG TUA / WALI -->
                    <div class="space-y-5 mb-8">
                        <h3 class="text-sm font-black text-gray-800 uppercase border-b pb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-mono text-xs">III</span> Identitas Orang Tua / Wali
                        </h3>
                        
                        <!-- DATA AYAH -->
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 mb-4">
                            <h4 class="text-xs font-black text-gray-700 mb-4 uppercase border-l-4 border-blue-500 pl-3">Data Ayah</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Ayah *</label>
                                    <input type="text" name="ayah_nama" required oninput="this.value = this.value.replace(/[^a-zA-Z\s\'\.\,]/g, '')" placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs uppercase bg-white outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Hubungan Keluarga *</label>
                                    <select id="ayah_hubungan_select" name="ayah_hubungan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none font-bold text-blue-700 cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Status --</option>
                                        <option value="Ayah Kandung">Ayah Kandung</option>
                                        <option value="Ayah Tiri">Ayah Tiri</option>
                                        <option value="Ayah Angkat">Ayah Angkat</option>
                                        <option value="Ayah Asuh">Ayah Asuh</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="ayah_hubungan_input" class="hidden mt-2 w-full border border-blue-300 p-2.5 rounded-lg text-xs uppercase bg-blue-50 outline-none" placeholder="Ketik manual...">
                                </div>
                                
                                <div><label class="block text-[11px] font-bold text-gray-600 mb-1">Umur (Tahun) *</label><input type="number" name="ayah_umur" required placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none"></div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Agama *</label>
                                    <select name="ayah_agama" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Agama --</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen Protestan">Kristen Protestan</option>
                                        <option value="Kristen Katolik">Kristen Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Khonghucu">Khonghucu</option>
                                        <option value="Kepercayaan Terhadap Tuhan YME">Kepercayaan Terhadap Tuhan YME</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Pendidikan Terakhir *</label>
                                    <select id="ayah_pendidikan_select" name="ayah_pendidikan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Pendidikan --</option>
                                        <option value="D1">D1</option>
                                        <option value="D2">D2</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="Informal">Informal</option>
                                        <option value="Non formal">Non formal</option>
                                        <option value="Paket A">Paket A</option>
                                        <option value="Paket B">Paket B</option>
                                        <option value="Paket C">Paket C</option>
                                        <option value="PAUD">PAUD</option>
                                        <option value="Profesi">Profesi</option>
                                        <option value="Putus SD">Putus SD</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S2 Terapan">S2 Terapan</option>
                                        <option value="S3">S3</option>
                                        <option value="S3 Terapan">S3 Terapan</option>
                                        <option value="SD / sederajat">SD / sederajat</option>
                                        <option value="SMA / sederajat">SMA / sederajat</option>
                                        <option value="SMP / sederajat">SMP / sederajat</option>
                                        <option value="Sp-1">Sp-1</option>
                                        <option value="Sp-2">Sp-2</option>
                                        <option value="Tidak sekolah">Tidak sekolah</option>
                                        <option value="TK / sederajat">TK / sederajat</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="ayah_pendidikan_input" class="hidden mt-2 w-full border border-blue-300 p-2.5 rounded-lg text-xs uppercase bg-blue-50 outline-none" placeholder="Ketik manual...">
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Pekerjaan *</label>
                                    <select id="ayah_pekerjaan_select" name="ayah_pekerjaan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Pekerjaan --</option>
                                        <option value="Tidak bekerja">Tidak bekerja</option>
                                        <option value="Nelayan">Nelayan</option>
                                        <option value="Petani">Petani</option>
                                        <option value="Peternak">Peternak</option>
                                        <option value="PNS/TNI/Polri">PNS/TNI/Polri</option>
                                        <option value="Karyawan Swasta">Karyawan Swasta</option>
                                        <option value="Pedagang Kecil">Pedagang Kecil</option>
                                        <option value="Pedagang Besar">Pedagang Besar</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Wirausaha">Wirausaha</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Pensiunan">Pensiunan</option>
                                        <option value="Tenaga Kerja Indonesia">Tenaga Kerja Indonesia</option>
                                        <option value="Karyawan BUMN">Karyawan BUMN</option>
                                        <option value="Tidak dapat diterapkan">Tidak dapat diterapkan</option>
                                        <option value="Sudah Meninggal">Sudah Meninggal</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="ayah_pekerjaan_input" class="hidden mt-2 w-full border border-blue-300 p-2.5 rounded-lg text-xs uppercase bg-blue-50 outline-none" placeholder="Ketik manual...">
                                </div>
                                
                                <div><label class="block text-[11px] font-bold text-gray-600 mb-1">No HP/WA *</label><input type="text" name="ayah_hp" required oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkHpLive();" placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none"></div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Penghasilan /Bulan *</label>
                                    <select id="ayah_penghasilan_select" name="ayah_penghasilan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Penghasilan --</option>
                                        <option value="Kurang dari Rp. 500,000">Kurang dari Rp. 500,000</option>
                                        <option value="Rp. 500,000 - Rp. 999,999">Rp. 500,000 - Rp. 999,999</option>
                                        <option value="Rp. 1,000,000 - Rp. 1,999,999">Rp. 1,000,000 - Rp. 1,999,999</option>
                                        <option value="Rp. 2,000,000 - Rp. 4,999,999">Rp. 2,000,000 - Rp. 4,999,999</option>
                                        <option value="Rp. 5,000,000 - Rp. 20,000,000">Rp. 5,000,000 - Rp. 20,000,000</option>
                                        <option value="Lebih dari Rp. 20,000,000">Lebih dari Rp. 20,000,000</option>
                                        <option value="Tidak Berpenghasilan">Tidak Berpenghasilan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <!-- Terkunci Input Angka -->
                                    <input type="text" id="ayah_penghasilan_input" class="hidden mt-2 w-full border border-blue-300 p-2.5 rounded-lg text-xs bg-blue-50 outline-none" placeholder="Ketik nominal angka manual..." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                
                                <div class="md:col-span-3"><label class="block text-[11px] font-bold text-gray-600 mb-1">Alamat Lengkap Ayah *</label><input type="text" name="ayah_alamat" required placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs uppercase bg-white outline-none"></div>
                            </div>
                        </div>

                        <!-- DATA IBU -->
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 mb-4">
                            <h4 class="text-xs font-black text-gray-700 mb-4 uppercase border-l-4 border-rose-400 pl-3">Data Ibu</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2"><label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Ibu *</label><input type="text" name="ibu_nama" required oninput="this.value = this.value.replace(/[^a-zA-Z\s\'\.\,]/g, '')" placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs uppercase bg-white outline-none"></div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Hubungan Keluarga *</label>
                                    <select id="ibu_hubungan_select" name="ibu_hubungan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none font-bold text-rose-600 cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Status --</option>
                                        <option value="Ibu Kandung">Ibu Kandung</option>
                                        <option value="Ibu Tiri">Ibu Tiri</option>
                                        <option value="Ibu Angkat">Ibu Angkat</option>
                                        <option value="Ibu Asuh">Ibu Asuh</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="ibu_hubungan_input" class="hidden mt-2 w-full border border-rose-300 p-2.5 rounded-lg text-xs uppercase bg-rose-50 outline-none" placeholder="Ketik manual...">
                                </div>
                                
                                <div><label class="block text-[11px] font-bold text-gray-600 mb-1">Umur (Tahun) *</label><input type="number" name="ibu_umur" required placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none"></div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Agama *</label>
                                    <select name="ibu_agama" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Agama --</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen Protestan">Kristen Protestan</option>
                                        <option value="Kristen Katolik">Kristen Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Khonghucu">Khonghucu</option>
                                        <option value="Kepercayaan Terhadap Tuhan YME">Kepercayaan Terhadap Tuhan YME</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Pendidikan Terakhir *</label>
                                    <select id="ibu_pendidikan_select" name="ibu_pendidikan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Pendidikan --</option>
                                        <option value="D1">D1</option>
                                        <option value="D2">D2</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="Informal">Informal</option>
                                        <option value="Non formal">Non formal</option>
                                        <option value="Paket A">Paket A</option>
                                        <option value="Paket B">Paket B</option>
                                        <option value="Paket C">Paket C</option>
                                        <option value="PAUD">PAUD</option>
                                        <option value="Profesi">Profesi</option>
                                        <option value="Putus SD">Putus SD</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S2 Terapan">S2 Terapan</option>
                                        <option value="S3">S3</option>
                                        <option value="S3 Terapan">S3 Terapan</option>
                                        <option value="SD / sederajat">SD / sederajat</option>
                                        <option value="SMA / sederajat">SMA / sederajat</option>
                                        <option value="SMP / sederajat">SMP / sederajat</option>
                                        <option value="Sp-1">Sp-1</option>
                                        <option value="Sp-2">Sp-2</option>
                                        <option value="Tidak sekolah">Tidak sekolah</option>
                                        <option value="TK / sederajat">TK / sederajat</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="ibu_pendidikan_input" class="hidden mt-2 w-full border border-rose-300 p-2.5 rounded-lg text-xs uppercase bg-rose-50 outline-none" placeholder="Ketik manual...">
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Pekerjaan *</label>
                                    <select id="ibu_pekerjaan_select" name="ibu_pekerjaan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Pekerjaan --</option>
                                        <option value="Tidak bekerja">Tidak bekerja</option>
                                        <option value="Nelayan">Nelayan</option>
                                        <option value="Petani">Petani</option>
                                        <option value="Peternak">Peternak</option>
                                        <option value="PNS/TNI/Polri">PNS/TNI/Polri</option>
                                        <option value="Karyawan Swasta">Karyawan Swasta</option>
                                        <option value="Pedagang Kecil">Pedagang Kecil</option>
                                        <option value="Pedagang Besar">Pedagang Besar</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Wirausaha">Wirausaha</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Pensiunan">Pensiunan</option>
                                        <option value="Tenaga Kerja Indonesia">Tenaga Kerja Indonesia</option>
                                        <option value="Karyawan BUMN">Karyawan BUMN</option>
                                        <option value="Tidak dapat diterapkan">Tidak dapat diterapkan</option>
                                        <option value="Sudah Meninggal">Sudah Meninggal</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="ibu_pekerjaan_input" class="hidden mt-2 w-full border border-rose-300 p-2.5 rounded-lg text-xs uppercase bg-rose-50 outline-none" placeholder="Ketik manual...">
                                </div>

                                <div><label class="block text-[11px] font-bold text-gray-600 mb-1">No HP/WA *</label><input type="text" name="ibu_hp" required oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkHpLive();" placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none"><span class="font-normal text-gray-500 text-[10px]">No HP/WA Orang Tua harus berbeda dengan No HP/WA Anak</span></div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Penghasilan /Bulan *</label>
                                    <select id="ibu_penghasilan_select" name="ibu_penghasilan" required class="w-full border border-gray-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Penghasilan --</option>
                                        <option value="Kurang dari Rp. 500,000">Kurang dari Rp. 500,000</option>
                                        <option value="Rp. 500,000 - Rp. 999,999">Rp. 500,000 - Rp. 999,999</option>
                                        <option value="Rp. 1,000,000 - Rp. 1,999,999">Rp. 1,000,000 - Rp. 1,999,999</option>
                                        <option value="Rp. 2,000,000 - Rp. 4,999,999">Rp. 2,000,000 - Rp. 4,999,999</option>
                                        <option value="Rp. 5,000,000 - Rp. 20,000,000">Rp. 5,000,000 - Rp. 20,000,000</option>
                                        <option value="Lebih dari Rp. 20,000,000">Lebih dari Rp. 20,000,000</option>
                                        <option value="Tidak Berpenghasilan">Tidak Berpenghasilan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <!-- Terkunci Input Angka -->
                                    <input type="text" id="ibu_penghasilan_input" class="hidden mt-2 w-full border border-rose-300 p-2.5 rounded-lg text-xs bg-rose-50 outline-none" placeholder="Ketik nominal angka manual..." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                
                                <div class="md:col-span-3"><label class="block text-[11px] font-bold text-gray-600 mb-1">Alamat Lengkap Ibu *</label><input type="text" name="ibu_alamat" required placeholder="" class="w-full border border-gray-300 p-2.5 rounded-lg text-xs uppercase bg-white outline-none"></div>
                            </div>
                        </div>

                        <!-- DATA WALI -->
                        <div class="bg-amber-50 p-5 rounded-2xl border border-amber-200">
                            <h4 class="text-xs font-black text-amber-800 mb-4 uppercase border-l-4 border-amber-500 pl-3">Data Wali (Opsional - Jika tidak tinggal dengan orang tua WAJIB diisi)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2"><label class="block text-[11px] font-bold text-amber-900 mb-1">Nama Wali</label><input type="text" name="wali_nama" oninput="this.value = this.value.replace(/[^a-zA-Z\s\'\.\,]/g, '')" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs uppercase bg-white outline-none"></div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-amber-900 mb-1">Hubungan Keluarga</label>
                                    <select id="wali_hubungan_select" name="wali_hubungan" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Status --</option>
                                        <option value="Kakek">Kakek</option>
                                        <option value="Nenek">Nenek</option>
                                        <option value="Paman">Paman</option>
                                        <option value="Bibi">Bibi</option>
                                        <option value="Kakak">Kakak</option>
                                        <option value="Saudara Sepupu">Saudara Sepupu</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="wali_hubungan_input" class="hidden mt-2 w-full border border-amber-400 p-2.5 rounded-lg text-xs uppercase bg-amber-100 outline-none" placeholder="Ketik manual...">
                                </div>
                                
                                <div><label class="block text-[11px] font-bold text-amber-900 mb-1">Umur Wali</label><input type="number" name="wali_umur" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs bg-white outline-none"></div>
                                <div>
                                    <label class="block text-[11px] font-bold text-amber-900 mb-1">Agama Wali</label>
                                    <select name="wali_agama" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-Pilih Agama-</option>
                                        <option value="Islam">Islam</option><option value="Kristen Protestan">Kristen Protestan</option><option value="Kristen Katolik">Kristen Katolik</option><option value="Hindu">Hindu</option><option value="Buddha">Buddha</option><option value="Khonghucu">Khonghucu</option><option value="Kepercayaan Terhadap Tuhan YME">Kepercayaan Terhadap Tuhan YME</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-amber-900 mb-1">Pendidikan Terakhir</label>
                                    <select id="wali_pendidikan_select" name="wali_pendidikan" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Pendidikan --</option>
                                        <option value="D1">D1</option>
                                        <option value="D2">D2</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="Informal">Informal</option>
                                        <option value="Non formal">Non formal</option>
                                        <option value="Paket A">Paket A</option>
                                        <option value="Paket B">Paket B</option>
                                        <option value="Paket C">Paket C</option>
                                        <option value="PAUD">PAUD</option>
                                        <option value="Profesi">Profesi</option>
                                        <option value="Putus SD">Putus SD</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S2 Terapan">S2 Terapan</option>
                                        <option value="S3">S3</option>
                                        <option value="S3 Terapan">S3 Terapan</option>
                                        <option value="SD / sederajat">SD / sederajat</option>
                                        <option value="SMA / sederajat">SMA / sederajat</option>
                                        <option value="SMP / sederajat">SMP / sederajat</option>
                                        <option value="Sp-1">Sp-1</option>
                                        <option value="Sp-2">Sp-2</option>
                                        <option value="Tidak sekolah">Tidak sekolah</option>
                                        <option value="TK / sederajat">TK / sederajat</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="wali_pendidikan_input" class="hidden mt-2 w-full border border-amber-400 p-2.5 rounded-lg text-xs uppercase bg-amber-100 outline-none" placeholder="Ketik manual...">
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-amber-900 mb-1">Pekerjaan Wali</label>
                                    <select id="wali_pekerjaan_select" name="wali_pekerjaan" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Pekerjaan --</option>
                                        <option value="Tidak bekerja">Tidak bekerja</option>
                                        <option value="Nelayan">Nelayan</option>
                                        <option value="Petani">Petani</option>
                                        <option value="Peternak">Peternak</option>
                                        <option value="PNS/TNI/Polri">PNS/TNI/Polri</option>
                                        <option value="Karyawan Swasta">Karyawan Swasta</option>
                                        <option value="Pedagang Kecil">Pedagang Kecil</option>
                                        <option value="Pedagang Besar">Pedagang Besar</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Wirausaha">Wirausaha</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Pensiunan">Pensiunan</option>
                                        <option value="Tenaga Kerja Indonesia">Tenaga Kerja Indonesia</option>
                                        <option value="Karyawan BUMN">Karyawan BUMN</option>
                                        <option value="Tidak dapat diterapkan">Tidak dapat diterapkan</option>
                                        <option value="Sudah Meninggal">Sudah Meninggal</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <input type="text" id="wali_pekerjaan_input" class="hidden mt-2 w-full border border-amber-400 p-2.5 rounded-lg text-xs uppercase bg-amber-100 outline-none" placeholder="Ketik manual...">
                                </div>

                                <div><label class="block text-[11px] font-bold text-amber-900 mb-1">No HP/WA Wali</label><input type="text" name="wali_hp" oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkHpLive();" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs bg-white outline-none"><span class="font-normal text-gray-500 text-[10px]">No HP/WA Wali harus berbeda dengan No HP/WA Anak</span></div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-amber-900 mb-1">Penghasilan Wali</label>
                                    <select id="wali_penghasilan_select" name="wali_penghasilan" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs bg-white outline-none cursor-pointer">
                                        <option value="" selected disabled>-- Pilih Penghasilan --</option>
                                        <option value="Kurang dari Rp. 500,000">Kurang dari Rp. 500,000</option>
                                        <option value="Rp. 500,000 - Rp. 999,999">Rp. 500,000 - Rp. 999,999</option>
                                        <option value="Rp. 1,000,000 - Rp. 1,999,999">Rp. 1,000,000 - Rp. 1,999,999</option>
                                        <option value="Rp. 2,000,000 - Rp. 4,999,999">Rp. 2,000,000 - Rp. 4,999,999</option>
                                        <option value="Rp. 5,000,000 - Rp. 20,000,000">Rp. 5,000,000 - Rp. 20,000,000</option>
                                        <option value="Lebih dari Rp. 20,000,000">Lebih dari Rp. 20,000,000</option>
                                        <option value="Tidak Berpenghasilan">Tidak Berpenghasilan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <!-- Terkunci Input Angka -->
                                    <input type="text" id="wali_penghasilan_input" class="hidden mt-2 w-full border border-amber-400 p-2.5 rounded-lg text-xs bg-amber-100 outline-none" placeholder="Ketik nominal angka manual..." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>

                                <div class="md:col-span-3"><label class="block text-[11px] font-bold text-amber-900 mb-1">Alamat Lengkap Wali</label><input type="text" name="wali_alamat" class="w-full border border-amber-300 p-2.5 rounded-lg text-xs uppercase bg-white outline-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN IV: RIWAYAT PENDIDIKAN -->
                    <div class="space-y-5 mb-8">
                        <h3 class="text-sm font-black text-gray-800 uppercase border-b pb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-mono text-xs">IV</span> Riwayat Pendidikan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div><label class="block text-xs font-bold text-gray-700 mb-2">Nama TK / PAUD (Opsional)</label><input type="text" name="sekolah_tk" placeholder="" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase"></div>
                            <div><label class="block text-xs font-bold text-gray-700 mb-2">Nama SD / MI *</label><input type="text" name="sekolah_sd" required placeholder="" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase"></div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-2">Nama SMP / MTs Asal *</label>
                                <input type="text" name="sekolah_smp" required placeholder="" value="{{ $siswa->asal_sekolah }}" class="w-full border-2 border-blue-200 font-bold text-blue-800 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition uppercase">
                            </div>
                            
                            <div class="md:col-span-2 bg-gray-50 p-4 rounded-xl border border-gray-200 mt-2">
                                <label class="block text-xs font-bold text-gray-700 mb-3">Pernah tinggal kelas / tidak lulus? <span class="font-normal text-gray-500">(Isi "-" jika lancar)</span></label>
                                <div class="flex gap-4">
                                    <div class="w-1/2"><label class="block text-[10px] font-bold text-gray-500 mb-1">Saat SD, di Kelas:</label><input type="text" name="tinggal_kelas_sd" required value="-" class="w-full border border-gray-300 p-2 rounded-lg text-sm uppercase outline-none"></div>
                                    <div class="w-1/2"><label class="block text-[10px] font-bold text-gray-500 mb-1">Saat SMP/MTs, di Kelas:</label><input type="text" name="tinggal_kelas_smp" required value="-" class="w-full border border-gray-300 p-2 rounded-lg text-sm uppercase outline-none"></div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <label class="block text-xs font-bold text-gray-700 mb-3">Pernah Mutasi / Pindah Sekolah? <span class="font-normal text-gray-500">(Isi "-" jika tidak pernah)</span></label>
                                <div class="flex gap-4">
                                    <div class="w-1/3"><label class="block text-[10px] font-bold text-gray-500 mb-1">Tahun Pindah:</label><input type="text" name="mutasi_tahun" required value="-" class="w-full border border-gray-300 p-2 rounded-lg text-sm uppercase outline-none"></div>
                                    <div class="w-2/3"><label class="block text-[10px] font-bold text-gray-500 mb-1">Dari Sekolah Asal:</label><input type="text" name="mutasi_dari" required value="-" class="w-full border border-gray-300 p-2 rounded-lg text-sm uppercase outline-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="btnSave" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg transition-all text-sm uppercase tracking-wider flex items-center justify-center gap-2 mt-8">
                        <i class="fa-solid fa-save"></i> Simpan & Lanjutkan
                    </button>
                    <div class="text-center mt-4">
                        <span id="gpsStatus" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-50 text-yellow-600 text-[10px] font-black border border-yellow-200 shadow-sm">
                            <i class="fa-solid fa-satellite-dish animate-pulse"></i> Mendeteksi Lokasi GPS...
                        </span>
                    </div>
                </form>
            </div>

            <!-- SUCCESS SECTION -->
            <div id="successSection" class="hidden text-center py-4">
                
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="fa-solid fa-check text-5xl text-green-500"></i>
                </div>
                
                <h2 class="text-3xl font-black text-[#1e293b] mb-2 tracking-tight">PENGISIAN BERHASIL!</h2>
                <p class="text-gray-600 mb-8 text-sm">Terima kasih <b class="text-gray-800 uppercase">{{ $siswa->nama ?? '' }}</b>, data Anda telah tersimpan sementara untuk dicetak.</p>

                <div class="relative bg-[#fffdf0] border-2 border-dashed border-[#facc15] rounded-2xl p-8 mb-8 mt-8 shadow-sm max-w-2xl mx-auto">
                    <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-[#facc15] text-yellow-900 text-[11px] font-black px-6 py-1.5 rounded-full tracking-widest uppercase shadow-sm whitespace-nowrap">
                        Jadwal Daftar Ulang Anda
                    </div>
                    
                    <h3 class="text-2xl md:text-3xl font-black text-[#b45309] mb-3 uppercase tracking-wide">
                        {{ $jadwalSiswa ?? 'Menunggu Jadwal' }}
                    </h3>
                    
                    <p class="text-sm font-bold text-[#d97706]">Wajib Hadir Ke Sekolah Sesuai Tanggal di Atas!</p>
                </div>

                <a href="{{ route('welcome.download', ['nisn' => $siswa->nisn ?? '']) }}" class="inline-flex items-center justify-center w-full max-w-2xl bg-[#dc2626] hover:bg-[#b91c1c] text-white font-black py-4 rounded-xl shadow-lg hover:shadow-red-500/30 transition-all text-base tracking-wide mb-8 uppercase">
                    <i class="fa-solid fa-file-pdf mr-3 text-xl"></i> DOWNLOAD BERKAS PDF SEKARANG
                </a>

                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-left max-w-2xl mx-auto">
                    <h4 class="text-xs font-black text-red-600 uppercase tracking-widest mb-4 flex items-center border-b border-gray-200 pb-3">
                        <i class="fa-solid fa-circle-exclamation mr-2 text-lg"></i> Wajib Dibaca:
                    </h4>
                    <ul class="text-sm text-gray-600 space-y-3 list-disc pl-5 marker:text-gray-400 font-medium leading-relaxed">
                        <li>Klik tombol merah di atas untuk mengunduh Dokumen Daftar Ulang Anda.</li>
                        <li>Cetak (Print) dokumen tersebut menggunakan kertas ukuran <b class="text-gray-900 font-black">A4</b>.</li>
                        <li>Gambarkan denah lokasi rumah Anda secara manual di halaman ke-3.</li>
                        <li>Siapkan <b class="text-gray-900 font-black">Materai Rp 10.000</b> untuk ditempel pada Surat Pernyataan di halaman ke-4, lalu ditandatangani basah.</li>
                        <li class="font-bold text-gray-900">Dokumen Daftar Ulang (Biodata, Denah, Surat Pernyataan, & Verifikasi di Halaman 2, 3, 4, 5 PDF ini).</li>
                    </ul>
                </div>
                
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dynamicFields = [
            { id: 'ayah_hubungan', required: true }, { id: 'ibu_hubungan', required: true }, { id: 'wali_hubungan', required: false },
            { id: 'tinggal_dengan', required: true }, { id: 'cita_cita', required: true }, { id: 'hobi', required: true },
            { id: 'ayah_pendidikan', required: true }, { id: 'ibu_pendidikan', required: true }, { id: 'wali_pendidikan', required: false },
            { id: 'ayah_pekerjaan', required: true }, { id: 'ibu_pekerjaan', required: true }, { id: 'wali_pekerjaan', required: false },
            { id: 'ayah_penghasilan', required: true }, { id: 'ibu_penghasilan', required: true }, { id: 'wali_penghasilan', required: false },
        ];

        dynamicFields.forEach(field => {
            const selectEl = document.getElementById(field.id + '_select');
            const inputEl = document.getElementById(field.id + '_input');
            if(selectEl && inputEl) {
                selectEl.addEventListener('change', function() {
                    if (this.value === 'Lainnya') {
                        inputEl.classList.remove('hidden'); inputEl.setAttribute('name', field.id);
                        this.removeAttribute('name'); if (field.required) inputEl.setAttribute('required', 'required');
                    } else {
                        inputEl.classList.add('hidden'); inputEl.removeAttribute('name');
                        inputEl.removeAttribute('required'); inputEl.value = ''; this.setAttribute('name', field.id);
                    }
                });
            }
        });

        // Deteksi Lokasi GPS
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let latEl = document.getElementById('lat_input');
                let lngEl = document.getElementById('lng_input');
                let gpsStatus = document.getElementById('gpsStatus');

                if (latEl && lngEl) {
                    latEl.value = position.coords.latitude;
                    lngEl.value = position.coords.longitude;
                    
                    if(gpsStatus) {
                        gpsStatus.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-50 text-green-700 text-[10px] font-black border border-green-200 shadow-sm";
                        gpsStatus.innerHTML = '<i class="fa-solid fa-check-circle"></i> GPS Terkunci';
                    }
                }
            }, function(error) {
                console.warn("GPS tidak aktif/ditolak: ", error.message);
                let gpsStatus = document.getElementById('gpsStatus');
                
                if(gpsStatus) {
                    gpsStatus.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-red-600 text-[10px] font-black border border-red-200 shadow-sm";
                    gpsStatus.innerHTML = '<i class="fa-solid fa-times-circle"></i> Lokasi Tidak Aktif (Pendaftaran Tetap Lanjut)';
                }

                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 6000, timerProgressBar: true,
                });
                Toast.fire({
                    icon: 'info',
                    title: 'GPS / Lokasi Tidak Aktif',
                    text: 'Fitur Rute Otomatis di PDF dimatikan. Anda tetap bisa melanjutkan pendaftaran.'
                });
            }, { enableHighAccuracy: true, timeout: 10000 });
        }
    });

    // Upload & Auto Crop Image
    document.getElementById('foto_upload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.match(/image.*/)) {
            Swal.fire('Format Salah!', 'Hanya file gambar (JPG/PNG) yang diperbolehkan.', 'error');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                
                const targetRatio = 3/4;
                const sourceRatio = img.width / img.height;
                let cropWidth, cropHeight, cropX, cropY;

                if (sourceRatio > targetRatio) {
                    cropHeight = img.height;
                    cropWidth = img.height * targetRatio;
                    cropX = (img.width - cropWidth) / 2;
                    cropY = 0;
                } else {
                    cropWidth = img.width;
                    cropHeight = img.width / targetRatio;
                    cropX = 0;
                    cropY = (img.height - cropHeight) / 2;
                }

                canvas.width = 150; 
                canvas.height = 200; 
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, cropX, cropY, cropWidth, cropHeight, 0, 0, canvas.width, canvas.height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.7);

                document.getElementById('foto_preview').src = dataUrl;
                document.getElementById('foto_preview_container').classList.remove('hidden');
                
                document.getElementById('pas_foto_base64').value = dataUrl;
            }
            img.src = event.target.result;
        }
        reader.readAsDataURL(file);
    });

    // Cegah Nomor HP Ganda
    function checkHpLive() {
        let no_hp = document.querySelector('input[name="no_hp"]');
        let ayah_hp = document.querySelector('input[name="ayah_hp"]');
        let ibu_hp = document.querySelector('input[name="ibu_hp"]');
        let wali_hp = document.querySelector('input[name="wali_hp"]');
        
        let valSiswa = no_hp.value.trim();
        let valAyah = ayah_hp ? ayah_hp.value.trim() : '';
        let valIbu = ibu_hp ? ibu_hp.value.trim() : '';
        let valWali = wali_hp ? wali_hp.value.trim() : '';

        let btn = document.getElementById('btnSave');
        let warnEl = document.getElementById('hp_warning');

        if (valSiswa !== '' && (valSiswa === valAyah || valSiswa === valIbu || (valWali !== '' && valSiswa === valWali))) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed', 'grayscale');
            warnEl.classList.remove('hidden');
            no_hp.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        } else {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed', 'grayscale');
            warnEl.classList.add('hidden');
            no_hp.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        }
    }

    // Dynamic Saudara Limit HTML
    document.getElementById('jumlah_saudara').addEventListener('input', function() {
        let count = parseInt(this.value) || 0;
        
        if (count > 3) {
            count = 3;
            this.value = 3;
        }
        
        let container = document.getElementById('saudara_container');
        let list = document.getElementById('saudara_list');
        list.innerHTML = ''; 
        if (count > 0 && count <= 3) { 
            container.classList.remove('hidden');
            for (let i = 1; i <= count; i++) {
                list.innerHTML += `
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-3 rounded-xl border border-gray-200 shadow-sm animate-fade-in">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-bold text-gray-500 mb-1">Nama Sdr ke-${i}</label>
                            <input type="text" name="saudara_nama[]" required oninput="this.value = this.value.replace(/[^a-zA-Z\\s\\'\\.\\,]/g, '')" class="w-full p-2 border border-gray-200 rounded-lg text-xs uppercase outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 mb-1">Umur</label>
                            <input type="number" name="saudara_umur[]" required class="w-full p-2 border border-gray-200 rounded-lg text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 mb-1">Status</label>
                            <select name="saudara_status[]" required class="w-full p-2 border border-gray-200 rounded-lg text-xs uppercase outline-none cursor-pointer">
                                <option value="" selected disabled>-Pilih-</option>
                                <option value="Kerja">Kerja</option>
                                <option value="Pelajar">Pelajar</option>
                            </select>
                        </div>
                    </div>
                `;
            }
        } else {
            container.classList.add('hidden');
        }
    });

    // AJAX Form Submit
    function submitBiodata(e) {
        e.preventDefault();

        let fotoBase64 = document.getElementById('pas_foto_base64').value;
        if (!fotoBase64) {
            Swal.fire('Pas Foto Wajib Diupload!', 'Silakan pilih file foto resmi Anda di bagian Identitas Diri.', 'warning');
            return;
        }

        let btn = document.getElementById('btnSave');
        if (btn.disabled) {
            Swal.fire('Peringatan!', 'Mohon perbaiki Nomor HP Murid terlebih dahulu.', 'warning');
            return;
        }

        // 🔥 VALIDASI FINAL FRONTEND: Pastikan 10 atau 16 Karakter 🔥
        let valNisnAsli = document.querySelector('input[name="nisn_asli"]').value;
        let valNoPend = document.querySelector('input[name="no_pendaftaran"]').value;

        if(valNisnAsli.length !== 10) {
            Swal.fire('Validasi Gagal!', 'NISN Asli harus tepat 10 Digit Angka.', 'warning');
            return;
        }
        if(valNoPend.length !== 16) {
            Swal.fire('Validasi Gagal!', 'No Pendaftaran harus tepat 16 Karakter.', 'warning');
            return;
        }

        let originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan Data...';
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        let formData = new FormData(e.target);
        let dataObj = Object.fromEntries(formData.entries());

        dataObj.latitude = document.getElementById('lat_input').value;
        dataObj.longitude = document.getElementById('lng_input').value;

        dataObj.saudara_nama = Array.from(document.querySelectorAll('input[name="saudara_nama[]"]')).map(el => el.value);
        dataObj.saudara_umur = Array.from(document.querySelectorAll('input[name="saudara_umur[]"]')).map(el => el.value);
        dataObj.saudara_status = Array.from(document.querySelectorAll('select[name="saudara_status[]"]')).map(el => el.value);

        fetch("{{ route('welcome.submit') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(dataObj)
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');

            if(data.success) {
                document.getElementById('formSection').style.display = 'none';
                document.getElementById('successSection').classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                Swal.fire('Gagal Menyimpan', data.message || 'Terjadi kesalahan sistem.', 'error');
            }
        }).catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            Swal.fire('Error Jaringan', 'Kesalahan jaringan. Silakan periksa koneksi Anda.', 'error');
        });
    }
</script>
<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush