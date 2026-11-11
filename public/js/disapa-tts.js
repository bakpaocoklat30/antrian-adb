window.DisapaTts = (function () {
    let activeAudio = null;
    let activeUtt = null;
    let sessionToken = 0;

    function clamp(num, min, max, fallback) {
        const parsed = parseFloat(num);
        if (Number.isNaN(parsed)) return fallback;
        return Math.max(min, Math.min(max, parsed));
    }

    function formatTicketNumber(str) {
        const digitMap = {
            '0': 'nol',
            '1': 'satu',
            '2': 'dua',
            '3': 'tiga',
            '4': 'empat',
            '5': 'lima',
            '6': 'enam',
            '7': 'tujuh',
            '8': 'delapan',
            '9': 'sembilan',
        };

        const chars = String(str || '').trim().toUpperCase().split('');
        const tokens = [];
        for (const ch of chars) {
            if (!ch || ch === '-' || ch === ' ') continue;
            tokens.push(digitMap[ch] || ch);
        }
        return tokens.join(', ');
    }

    function buildAnnouncementText(template, ticket, loket) {
        const rawTemplate = (template && String(template).trim())
            ? String(template)
            : 'Nomor antrian, {nomor}, silakan menuju ke loket {loket}';

        const cleanedTemplate = rawTemplate.replace(/\.{2,}/g, ', ');
        const formattedNumber = formatTicketNumber(ticket);

        return cleanedTemplate
            .replace(/\{nomor\}/gi, formattedNumber)
            .replace(/\{loket\}/gi, String(loket ?? ''))
            .replace(/\s+/g, ' ')
            .trim();
    }

    function splitTextChunks(text, maxLen) {
        const limit = maxLen || 180;
        const clean = String(text || '').replace(/\s+/g, ' ').trim();
        if (!clean) return [];
        if (clean.length <= limit) return [clean];

        const segments = clean.split(/(?<=[,;:.!?])\s+/);
        const chunks = [];
        let current = '';

        for (const seg of segments) {
            if (!current) {
                current = seg;
            } else if ((current.length + 1 + seg.length) <= limit) {
                current += ' ' + seg;
            } else {
                chunks.push(current);
                current = seg;
            }

            while (current.length > limit) {
                let cut = current.lastIndexOf(' ', limit);
                if (cut < 20) cut = limit;
                chunks.push(current.slice(0, cut).trim());
                current = current.slice(cut).trim();
            }
        }

        if (current) chunks.push(current);
        return chunks;
    }

    function isIndonesianVoice(v) {
        if (!v) return false;
        const lang = String(v.lang || '').toLowerCase().replace('_', '-');
        const name = String(v.name || '').toLowerCase();
        return lang === 'id'
            || lang.startsWith('id-')
            || lang === 'in-id'
            || name.includes('indonesia')
            || name.includes('gadis')
            || name.includes('ardi')
            || name.includes('damayanti');
    }

    function rankIndonesianVoice(v) {
        const name = String(v.name || '').toLowerCase();
        if (name.includes('natural') || name.includes('gadis') || name.includes('ardi')) return 0;
        if (name.includes('google')) return 1;
        if (!name.includes('espeak')) return 2;
        return 3;
    }

    function pickBestIndonesianVoice() {
        if (!('speechSynthesis' in window)) return null;
        const voices = window.speechSynthesis.getVoices() || [];
        const indoVoices = voices.filter(isIndonesianVoice);
        if (indoVoices.length === 0) return null;
        indoVoices.sort((a, b) => rankIndonesianVoice(a) - rankIndonesianVoice(b));
        return indoVoices[0];
    }

    function stop() {
        sessionToken++;
        if (activeAudio) {
            try {
                activeAudio.onended = null;
                activeAudio.onerror = null;
                activeAudio.pause();
                activeAudio.src = '';
            } catch (e) {}
            activeAudio = null;
        }
        if ('speechSynthesis' in window) {
            try {
                window.speechSynthesis.cancel();
            } catch (e) {}
        }
        activeUtt = null;
    }

    function unlock() {
        try {
            const silence = new Audio('data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA');
            silence.volume = 0;
            silence.play().catch(() => {});
        } catch (e) {}

        try {
            if ('speechSynthesis' in window) {
                const dummy = new SpeechSynthesisUtterance('');
                dummy.volume = 0;
                window.speechSynthesis.speak(dummy);
            }
        } catch (e) {}
    }

    function playBrowserIndonesian(text, opts, token, allowServerFallback) {
        if (token !== sessionToken || (opts.shouldAbort && opts.shouldAbort())) {
            opts.onEnd && opts.onEnd();
            return;
        }

        const voice = pickBestIndonesianVoice();
        if (!voice) {
            if (allowServerFallback) {
                playServerIndonesian(text, opts, token, false);
                return;
            }
            opts.onEnd && opts.onEnd('no_indonesian_voice');
            return;
        }

        try {
            window.speechSynthesis.cancel();
            window.speechSynthesis.resume();

            const utt = new SpeechSynthesisUtterance(text);
            activeUtt = utt;
            utt.voice = voice;
            utt.lang = voice.lang || 'id-ID';
            utt.rate = clamp(opts.speed, 0.5, 1.5, 1.0);
            utt.pitch = clamp(opts.pitch, 0.5, 2.0, 1.0);
            utt.volume = clamp(opts.volume, 0.0, 1.0, 0.8);

            let finished = false;
            const finish = (reason) => {
                if (finished) return;
                finished = true;
                activeUtt = null;
                clearTimeout(failsafe);
                if (token === sessionToken) {
                    opts.onEnd && opts.onEnd(reason);
                }
            };

            const failsafe = setTimeout(() => {
                if (token === sessionToken) {
                    try { window.speechSynthesis.cancel(); } catch (e) {}
                    finish('timeout');
                }
            }, 25000);

            utt.onend = () => finish();
            utt.onerror = () => finish('browser_tts_error');

            window.speechSynthesis.speak(utt);
        } catch (e) {
            if (allowServerFallback) {
                playServerIndonesian(text, opts, token, false);
            } else {
                opts.onEnd && opts.onEnd('browser_tts_exception');
            }
        }
    }

    function playServerIndonesian(text, opts, token, allowBrowserFallback) {
        const chunks = splitTextChunks(text, 180);
        if (chunks.length === 0) {
            opts.onEnd && opts.onEnd();
            return;
        }

        const endpoint = opts.ttsUrl || '/api/tts';
        const speed = clamp(opts.speed, 0.5, 1.5, 1.0);
        const volume = clamp(opts.volume, 0.0, 1.0, 0.8);

        let idx = 0;

        const playNextChunk = () => {
            if (token !== sessionToken || (opts.shouldAbort && opts.shouldAbort())) {
                opts.onEnd && opts.onEnd('aborted');
                return;
            }

            if (idx >= chunks.length) {
                activeAudio = null;
                opts.onEnd && opts.onEnd();
                return;
            }

            const chunkText = chunks[idx++];
            const url = endpoint + (endpoint.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(chunkText);
            const audio = new Audio(url);
            activeAudio = audio;
            audio.volume = volume;
            audio.playbackRate = speed;
            audio.preservesPitch = true;
            audio.mozPreservesPitch = true;
            audio.webkitPreservesPitch = true;

            let settled = false;
            const failsafe = setTimeout(() => {
                if (!settled && token === sessionToken) {
                    settled = true;
                    try { audio.pause(); } catch (e) {}
                    handleChunkFailure('timeout');
                }
            }, 20000);

            const handleChunkFailure = (reason) => {
                clearTimeout(failsafe);
                activeAudio = null;
                if (token !== sessionToken) return;
                if (allowBrowserFallback && pickBestIndonesianVoice()) {
                    playBrowserIndonesian(text, opts, token, false);
                } else {
                    opts.onEnd && opts.onEnd(reason);
                }
            };

            audio.onended = () => {
                if (settled) return;
                settled = true;
                clearTimeout(failsafe);
                playNextChunk();
            };

            audio.onerror = () => {
                if (settled) return;
                settled = true;
                handleChunkFailure('server_audio_error');
            };

            const playPromise = audio.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(() => {
                    if (settled) return;
                    settled = true;
                    handleChunkFailure('autoplay_blocked');
                });
            }
        };

        playNextChunk();
    }

    function speak(text, opts) {
        stop();
        const token = ++sessionToken;
        const cleanText = String(text || '').trim();
        if (!cleanText) {
            opts && opts.onEnd && opts.onEnd();
            return;
        }

        const options = opts || {};
        const engine = options.engine || 'server_id';

        if (engine === 'browser_id') {
            playBrowserIndonesian(cleanText, options, token, true);
        } else {
            playServerIndonesian(cleanText, options, token, true);
        }
    }

    return {
        formatTicketNumber,
        buildAnnouncementText,
        pickBestIndonesianVoice,
        unlock,
        speak,
        stop,
    };
})();
