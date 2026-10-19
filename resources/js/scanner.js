import { Html5Qrcode } from 'html5-qrcode';

class LokerMandiriApp {
    constructor() {
        this.barcodeInput = document.getElementById('barcodeInput');
        this.statusIndicator = document.getElementById('statusIndicator');
        this.resultModal = document.getElementById('resultModal');
        this.genderModal = document.getElementById('genderModal');
        this.detailModal = document.getElementById('detailModal');
        this.cameraModal = document.getElementById('cameraModal');

        this.html5QrCode = null;
        this.isScanning = false;
        this.isProcessing = false;
        this.pendingBarcode = null;
        this.audioCtx = null;
        this.activeLockerBox = null;
        this.resultTimeout = null;

        this.init();
    }

    init() {
        this.setupKeyboardScanner();
        this.setupCameraScanner();
        this.setupModals();
        this.setupAudio();
        this.setupLockerClicks();
        this.startPeriodicSync();
        this.ensureInputFocus();
    }

    /* ----------------------------------------------------
     * AUDIO & SPEECH SYNTHESIS (INDONESIAN)
     * ---------------------------------------------------- */
    setupAudio() {
        window.addEventListener('click', () => {
            if (!this.audioCtx) {
                const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
                if (AudioCtxClass) {
                    this.audioCtx = new AudioCtxClass();
                }
            }
        }, { once: true });
    }

    playChime(type = 'success') {
        try {
            const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
            if (!this.audioCtx && AudioCtxClass) {
                this.audioCtx = new AudioCtxClass();
            }
            if (!this.audioCtx) return;

            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            osc.connect(gain);
            gain.connect(this.audioCtx.destination);

            const now = this.audioCtx.currentTime;

            if (type === 'success') {
                // Two-tone pleasant chime (C5 -> G5)
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now); // C5
                osc.frequency.setValueAtTime(783.99, now + 0.12); // G5
                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                osc.start(now);
                osc.stop(now + 0.45);
            } else {
                // Low buzz error
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(220, now);
                gain.gain.setValueAtTime(0.25, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc.start(now);
                osc.stop(now + 0.35);
            }
        } catch (e) {
            console.warn('Audio chime error:', e);
        }
    }

    speak(text) {
        if (!('speechSynthesis' in window) || !text) return;

        try {
            window.speechSynthesis.cancel(); // Stop prior speeches

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.95;
            utterance.pitch = 1.0;

            const voices = window.speechSynthesis.getVoices();
            const indonesianVoice = voices.find(v => v.lang.startsWith('id') || v.lang.includes('IND'));
            if (indonesianVoice) {
                utterance.voice = indonesianVoice;
            }

            window.speechSynthesis.speak(utterance);
        } catch (e) {
            console.warn('Speech synthesis error:', e);
        }
    }

    /* ----------------------------------------------------
     * KEYBOARD / USB SCANNER HANDLING
     * ---------------------------------------------------- */
    setupKeyboardScanner() {
        if (!this.barcodeInput) return;

        // Auto submit saat scanner mengirim Enter
        this.barcodeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = this.barcodeInput.value.trim();
                if (barcode && !this.isProcessing) {
                    this.sendScanRequest(barcode);
                }
            }
        });

        // Global key listener agar scanner yang mengirim langsung tetap tertangkap
        window.addEventListener('keydown', (e) => {
            // Abaikan jika fokus pada input manual lain atau sedang membuka dialog
            if (e.target.tagName === 'INPUT' && e.target !== this.barcodeInput) {
                return;
            }
            if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                return;
            }

            // Jika barcodeInput tidak fokus, fokuskan dan teruskan karakter
            if (document.activeElement !== this.barcodeInput && !this.isModalOpen()) {
                this.barcodeInput.focus();
            }
        });

        // Tombol manual scan jika diinput dengan tombol
        const manualBtn = document.getElementById('manualScanBtn');
        if (manualBtn) {
            manualBtn.addEventListener('click', () => {
                const barcode = this.barcodeInput.value.trim();
                if (barcode && !this.isProcessing) {
                    this.sendScanRequest(barcode);
                }
            });
        }
    }

    ensureInputFocus() {
        setInterval(() => {
            if (!this.isModalOpen() && !this.isScanning && document.activeElement !== this.barcodeInput) {
                this.barcodeInput?.focus();
            }
        }, 1500);
    }

    isModalOpen() {
        return (
            (this.genderModal && !this.genderModal.classList.contains('hidden')) ||
            (this.detailModal && !this.detailModal.classList.contains('hidden')) ||
            (this.cameraModal && !this.cameraModal.classList.contains('hidden'))
        );
    }

    /* ----------------------------------------------------
     * CAMERA SCANNER (HTML5-QRCODE)
     * ---------------------------------------------------- */
    setupCameraScanner() {
        const openCameraBtn = document.getElementById('openCameraBtn');
        const closeCameraBtn = document.getElementById('closeCameraBtn');

        if (openCameraBtn) {
            openCameraBtn.addEventListener('click', () => this.startCamera());
        }
        if (closeCameraBtn) {
            closeCameraBtn.addEventListener('click', () => this.stopCamera());
        }
    }

    async startCamera() {
        if (this.isScanning) return;

        this.cameraModal?.classList.remove('hidden');
        this.isScanning = true;

        try {
            if (!this.html5QrCode) {
                this.html5QrCode = new Html5Qrcode('cameraReader');
            }

            const config = {
                fps: 10,
                qrbox: { width: 280, height: 280 },
                aspectRatio: 1.0,
            };

            await this.html5QrCode.start(
                { facingMode: 'environment' },
                config,
                (decodedText) => {
                    // Berhasil scan dari kamera
                    if (!this.isProcessing && decodedText.trim() !== '') {
                        this.stopCamera();
                        this.sendScanRequest(decodedText.trim());
                    }
                },
                (errorMessage) => {
                    // Frame scan biasa tidak terdeteksi, abaikan
                }
            );
        } catch (err) {
            console.error('Kamera gagal dibuka:', err);
            const errMsg = 'Tidak dapat mengakses kamera: ' + (err.message || 'Periksa izin kamera Anda.');
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Akses Kamera',
                    text: errMsg,
                    confirmButtonColor: '#0060b2'
                });
            } else {
                alert(errMsg);
            }
            this.stopCamera();
        }
    }

    async stopCamera() {
        if (this.html5QrCode && this.isScanning) {
            try {
                await this.html5QrCode.stop();
            } catch (e) {
                console.warn('Stop camera error:', e);
            }
        }
        this.isScanning = false;
        this.cameraModal?.classList.add('hidden');
        this.barcodeInput?.focus();
    }

    /* ----------------------------------------------------
     * SERVER REQUEST & TRANSACTIONS
     * ---------------------------------------------------- */
    async sendScanRequest(barcode, genderChoice = null) {
        if (this.isProcessing) return;

        this.isProcessing = true;
        this.setIndicator('Memproses data...', 'processing');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const idempotencyKey = 'req_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);

        try {
            const response = await fetch('/api/loker/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    barcode: barcode,
                    gender: genderChoice,
                    idempotency_key: idempotencyKey,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errMsg = data.message || 'Terjadi kesalahan sistem.';
                this.showErrorResult(errMsg);
                return;
            }

            const res = data.data;

            // Kasus khusus: Karyawan belum memiliki data gender
            if (res.action === 'requires_gender') {
                this.pendingBarcode = barcode;
                this.showGenderModal(res.karyawan, res.message);
                return;
            }

            // Transaksi Sukses: Alokasi atau Pelepasan
            this.showSuccessResult(res);

        } catch (error) {
            console.error('Fetch scan error:', error);
            this.showErrorResult('Gagal terhubung ke server loker.');
        } finally {
            this.isProcessing = false;
            if (this.barcodeInput) {
                this.barcodeInput.value = '';
            }
        }
    }

    /* ----------------------------------------------------
     * MODAL & VISUAL FEEDBACK
     * ---------------------------------------------------- */
    showGenderModal(karyawan, message) {
        const nameEl = document.getElementById('genderKaryawanName');
        const descEl = document.getElementById('genderKaryawanDesc');

        if (nameEl) nameEl.textContent = karyawan.nama || karyawan.nik;
        if (descEl) descEl.textContent = message || 'Silakan pilih kelompok loker Anda.';

        this.genderModal?.classList.remove('hidden');
        this.setIndicator('Menunggu pemilihan gender...', 'warning');
        this.speak('Silakan pilih kelompok loker Anda.');
    }

    showSuccessResult(res) {
        this.genderModal?.classList.add('hidden');
        this.playChime('success');

        const titleEl = document.getElementById('resultTitle');
        const numberEl = document.getElementById('resultLockerNumber');
        const codeEl = document.getElementById('resultLockerCode');
        const subtitleEl = document.getElementById('resultStatusSubtitle');
        const nameEl = document.getElementById('resultKaryawanName');

        const isAllocated = res.action === 'allocated';

        if (titleEl) {
            titleEl.textContent = isAllocated ? 'Selamat Datang' : 'Terima Kasih';
        }

        if (numberEl) {
            numberEl.textContent = res.loker.nomor_urut;
            numberEl.className = isAllocated
                ? 'text-8xl sm:text-9xl font-black text-[#d92534] tracking-tight leading-none block my-2'
                : 'text-8xl sm:text-9xl font-black text-[#0060b2] tracking-tight leading-none block my-2';
        }

        if (codeEl) {
            const genderLabel = res.loker.gender_group === 'L' ? 'Laki-laki' : 'Perempuan';
            codeEl.textContent = `Loker ${res.loker.kode_loker} (${genderLabel})`;
        }

        if (subtitleEl) {
            subtitleEl.textContent = isAllocated ? 'Kunci Berhasil Diberikan' : 'Kunci Berhasil Dikembalikan';
            subtitleEl.className = 'mt-3 text-base sm:text-lg font-extrabold text-emerald-600 px-4 py-1.5 bg-emerald-50 border border-emerald-200 rounded-full inline-block';
        }

        if (nameEl) {
            nameEl.textContent = res.karyawan.nama;
        }

        this.resultModal?.classList.remove('hidden');
        this.setIndicator(res.message, isAllocated ? 'success' : 'info');

        // Bacakan suara Text-to-Speech
        this.speak(res.speech_text);

        // Update sel di peta loker secara real-time
        this.updateSingleLockerInDom(res.loker.id, res.loker.gender_group, res.loker.nomor_urut, isAllocated ? 'occupied' : 'available', isAllocated ? res.karyawan.nama : null);

        // Bersihkan timer sebelumnya jika karyawan berikutnya langsung scan cepat
        if (this.resultTimeout) {
            clearTimeout(this.resultTimeout);
        }

        // Tutup otomatis hasil modal setelah 5 detik (atau langsung tertimpa jika ada scan baru)
        this.resultTimeout = setTimeout(() => {
            this.closeResultModal();
        }, 5000);
    }

    showErrorResult(message) {
        this.playChime('error');
        if (window.Swal) {
            window.Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                text: message,
                confirmButtonColor: '#d92534',
                timer: 3500,
                timerProgressBar: true
            });
        } else {
            alert(message);
        }
        this.setIndicator(message, 'error');
        this.barcodeInput?.focus();
    }

    closeResultModal() {
        this.resultModal?.classList.add('hidden');
        this.setIndicator('Siap memindai kartu barcode...', 'ready');
        this.barcodeInput?.focus();
    }

    setupModals() {
        // Klik pada backdrop modal hasil untuk menutup modal
        this.resultModal?.addEventListener('click', (e) => {
            if (e.target === this.resultModal) {
                this.closeResultModal();
            }
        });

        // Tombol tutup modal hasil (jika ada)
        document.getElementById('closeResultBtn')?.addEventListener('click', () => {
            this.closeResultModal();
        });

        // Tombol pilihan gender
        document.getElementById('chooseMaleBtn')?.addEventListener('click', () => {
            if (this.pendingBarcode) {
                const b = this.pendingBarcode;
                this.pendingBarcode = null;
                this.genderModal?.classList.add('hidden');
                this.sendScanRequest(b, 'L');
            }
        });

        document.getElementById('chooseFemaleBtn')?.addEventListener('click', () => {
            if (this.pendingBarcode) {
                const b = this.pendingBarcode;
                this.pendingBarcode = null;
                this.genderModal?.classList.add('hidden');
                this.sendScanRequest(b, 'P');
            }
        });

        document.getElementById('cancelGenderBtn')?.addEventListener('click', () => {
            this.pendingBarcode = null;
            this.genderModal?.classList.add('hidden');
            this.setIndicator('Transaksi dibatalkan.', 'ready');
            this.barcodeInput?.focus();
        });

        // Tutup modal detail loker
        document.getElementById('closeDetailBtn')?.addEventListener('click', () => {
            this.detailModal?.classList.add('hidden');
            this.barcodeInput?.focus();
        });
    }

    setIndicator(text, status = 'ready') {
        if (!this.statusIndicator) return;
        this.statusIndicator.textContent = text;

        const colors = {
            ready: 'text-slate-600',
            processing: 'text-amber-600',
            success: 'text-emerald-700 font-semibold',
            info: 'text-blue-700 font-semibold',
            warning: 'text-amber-700 font-semibold',
            error: 'text-rose-700 font-semibold',
        };

        this.statusIndicator.className = 'text-xs transition-colors ' + (colors[status] || colors.ready);
    }

    /* ----------------------------------------------------
     * LOCKER MAP CLICKS & STATUS UPDATES
     * ---------------------------------------------------- */
    setupLockerClicks() {
        document.querySelectorAll('.loker-box').forEach(el => {
            el.addEventListener('click', () => {
                this.openLockerDetail(el);
            });
        });
    }

    openLockerDetail(el) {
        this.activeLockerBox = el;
        const id = el.dataset.id;
        const kode = el.dataset.kode;
        const nomor = el.dataset.nomor;
        const gender = el.dataset.gender === 'L' ? 'Laki-laki' : 'Perempuan';
        const status = el.dataset.status;
        const karyawan = el.dataset.karyawan || '-';

        document.getElementById('detailLockerCode').textContent = kode;
        document.getElementById('detailLockerNumber').textContent = nomor;
        document.getElementById('detailLockerGender').textContent = gender;
        document.getElementById('detailLockerStatus').textContent =
            status === 'occupied' ? 'Terisi (' + karyawan + ')' :
                status === 'disabled' ? 'Dinonaktifkan / Dikunci Admin' : 'Tersedia';

        this.detailModal?.classList.remove('hidden');
    }

    updateSingleLockerInDom(id, genderGroup, nomorUrut, newStatus, karyawanName) {
        const el = document.querySelector(`.loker-box[data-id="${id}"]`) ||
            document.querySelector(`.loker-box[data-gender="${genderGroup}"][data-nomor="${nomorUrut}"]`);

        if (!el) return;

        el.classList.remove('status-available', 'status-occupied', 'status-disabled', 'status-allocated-just-now');
        el.classList.add(`status-${newStatus}`);
        el.dataset.status = newStatus;
        el.dataset.karyawan = karyawanName || '';

        if (newStatus === 'occupied') {
            el.classList.add('status-allocated-just-now');
            setTimeout(() => {
                el.classList.remove('status-allocated-just-now');
            }, 5000);
        }

        this.updateCounters();
    }

    updateCounters() {
        const maleAvail = document.querySelectorAll('.male-grid .loker-box.status-available').length;
        const femaleAvail = document.querySelectorAll('.female-grid .loker-box.status-available').length;

        const maleBadge = document.getElementById('maleAvailableCount');
        const femaleBadge = document.getElementById('femaleAvailableCount');

        if (maleBadge) maleBadge.textContent = `${maleAvail} / 100 Tersedia`;
        if (femaleBadge) femaleBadge.textContent = `${femaleAvail} / 100 Tersedia`;
    }

    startPeriodicSync() {
        setInterval(() => {
            if (!this.isProcessing && !this.isModalOpen()) {
                this.refreshStatusMap();
            }
        }, 15000); // Sinkronisasi setiap 15 detik
    }

    async refreshStatusMap() {
        try {
            const res = await fetch('/api/loker/status');
            const data = await res.json();
            if (!data.success) return;

            // Update Male Lockers
            data.male.forEach(item => {
                const el = document.querySelector(`.loker-box[data-gender="L"][data-nomor="${item.nomor}"]`);
                if (el) {
                    el.classList.remove('status-available', 'status-occupied', 'status-disabled');
                    el.classList.add(`status-${item.status}`);
                    el.dataset.status = item.status;
                    el.dataset.karyawan = item.karyawan_nama || '';
                }
            });

            // Update Female Lockers
            data.female.forEach(item => {
                const el = document.querySelector(`.loker-box[data-gender="P"][data-nomor="${item.nomor}"]`);
                if (el) {
                    el.classList.remove('status-available', 'status-occupied', 'status-disabled');
                    el.classList.add(`status-${item.status}`);
                    el.dataset.status = item.status;
                    el.dataset.karyawan = item.karyawan_nama || '';
                }
            });

            this.updateCounters();
        } catch (e) {
            console.warn('Periodic sync failed:', e);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.lokerApp = new LokerMandiriApp();
    window.kiosk = window.lokerApp;
});
