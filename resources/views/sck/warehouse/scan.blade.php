@extends('sck.layouts.sck')

@section('content')
<div class="space-y-6" x-data="scannerApp()">
    
    <!-- Header -->
    <div class="flex items-center space-x-2">
        <a href="{{ route('sck.lager.index') }}" class="text-gray-400 hover:text-cyan-400 transition-colors">
            <i class="fa-solid fa-circle-chevron-left text-xl"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black">Lagersystem QR-Code Scanner</h2>
            <p class="text-xs text-gray-500">Kamera auf den QR-Code eines Artikels richten, um den Bestand anzupassen.</p>
        </div>
    </div>

    <!-- Scanner Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Column 1 & 2: Camera & Settings -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Settings Panel -->
            <div class="glass-panel p-5 rounded-2xl border border-gray-800 space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 flex items-center space-x-2">
                    <i class="fa-solid fa-sliders text-cyan-400"></i>
                    <span>Scanner-Einstellungen</span>
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Action Select: Add vs Remove -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktion</label>
                        <div class="grid grid-cols-2 gap-2 bg-gray-950/60 p-1.5 rounded-xl border border-gray-800">
                            <button @click="action = 'add'" :class="action === 'add' ? 'bg-emerald-600 text-white font-bold' : 'text-gray-400'" class="py-2 px-3 rounded-lg text-xs transition-all uppercase tracking-wider flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-square-plus"></i>
                                <span>Auffüllen</span>
                            </button>
                            <button @click="action = 'remove'" :class="action === 'remove' ? 'bg-red-600 text-white font-bold' : 'text-gray-400'" class="py-2 px-3 rounded-lg text-xs transition-all uppercase tracking-wider flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-square-minus"></i>
                                <span>Entnahme</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mode Select: Auto vs Manual -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mengen-Modus</label>
                        <div class="grid grid-cols-2 gap-2 bg-gray-950/60 p-1.5 rounded-xl border border-gray-800">
                            <button @click="qtyMode = 'auto'" :class="qtyMode === 'auto' ? 'bg-cyan-600 text-white font-bold' : 'text-gray-400'" class="py-2 px-2 rounded-lg text-xs transition-all uppercase tracking-wider flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                <span>Automatisch (1)</span>
                            </button>
                            <button @click="qtyMode = 'manual'" :class="qtyMode === 'manual' ? 'bg-cyan-600 text-white font-bold' : 'text-gray-400'" class="py-2 px-2 rounded-lg text-xs transition-all uppercase tracking-wider flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-keyboard"></i>
                                <span>Manuell</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Custom Quantity input (Shown only in manual mode) -->
                <div x-show="qtyMode === 'manual'" x-transition class="space-y-1" x-cloak>
                    <label for="quantity" class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Festzulegende Menge vor dem Scannen</label>
                    <div class="flex items-center space-x-3 mt-1">
                        <input type="number" id="quantity" x-model.number="quantity" min="1" class="sck-input text-lg rounded-xl px-4 py-2 w-32 font-bold font-mono text-center">
                        <span class="text-xs text-gray-400">Einheiten werden bei jedem Scan hinzugefügt oder abgezogen.</span>
                    </div>
                </div>
            </div>

            <!-- Camera Viewport Card -->
            <div class="glass-panel rounded-2xl border border-gray-800 overflow-hidden shadow-2xl relative">
                <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/45">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-300 flex items-center space-x-2">
                        <i class="fa-solid fa-video text-cyan-400"></i>
                        <span>Kamera-Livefeed</span>
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="scanning ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                        <span class="text-xs font-bold text-gray-400" x-text="scanning ? 'Scanner aktiv' : 'Scanner gestoppt'"></span>
                    </div>
                </div>

                <!-- Scanner element -->
                <div class="p-6 bg-black/60 flex flex-col items-center justify-center min-h-[350px]">
                    <div id="reader" class="w-full max-w-md rounded-xl overflow-hidden border border-gray-800 bg-gray-950" style="min-height: 250px;"></div>
                    
                    <div class="mt-6 flex space-x-3">
                        <button @click="startScanner()" x-show="!scanning" class="btn-neon-cyan text-white px-5 py-2.5 rounded-xl font-bold flex items-center space-x-2 transition-all">
                            <i class="fa-solid fa-play"></i>
                            <span>Kamera starten</span>
                        </button>
                        <button @click="stopScanner()" x-show="scanning" class="bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 px-5 py-2.5 rounded-xl font-bold flex items-center space-x-2 transition-colors">
                            <i class="fa-solid fa-stop"></i>
                            <span>Scanner pausieren</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Column 3: Live Scan logs -->
        <div class="space-y-6">
            <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex flex-col h-[560px] max-h-[80vh]">
                <div class="flex items-center justify-between border-b border-gray-850 pb-3 mb-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-300 flex items-center space-x-2">
                        <i class="fa-solid fa-list-check text-cyan-400"></i>
                        <span>Aktivitätsprotokoll</span>
                    </h3>
                    <button @click="clearLogs()" class="text-[10px] text-gray-500 hover:text-cyan-400 transition-colors uppercase font-bold tracking-wider">
                        Leeren
                    </button>
                </div>

                <!-- Logs List container -->
                <div class="flex-grow overflow-y-auto space-y-3 pr-1 text-left text-xs font-mono">
                    <template x-for="(log, idx) in logs" :key="idx">
                        <div class="p-3 rounded-lg border flex items-start space-x-2" 
                             :class="log.success ? 'bg-emerald-950/20 border-emerald-500/30 text-emerald-300' : 'bg-red-950/20 border-red-500/30 text-red-300'">
                            <div class="flex-shrink-0 mt-0.5">
                                <i :class="log.success ? 'fa-solid fa-circle-check text-emerald-400' : 'fa-solid fa-triangle-exclamation text-red-400'"></i>
                            </div>
                            <div class="space-y-1 flex-grow">
                                <div class="font-bold flex items-center justify-between text-[10px] text-gray-500">
                                    <span x-text="log.time"></span>
                                    <span class="uppercase text-[9px]" :class="log.action === 'add' ? 'text-emerald-400' : 'text-red-400'" x-text="log.action === 'add' ? 'Einbuchung' : 'Entnahme'"></span>
                                </div>
                                <p class="leading-normal" x-text="log.message"></p>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="logs.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-center text-gray-600 py-12">
                            <i class="fa-solid fa-barcode text-4xl mb-3 text-gray-800 animate-pulse"></i>
                            <p>Bisher noch keine Artikel gescannt.</p>
                            <p class="text-[10px] text-gray-700 mt-1">Gescannte Barcodes werden hier protokolliert.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- html5-qrcode scanner client-side dependency -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    function scannerApp() {
        return {
            action: 'remove', // 'add' or 'remove'
            qtyMode: 'auto', // 'auto' or 'manual'
            quantity: 1,
            scanning: false,
            logs: [],
            html5Qrcode: null,
            lastCode: '',
            lastTime: 0,
            
            init() {
                // Initialize audio context early on user click (avoid autoplay block)
                document.addEventListener('click', () => {
                    this.initAudio();
                }, { once: true });
            },

            initAudio() {
                if (window.AudioContext || window.webkitAudioContext) {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
            },

            beep(type = 'success') {
                if (!this.audioCtx) return;
                
                // Resume audio context if suspended (browser behavior)
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }

                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                
                if (type === 'success') {
                    // Quick high pitch beep
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(900, this.audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.12, this.audioCtx.currentTime);
                    
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.12);
                } else {
                    // Double low pitch warning beep
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(260, this.audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                    
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.18);
                    
                    setTimeout(() => {
                        const osc2 = this.audioCtx.createOscillator();
                        const gain2 = this.audioCtx.createGain();
                        osc2.connect(gain2);
                        gain2.connect(this.audioCtx.destination);
                        osc2.type = 'sawtooth';
                        osc2.frequency.setValueAtTime(260, this.audioCtx.currentTime);
                        gain2.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                        osc2.start();
                        osc2.stop(this.audioCtx.currentTime + 0.18);
                    }, 240);
                }
            },

            startScanner() {
                this.html5Qrcode = new Html5Qrcode("reader");
                const config = { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };
                
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length > 0) {
                        let cameraId = devices[0].id;
                        for (const device of devices) {
                            const label = device.label.toLowerCase();
                            if (label.includes('back') || label.includes('rear') || label.includes('rück') || label.includes('umgebung')) {
                                cameraId = device.id;
                                break;
                            }
                        }
                        return this.html5Qrcode.start(
                            cameraId, 
                            config, 
                            (decodedText, decodedResult) => this.onScan(decodedText, decodedResult)
                        );
                    } else {
                        return this.html5Qrcode.start(
                            { facingMode: "environment" }, 
                            config, 
                            (decodedText, decodedResult) => this.onScan(decodedText, decodedResult)
                        );
                    }
                })
                .then(() => {
                    this.scanning = true;
                })
                .catch(err => {
                    alert("Kamerafehler: Bitte überprüfe die Berechtigungen der Kamera.");
                    console.error("Scanner Error: ", err);
                });
            },

            stopScanner() {
                if (this.html5Qrcode) {
                    this.html5Qrcode.stop()
                    .then(() => {
                        this.scanning = false;
                        this.html5Qrcode = null;
                    })
                    .catch(err => {
                        console.error("Failed to stop scanner: ", err);
                    });
                }
            },

            onScan(decodedText, decodedResult) {
                const now = Date.now();
                
                // Extracts article number from scanned code
                // Code could be a raw 5-digit number or a full URL like http://localhost/sck/lager/artikel/12345
                let articleNum = decodedText;
                
                // Regex check to see if it's a URL
                if (decodedText.includes('/lager/artikel/')) {
                    const parts = decodedText.split('/');
                    articleNum = parts[parts.length - 1].trim();
                }

                // Clean and ensure it's a 5 digit pattern
                articleNum = articleNum.replace(/[^0-9]/g, '');

                if (articleNum.length !== 5) {
                    // Invalid QR scanned
                    if (now - this.lastTime > 2500) {
                        this.beep('error');
                        this.addLog(false, `Ungültiger Code gescannt: '${decodedText}'. QR-Code muss eine 5-stellige Artikelnummer enthalten.`, this.action);
                        this.lastTime = now;
                    }
                    return;
                }

                // Prevent double scanning within 2 seconds
                if (articleNum === this.lastCode && (now - this.lastTime) < 2200) {
                    return;
                }

                this.lastCode = articleNum;
                this.lastTime = now;

                const qty = this.qtyMode === 'auto' ? 1 : Math.max(1, parseInt(this.quantity) || 1);

                // Send request to API endpoint
                window.axios.post("{{ route('sck.lager.scan.action') }}", {
                    barcode: articleNum,
                    action: this.action,
                    quantity: qty
                })
                .then(res => {
                    if (res.data.success) {
                        this.beep('success');
                        this.addLog(true, res.data.message, this.action);
                    } else {
                        this.beep('error');
                        this.addLog(false, res.data.message || "Fehler beim Ein-/Ausbuchen.", this.action);
                    }
                })
                .catch(err => {
                    this.beep('error');
                    let errMsg = `Artikel ${articleNum} fehlgeschlagen.`;
                    if (err.response && err.response.data && err.response.data.message) {
                        errMsg = err.response.data.message;
                    }
                    this.addLog(false, errMsg, this.action);
                });
            },

            addLog(success, message, action) {
                const time = new Date().toLocaleTimeString('de-DE');
                this.logs.unshift({
                    success,
                    message,
                    action,
                    time
                });
            },

            clearLogs() {
                this.logs = [];
            }
        };
    }
</script>
@endsection
