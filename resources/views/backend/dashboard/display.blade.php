@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto w-full">
    
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6">
        <h1 class="text-2xl font-black text-gray-800 text-left"><i class="fa-solid fa-tv text-purple-600 mr-2"></i> Pengaturan Layar & Suara</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition"><i class="fa-solid fa-arrow-left mr-2"></i> Kembali</a>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 min-h-[70vh] flex flex-col">
        <form action="{{ route('admin.update_display') }}" method="POST" enctype="multipart/form-data" class="flex flex-col space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-bullhorn text-blue-500 mr-1"></i> Running Text</label>
                    <textarea name="running_text" rows="4" required class="w-full rounded-lg border-gray-300 border p-3 outline-none focus:ring-2 focus:ring-purple-500">{{ $setting->running_text }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-microphone-lines text-green-500 mr-1"></i> Format Voice Announcer</label>
                    <textarea name="announcer_text" rows="4" required class="w-full rounded-lg border-gray-300 border p-3 outline-none focus:ring-2 focus:ring-purple-500">{{ $setting->announcer_text }}</textarea>
                    <div class="text-[10px] text-gray-600 mt-1 space-y-1 bg-gray-50 p-2 rounded border">
                        <p><b>Gunakan Kode Berikut:</b></p>
                        <p><code class="bg-yellow-100 px-1 font-bold text-yellow-800">{nomor}</code> = Memanggil nomor antrian.</p>
                        <p><code class="bg-green-100 px-1 font-bold text-green-800">{loket}</code> = Memanggil nomor loket.</p>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-video text-red-500 mr-1"></i> Sumber Video Media</label>
                <select name="video_type" id="videoType" onchange="toggleVideoInput()" class="w-full rounded-lg border-gray-300 border p-3 outline-none focus:ring-2 focus:ring-purple-500 font-bold mb-4">
                    <option value="youtube" {{ $setting->video_type == 'youtube' ? 'selected' : '' }}>YouTube URL (Membutuhkan Internet di Aula)</option>
                    <option value="local" {{ $setting->video_type == 'local' ? 'selected' : '' }}>Upload Video MP4 (Lokal / Tanpa Internet)</option>
                </select>

                <div id="youtubeInput" class="{{ $setting->video_type == 'youtube' ? 'block' : 'hidden' }} bg-red-50 p-4 rounded-lg border border-red-100">
                    <label class="block text-xs font-bold text-red-700 mb-1">Link Embed YouTube / Playlist</label>
                    <input type="text" name="video_url_youtube" value="{{ $setting->video_type == 'youtube' ? $setting->video_url : '' }}" placeholder="https://www.youtube.com/embed/..." class="w-full rounded border-gray-300 p-2 text-sm font-mono mb-3">
                    
                    <div class="bg-white p-3 rounded border border-red-200 text-red-800 text-xs shadow-sm">
                        <p class="font-bold mb-1"><i class="fa-brands fa-youtube text-red-600"></i> PETUNJUK LINK YOUTUBE:</p>
                        <ul class="list-disc pl-4 space-y-1 text-gray-700">
                            <li>Pastikan menggunakan link format <b>Embed</b>, bukan link url biasa.</li>
                            <li>Contoh Video Tunggal: <code class="bg-gray-100 px-1 rounded font-bold text-red-600">https://www.youtube.com/embed/ID_VIDEO</code></li>
                            <li>Contoh Playlist: <code class="bg-gray-100 px-1 rounded font-bold text-red-600">https://www.youtube.com/embed/videoseries?list=PLabcde12345</code></li>
                            <li class="text-red-600"><b>PENTING UNTUK PLAYLIST:</b> Pastikan menggunakan Playlist buatan Anda sendiri yang berstatus <b>Public</b> atau <b>Unlisted</b>. Kode ID Playlist harus diawali dengan huruf <b>PL</b> (Contoh: <i>PLabcde12345</i>), JANGAN gunakan playlist YouTube Mix yang berawalan huruf <i>RD</i>.</li>
                            <li><i>Catatan: Video/Playlist akan otomatis diulang (Loop) tanpa henti oleh sistem.</i></li>
                        </ul>
                    </div>
                </div>

                <div id="localInput" class="{{ $setting->video_type == 'local' ? 'block' : 'hidden' }} bg-blue-50 p-4 rounded-lg border border-blue-100">
                    
                    <label class="block text-xs font-bold text-blue-700 mb-1"><i class="fa-solid fa-folder-open mr-1"></i> Pilih Dari Video Tersimpan</label>
                    <select name="existing_video" class="w-full rounded border-gray-300 p-2 text-sm mb-4 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Pilih Video yang Pernah Diupload --</option>
                        @if(isset($existingVideos) && count($existingVideos) > 0)
                            @foreach($existingVideos as $vid)
                                <option value="{{ $vid }}" {{ $setting->video_url == $vid ? 'selected' : '' }}>
                                    🎥 {{ basename($vid) }}
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>Belum ada video di storage server.</option>
                        @endif
                    </select>

                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-blue-200"></div>
                        <span class="flex-shrink-0 mx-2 text-blue-400 text-[10px] font-bold uppercase">ATAU</span>
                        <div class="flex-grow border-t border-blue-200"></div>
                    </div>

                    <label class="block text-xs font-bold text-blue-700 mb-1 mt-2"><i class="fa-solid fa-cloud-arrow-up mr-1"></i> Upload File Video Baru (.mp4)</label>
                    <input type="file" name="video_file" accept="video/mp4" class="w-full rounded border-gray-300 p-2 bg-white text-sm">
                    <p class="text-[10px] text-gray-500 mt-1 font-bold italic">*Jika Anda meng-upload video baru, pilihan dropdown di atas akan diabaikan.</p>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-black py-3 rounded-xl hover:bg-blue-700 transition shadow-md">
                <i class="fa-solid fa-save mr-2"></i> SIMPAN TEKS & MEDIA
            </button>
        </form>

        <div class="my-8 border-t-2 border-dashed border-gray-200"></div>

        <div class="flex flex-col space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative overflow-hidden shadow-sm">
                <div class="absolute right-0 top-0 bg-green-500 text-white text-[9px] font-black px-2 py-1 rounded-bl-lg animate-pulse uppercase tracking-widest">Live Sync Active</div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-volume-high text-indigo-500 mr-1"></i> Master Volume Layar (Video & Suara)</label>
                <div class="flex items-center gap-4 mt-3">
                    <i class="fa-solid fa-volume-off text-gray-400"></i>
                    <input type="range" name="video_volume" min="0" max="100" value="{{ $setting->video_volume ?? 60 }}" 
                        oninput="document.getElementById('volValue').innerText = this.value + '%'" 
                        onchange="syncLiveSetting('video_volume', this.value)" 
                        class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer focus:outline-none">
                    <i class="fa-solid fa-volume-up text-gray-600"></i>
                    <span id="volValue" class="font-black text-indigo-600 w-12 text-right">{{ $setting->video_volume ?? 60 }}%</span>
                </div>
                <p class="text-[10px] text-gray-500 mt-2"><i class="fa-solid fa-bolt text-yellow-500 mr-1"></i> Perubahan langsung berefek ke TV. Set 0% untuk MUTE mutlak.</p>
            </div>

            <div>
                <h3 class="text-lg font-black text-gray-800 mb-4"><i class="fa-solid fa-microphone-lines text-red-500 mr-2"></i> Pengaturan Mesin Suara (Otomatis Tersimpan)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipe Suara</label>
                        <select name="voice_type" onchange="syncLiveSetting('voice_type', this.value)" class="w-full border-gray-300 p-2 rounded-lg font-bold text-blue-600 focus:ring-2 focus:ring-blue-400 outline-none">
                            <option value="tts" {{ ($setting->voice_type ?? 'tts') == 'tts' ? 'selected' : '' }}>Sistem Komputer (TTS)</option>
                            <option value="custom" {{ ($setting->voice_type ?? '') == 'custom' ? 'selected' : '' }}>Rekaman Manusia (Custom)</option>
                        </select>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 relative shadow-sm">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kecepatan Bicara (Speed)</label>
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-person-walking text-gray-400"></i>
                            <input type="range" name="voice_speed" id="voiceSpeedInput" min="0.5" max="1.5" step="0.1" value="{{ $setting->voice_speed ?? 0.8 }}" oninput="document.getElementById('speedValue').innerText = this.value + 'x'" onchange="testVoice(); syncLiveSetting('voice_speed', this.value)" class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer focus:outline-none">
                            <i class="fa-solid fa-person-running text-gray-600"></i>
                        </div>
                        <div class="text-center mt-2 font-black text-blue-600 text-lg" id="speedValue">{{ $setting->voice_speed ?? 0.8 }}x</div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 relative shadow-sm">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nada Suara Dasar (Pitch)</label>
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-microphone-lines-slash text-gray-400 text-sm"></i>
                            <input type="range" name="voice_pitch" id="voicePitchInput" min="0.5" max="2.0" step="0.1" value="{{ $setting->voice_pitch ?? 1.0 }}" oninput="document.getElementById('pitchValue').innerText = this.value" onchange="testVoice(); syncLiveSetting('voice_pitch', this.value)" class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer focus:outline-none">
                            <i class="fa-solid fa-microphone-lines text-gray-600"></i>
                        </div>
                        <div class="text-center mt-2 font-black text-red-600 text-lg" id="pitchValue">{{ $setting->voice_pitch ?? 1.0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleVideoInput() {
        let type = document.getElementById('videoType').value;
        document.getElementById('youtubeInput').classList.toggle('hidden', type !== 'youtube');
        document.getElementById('localInput').classList.toggle('hidden', type !== 'local');
    }

    function syncLiveSetting(field, val) {
        fetch('{{ route("admin.live_setting") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ field: field, value: val })
        }).catch(err => console.error(err));
    }

    window.previewUtt = null; 

    function testVoice() {
        let speed = parseFloat(document.getElementById('voiceSpeedInput').value);
        let pitch = parseFloat(document.getElementById('voicePitchInput').value);
        
        window.speechSynthesis.cancel(); 
        window.speechSynthesis.resume(); 

        let utt = new SpeechSynthesisUtterance("Cek suara, satu dua tiga.");
        window.previewUtt = utt; 
        utt.lang = 'id-ID'; 
        utt.rate = speed;
        utt.pitch = pitch;

        let voices = window.speechSynthesis.getVoices();
        let indoVoice = voices.find(v => v.lang.includes('id') || v.name.includes('Indonesian'));
        if(indoVoice) utt.voice = indoVoice;

        utt.onend = function() { window.previewUtt = null; };
        window.speechSynthesis.speak(utt);
    }

    @if(session('success')) Swal.fire('Berhasil!', '{{ session('success') }}', 'success'); @endif
</script>
@endpush