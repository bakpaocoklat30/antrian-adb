<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Antrian Utama - SMKN 1 Adiwerna</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #020617;
            font-family: sans-serif;
        }
        @keyframes alert-glow {
            0%, 100% {
                border-color: rgba(59, 130, 246, 0.5);
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
                background-color: #1e3a8a;
            }
            50% {
                border-color: rgba(239, 68, 68, 0.9);
                box-shadow: 0 0 60px rgba(239, 68, 68, 0.6);
                background-color: #7f1d1d;
            }
        }
        .call-active {
            animation: alert-glow 1s infinite !important;
        }
        ::-webkit-scrollbar {
            width: 0px;
        }
        .glass-card {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(15, 23, 42, 1) 100%);
            border: 3px solid #3b82f6;
        }
    </style>
</head>
<body class="text-white h-screen overflow-hidden flex flex-col relative">

    <div id="startOverlay" class="fixed inset-0 bg-slate-950 z-[100] flex flex-col items-center justify-center cursor-pointer transition-all duration-700 backdrop-blur-xl" onclick="startLayar()">
        <div class="relative mb-8 animate-pulse">
            <div class="absolute inset-0 bg-blue-500 rounded-full blur-[40px] opacity-60"></div>
            <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo ADB" class="relative w-40 h-40 object-contain rounded-full shadow-2xl border-4 border-blue-500/50 bg-white">
        </div>
        <h1 class="text-3xl font-black tracking-widest text-blue-400 uppercase drop-shadow-md">Klik Untuk Memulai Sistem</h1>
        <p class="text-slate-400 mt-2 tracking-widest text-sm uppercase">Sistem Antrian Pelayanan ADB</p>
    </div>

    <header class="bg-slate-900 border-b-2 border-blue-800 p-3 px-6 flex justify-between items-center h-[10vh] shrink-0 shadow-2xl relative z-10">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo" class="h-[7vh] w-auto object-contain rounded-full border-2 border-white bg-white p-0.5">
            <div>
                <h1 class="text-2xl lg:text-3xl font-black tracking-widest uppercase leading-none text-white">SMK Negeri 1 Adiwerna</h1>
                <p class="text-blue-400 font-bold text-sm lg:text-base mt-1.5 tracking-[0.3em] uppercase">Sistem Antrian Pelayanan ADB</p>
            </div>
        </div>

        <div class="text-right flex flex-col justify-center items-end border border-blue-800 px-5 py-1 rounded-xl bg-slate-950 shadow-inner">
            <div id="realtimeDate" class="text-blue-300 font-bold text-xs lg:text-sm tracking-[0.1em] uppercase mb-0.5">Memuat Tanggal...</div>
            <div class="flex items-center">
                <i class="fa-regular fa-clock text-blue-500 mr-2 text-xl"></i>
                <div id="realtimeClock" class="text-3xl font-black text-yellow-400 leading-none tracking-wider">00:00:00</div>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 flex gap-4 h-[65vh] overflow-hidden">

        <div class="w-4/12 glass-card rounded-2xl shadow-2xl flex flex-col overflow-hidden">
            <div class="bg-blue-950/80 border-b-2 border-blue-500 text-center py-3 px-4 shadow-lg shrink-0">
                <h2 class="font-black text-lg xl:text-xl tracking-widest uppercase leading-tight text-white">WAITING LIST SISWA<br><span class="text-yellow-400 text-xs tracking-widest">STATUS HADIR DI LOKASI</span></h2>
            </div>
            <div class="flex-1 p-3 flex flex-col gap-3 overflow-y-auto" id="waitingListContainer"></div>
        </div>

        <div class="w-8/12 flex flex-col gap-4">

            <div class="h-[55%] bg-black rounded-2xl border-[3px] border-slate-700 overflow-hidden relative shadow-xl flex items-center justify-center">
                @if($setting->video_type == 'youtube' && !empty($setting->video_url))
                    <div id="ytIframe" class="w-full h-full absolute inset-0 z-0 pointer-events-none"></div>
                @elseif($setting->video_type == 'local' && !empty($setting->video_url))
                    <video id="bgVideoLocal" class="w-full h-full object-cover absolute inset-0 z-0 pointer-events-none" loop muted autoplay>
                        <source src="{{ asset('storage/' . $setting->video_url) }}" type="video/mp4">
                    </video>
                @else
                    <div class="text-center text-slate-600">
                        <i class="fa-solid fa-tv text-5xl mb-3 opacity-40"></i>
                        <p class="text-xs font-bold uppercase tracking-widest">Layar Informasi Antrian</p>
                    </div>
                @endif
            </div>

            <div id="panggilanCard" class="h-[45%] glass-card rounded-2xl flex flex-col items-center justify-center relative overflow-hidden shadow-2xl transition-all duration-300">
                <div class="text-2xl lg:text-3xl font-black text-white tracking-[0.2em] mb-1 uppercase drop-shadow-lg">
                    LOKET <span id="activeLoket" class="text-yellow-400 ml-1">--</span>
                </div>
                <div class="text-sm lg:text-base font-bold text-blue-300 mb-0 tracking-[0.3em] uppercase">NOMOR ANTRIAN</div>
                <div id="activeTicket" class="text-[5.5rem] lg:text-[7.5rem] font-black text-white tracking-tighter leading-none mt-1 drop-shadow-[0_0_20px_rgba(255,255,255,0.4)]">----</div>
            </div>

        </div>
    </main>

    <div class="h-[17vh] px-4 pb-4 pt-0 shrink-0">
        <div class="w-full h-full flex gap-3" id="loketContainer"></div>
    </div>

    <footer class="h-[8vh] bg-blue-950 border-t-[3px] border-yellow-400 flex items-center overflow-hidden shrink-0 shadow-2xl">
        <div class="bg-blue-800 h-full px-6 flex items-center font-black uppercase tracking-[0.3em] text-lg text-white italic border-r-[3px] border-yellow-400 shadow-md relative z-10">INFO</div>
        <div class="flex-1 flex items-center h-full bg-slate-950">
            <marquee class="text-2xl font-black text-yellow-400 uppercase tracking-widest drop-shadow-md" scrollamount="12">{{ $setting->running_text }}</marquee>
        </div>
    </footer>

    @if($setting->video_type == 'youtube' && !empty($setting->video_url))
    @php
        $ytUrl = $setting->video_url;
        parse_str((string) parse_url($ytUrl, PHP_URL_QUERY), $params);
        $listId = $params['list'] ?? null;
        $videoId = '';
        $path = parse_url($ytUrl, PHP_URL_PATH);
        if ($path) {
            $pathParts = explode('/', trim($path, '/'));
            $lastPart = end($pathParts);
            if ($lastPart !== 'videoseries' && $lastPart !== 'embed') {
                $videoId = $lastPart;
            }
        }
    @endphp
    <script>
        var ytPlayer;
        let baseVol = parseInt("{{ $setting->video_volume ?? 60 }}");
        let isPlaylist = {{ $listId ? 'true' : 'false' }};

        function onYouTubeIframeAPIReady() {
            ytPlayer = new YT.Player('ytIframe', {
                @if($videoId) videoId: '{{ $videoId }}', @endif
                playerVars: {
                    'autoplay': 1,
                    'controls': 0,
                    'disablekb': 1,
                    'mute': 1,
                    'enablejsapi': 1,
                    'origin': window.location.origin,
                    @if($listId) 'listType': 'playlist', 'list': '{{ $listId }}'
                    @else 'loop': 1, 'playlist': '{{ $videoId }}' @endif
                },
                events: {
                    'onReady': function (e) {
                        e.target.mute();
                        if (isPlaylist) {
                            e.target.setLoop(true);
                        }
                        e.target.playVideo();
                    },
                    'onStateChange': function (e) {
                        if (e.data === YT.PlayerState.ENDED && !isPlaylist) {
                            e.target.playVideo();
                        }
                    }
                }
            });
        }

        let tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        let firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    </script>
    @else
    <script> let baseVol = parseInt("{{ $setting->video_volume ?? 60 }}"); </script>
    @endif

    <script>
        let playedSignatures = new Set();
        let audioQueue = [];
        let isProcessingQueue = false;
        let isFirstLoad = true;
        let visualActive = null;

        let globalLokets = [];
        let globalWaiting = [];

        let activeCallId = 0;
        window.currentUtt = null;
        let lastPlayTime = Date.now();

        let voiceSpeed = parseFloat("{{ $setting->voice_speed ?? 0.8 }}");
        let voicePitch = parseFloat("{{ $setting->voice_pitch ?? 1.0 }}");

        function startLayar() {
            document.getElementById('startOverlay').style.opacity = '0';

            setTimeout(() => {
                document.getElementById('startOverlay').classList.add('hidden');
            }, 700);

            let dummyTts = new SpeechSynthesisUtterance('');
            dummyTts.volume = 0;
            window.speechSynthesis.speak(dummyTts);

            let localVid = document.getElementById('bgVideoLocal');
            if (localVid) {
                localVid.muted = (baseVol === 0);
                localVid.volume = (baseVol === 0) ? 0 : baseVol / 100;
                localVid.play().catch(e => console.log(e));
            }

            if (typeof ytPlayer !== 'undefined' && ytPlayer.unMute) {
                if (baseVol === 0) {
                    ytPlayer.mute();
                } else {
                    ytPlayer.unMute();
                    ytPlayer.setVolume(baseVol);
                }
                ytPlayer.playVideo();
            }
        }

        function fadeAudio(targetVolume, durationMs = 1500) {
            let localVid = document.getElementById('bgVideoLocal');
            let isYT = (typeof ytPlayer !== 'undefined' && ytPlayer && typeof ytPlayer.getVolume === 'function');

            let startLocal = localVid ? localVid.volume * 100 : 0;
            let startYT = isYT ? ytPlayer.getVolume() : 0;

            let intervalMs = 20;
            let steps = Math.max(1, Math.floor(durationMs / intervalMs));
            let currentStep = 0;

            if (window.audioFader) {
                clearInterval(window.audioFader);
            }

            window.audioFader = setInterval(() => {
                currentStep++;
                let progress = currentStep / steps;

                if (localVid) {
                    localVid.volume = Math.max(0, Math.min(1, (startLocal + (targetVolume - startLocal) * progress) / 100));
                }
                if (isYT) {
                    ytPlayer.setVolume(Math.max(0, Math.min(100, startYT + (targetVolume - startYT) * progress)));
                }

                if (currentStep >= steps) {
                    clearInterval(window.audioFader);
                }
            }, intervalMs);
        }

        setInterval(() => {
            const now = new Date();
            document.getElementById('realtimeClock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('realtimeDate').innerText = now.toLocaleDateString('id-ID', options);
        }, 1000);

        function formatForSpeech(str) {
            const m = {
                '0': 'Nol', '1': 'Satu', '2': 'Dua', '3': 'Tiga', '4': 'Empat',
                '5': 'Lima', '6': 'Enam', '7': 'Tujuh', '8': 'Delapan', '9': 'Sembilan'
            };

            let r = [];
            for (let i = 0; i < str.length; i++) {
                let c = str[i].toUpperCase();
                r.push(m[c] ? m[c] : c);
            }
            return r.join('... ');
        }

        function renderLayar() {
            const ticketEl = document.getElementById('activeTicket');
            const loketEl = document.getElementById('activeLoket');

            if (visualActive) {
                ticketEl.innerText = visualActive.ticket;
                loketEl.innerText = visualActive.loket;
            }

            const loketContainer = document.getElementById('loketContainer');
            if (globalLokets.length > 0) {
                let loketHtml = '';

                globalLokets.forEach(l => {
                    let contentHtml = '';

                    if (l.is_tutup) {
                        contentHtml = `<div class="text-xl lg:text-2xl xl:text-3xl font-black text-yellow-400 drop-shadow-md tracking-widest uppercase text-center leading-tight mt-1 px-1 flex-1 flex items-center justify-center">${l.ticket}</div>`;
                    } else {
                        let isBlank = (l.ticket === '---');
                        contentHtml = `<div class="text-4xl lg:text-5xl xl:text-6xl font-black ${isBlank ? 'text-slate-600' : 'text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.4)]'} -mt-1 flex-1 flex items-center justify-center">${l.ticket}</div>`;
                    }

                    loketHtml += `<div class="flex-1 glass-card rounded-xl flex flex-col items-center justify-center shadow-lg px-2 py-2 transition-all border-[3px]">
                        <div class="text-sm lg:text-base xl:text-lg font-bold text-blue-300 uppercase tracking-widest mb-1 shrink-0">
                            LOKET <span class="text-yellow-400 ml-1 text-base lg:text-xl xl:text-2xl font-black">${l.loket}</span>
                        </div>
                        ${contentHtml}
                    </div>`;
                });
                loketContainer.innerHTML = loketHtml;
            } else {
                loketContainer.innerHTML = '<div class="w-full glass-card rounded-xl flex items-center justify-center text-slate-400 font-bold text-sm tracking-widest uppercase">Belum ada loket verifikator aktif</div>';
            }

            const waitingContainer = document.getElementById('waitingListContainer');
            if (globalWaiting.length > 0) {
                let waitingHtml = '';

                globalWaiting.forEach(w => {
                    waitingHtml += `<div class="flex items-center justify-between bg-blue-900/40 px-5 py-3.5 rounded-xl border border-blue-500/40 shadow-inner mb-2 last:mb-0">
                        <div class="font-black text-yellow-400 text-2xl tracking-widest drop-shadow-sm">${w.ticket}</div>
                        <div class="font-bold text-white text-sm md:text-base truncate uppercase text-right ml-2 w-2/3">${w.nama}</div>
                    </div>`;
                });

                waitingContainer.innerHTML = waitingHtml;
            } else if (waitingContainer) {
                waitingContainer.innerHTML = '<div class="flex-1 flex flex-col items-center justify-center text-slate-600 font-bold italic"><i class="fa-solid fa-users-slash text-4xl mb-3"></i>Belum ada siswa hadir</div>';
            }
        }

        function processQueue(templateFormat) {
            if (isProcessingQueue || audioQueue.length === 0) return;

            isProcessingQueue = true;
            executeNext(templateFormat);
        }

        function executeNext(templateFormat) {
            if (audioQueue.length === 0) {
                isProcessingQueue = false;
                if (baseVol > 0) {
                    fadeAudio(baseVol, 2000);
                }
                return;
            }

            let currentCall = audioQueue.shift();
            lastPlayTime = Date.now();
            activeCallId++;
            let myCallId = activeCallId;

            visualActive = currentCall;
            renderLayar();

            const cardEl = document.getElementById('panggilanCard');
            cardEl.classList.add('call-active');
            setTimeout(() => {
                cardEl.classList.remove('call-active');
            }, 5000);

            let isFinished = false;

            let onAudioFinished = function () {
                if (isFinished || myCallId !== activeCallId) return;

                isFinished = true;
                window.currentUtt = null;

                setTimeout(() => {
                    executeNext(templateFormat);
                }, 1500);
            };

            if (baseVol === 0) {
                setTimeout(onAudioFinished, 3000);
                return;
            }

            fadeAudio(0, 1500);
            playTTSSequence(currentCall, myCallId, templateFormat, onAudioFinished);
        }

        function playTTSSequence(currentCall, myCallId, templateFormat, callback) {
            let formattedNumber = formatForSpeech(currentCall.ticket);
            let template = templateFormat || 'Nomor Antrian, {nomor}, silakan menuju ke, loket, {loket}';
            let textToSpeak = template.replace('{nomor}', formattedNumber).replace('{loket}', currentCall.loket);

            window.speechSynthesis.cancel();
            window.speechSynthesis.resume();

            let utt = new SpeechSynthesisUtterance(textToSpeak);
            window.currentUtt = utt;
            utt.lang = 'id-ID';
            utt.volume = baseVol / 100;
            utt.pitch = voicePitch;
            utt.rate = voiceSpeed;

            let voices = window.speechSynthesis.getVoices();
            let indoVoices = voices.filter(v => v.lang.includes('id') || v.name.includes('Indonesian'));
            let localIndoVoices = indoVoices.filter(v => v.localService);
            let voicePool = localIndoVoices.length > 0 ? localIndoVoices : indoVoices;

            if (voicePool.length > 0) {
                utt.voice = voicePool[0];
            }

            let failsafe = setTimeout(() => {
                if (myCallId === activeCallId) {
                    window.speechSynthesis.cancel();
                    callback();
                }
            }, 25000);

            utt.onend = function () { clearTimeout(failsafe); callback(); };
            utt.onerror = function () { clearTimeout(failsafe); callback(); };

            setTimeout(() => {
                if (myCallId !== activeCallId) return;

                let bell = new Audio("{{ asset('sounds/bell.mp3') }}");
                bell.volume = baseVol / 100;
                bell.play().catch(e => console.log(e));

                setTimeout(() => {
                    if (myCallId === activeCallId) {
                        window.speechSynthesis.speak(utt);
                    }
                }, 1500);
            }, 500);
        }

        setInterval(() => {
            if (isProcessingQueue && (Date.now() - lastPlayTime > 35000)) {
                window.speechSynthesis.cancel();
                isProcessingQueue = false;
                activeCallId++;

                if (baseVol > 0) {
                    fadeAudio(baseVol, 1500);
                }
            }

            fetch("{{ route('api.layar') }}")
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.setting) {
                            voiceSpeed = parseFloat(data.setting.voice_speed || 0.8);
                            voicePitch = parseFloat(data.setting.voice_pitch || 1.0);

                            let newBaseVol = parseInt(data.setting.video_volume);
                            if (!isNaN(newBaseVol) && baseVol !== newBaseVol) {
                                baseVol = newBaseVol;

                                if (baseVol === 0) {
                                    window.speechSynthesis.cancel();
                                }

                                if (document.getElementById('bgVideoLocal')) {
                                    document.getElementById('bgVideoLocal').muted = (baseVol === 0);
                                    document.getElementById('bgVideoLocal').volume = baseVol / 100;
                                }

                                if (typeof ytPlayer !== 'undefined' && ytPlayer.setVolume) {
                                    if (baseVol === 0) {
                                        ytPlayer.mute();
                                    } else {
                                        ytPlayer.unMute();
                                        ytPlayer.setVolume(baseVol);
                                    }
                                }
                            }
                        }

                        globalLokets = data.lokets;
                        globalWaiting = data.waiting;

                        if (isFirstLoad) {
                            visualActive = data.active;
                            renderLayar();

                            let allInit = [...(data.history || [])];
                            if (data.active) {
                                allInit.push(data.active);
                            }

                            allInit.forEach(ev => {
                                playedSignatures.add(ev.ticket + '_' + ev.loket + '_' + (ev.call_count || 1));
                            });

                            isFirstLoad = false;
                            return;
                        }

                        let allEvents = [];
                        if (data.history) {
                            allEvents = [...data.history].reverse();
                        }
                        if (data.active) {
                            allEvents.push(data.active);
                        }

                        allEvents.forEach(ev => {
                            let sig = ev.ticket + '_' + ev.loket + '_' + (ev.call_count || 1);

                            if (!playedSignatures.has(sig)) {
                                playedSignatures.add(sig);
                                audioQueue.push(ev);
                            }
                        });

                        if (!isProcessingQueue && audioQueue.length === 0) {
                            visualActive = data.active;
                        }

                        renderLayar();
                        processQueue(data.template);
                    }
                }).catch(err => console.log('Polling Error:', err));
        }, 2000);

        window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
    </script>
</body>
</html>
