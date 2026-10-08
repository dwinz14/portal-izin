import { validateFile, formatSize, compressCanvas, blobToFile, setInputFile, fileToDataUrl, dataUrlToFile } from './pipeline';

export default function imageUploader(cfg) {
    return {
        cfg,
        required: false,
        state: 'idle', // idle | cropping | processing | ready
        error: '',
        notice: '',
        dragging: false,

        cropper: null,
        originalFile: null,
        resultFile: null,
        sourceUrl: '',
        previewUrl: '',
        resultName: '',
        resultInfo: '',

        get input() {
            return this.$refs.input;
        },

        init() {
            this.$watch('required', () => this.syncValidity());
            this.$watch('state', (s) => {
                document.body.classList.toggle('overflow-hidden', s === 'cropping' || s === 'processing');
                this.syncValidity();
            });

            // Setelah validasi server gagal, halaman dimuat ulang dan <input type=file> pasti kosong
            // (batasan browser). Pulihkan foto dari draf sementara. Pada kunjungan baru, draf lama dibuang.
            this.$nextTick(() => {
                if (this.cfg.restoreDraft) this.restoreDraft();
                else this.clearDraft();
                this.syncValidity();
            });
        },

        destroy() {
            this.cropper?.destroy();
            this.revoke('sourceUrl');
            this.revoke('previewUrl');
            document.body.classList.remove('overflow-hidden');
        },

        // Validasi native browser: memblokir submit & menampilkan bubble di input.
        syncValidity() {
            if (!this.input) return;
            let msg = '';

            if (this.state === 'cropping' || this.state === 'processing') {
                msg = 'Selesaikan pengaturan foto bukti terlebih dahulu.';
            } else if (this.required && !this.input.files?.length) {
                msg = 'Foto bukti wajib diunggah.';
            }

            this.input.setCustomValidity(msg);
        },

        revoke(key) {
            if (this[key]) {
                URL.revokeObjectURL(this[key]);
                this[key] = '';
            }
        },

        // ── Entry point: dari file picker maupun drag & drop ──
        handleFile(file) {
            if (!file) return;

            this.error = '';
            this.notice = '';

            const problem = validateFile(file, this.cfg);
            if (problem) return this.reject(problem);

            this.startEditor(file);
        },

        async startEditor(file) {
            this.originalFile = file;
            this.revoke('sourceUrl');
            this.sourceUrl = URL.createObjectURL(file);
            this.state = 'cropping';

            let Cropper;
            try {
                ({ default: Cropper } = await import('cropperjs'));
                await import('cropperjs/dist/cropper.css');
            } catch (e) {
                console.error('[imageUploader] Cropper gagal dimuat', e);
                return this.useOriginalWithoutEditor();
            }

            await this.$nextTick();
            const img = this.$refs.cropImage;

            try {
                img.src = this.sourceUrl;
                await img.decode();
            } catch (e) {
                console.error('[imageUploader] Gambar tidak bisa didecode', e);
                return this.reject('Gambar tidak dapat dibaca. File mungkin rusak, coba pilih foto lain.');
            }

            this.cropper?.destroy();
            this.cropper = new Cropper(img, {
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                background: false,
                guides: true,
                center: true,
                highlight: false,
                restore: false,
                scalable: false,
                toggleDragModeOnDblclick: false,
                checkOrientation: true, // baca EXIF, foto HP tidak miring
            });
        },

        async confirmCrop() {
            if (!this.cropper || this.state !== 'cropping') return;

            this.error = '';
            this.state = 'processing';

            try {
                await new Promise((r) => requestAnimationFrame(() => setTimeout(r, 0))); // biar spinner sempat tampil

                const canvas = this.cropper.getCroppedCanvas({
                    maxWidth: this.cfg.maxDimension,
                    maxHeight: this.cfg.maxDimension,
                    fillColor: '#fff', // PNG transparan -> latar putih
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });
                if (!canvas) throw new Error('Canvas kosong');

                const blob = await compressCanvas(canvas, this.cfg);
                canvas.width = canvas.height = 0; // bebaskan memori (penting di Safari/HP)

                if (blob.size > this.cfg.maxUploadBytes) throw new Error('Hasil masih terlalu besar');

                const file = blobToFile(blob, this.originalFile.name);

                if (!setInputFile(this.input, file)) {
                    // Browser lama: kirim file asli, server yang mengompres.
                    return this.useOriginalWithoutEditor();
                }

                this.setResult(file);
                this.closeEditor();
                this.state = 'ready';
            } catch (e) {
                console.error('[imageUploader] Gagal memproses', e);
                this.state = 'cropping';
                this.error = 'Gagal memproses gambar. Coba lagi atau pilih foto lain.';
            }
        },

        useOriginalWithoutEditor() {
            this.closeEditor();
            setInputFile(this.input, this.originalFile);
            this.setResult(this.originalFile);
            this.notice = 'Foto dikirim tanpa pengaturan di browser, server akan mengompresnya otomatis.';
            this.state = 'ready';
        },

        setResult(file, persist = true) {
            this.revoke('previewUrl');
            this.resultFile = file;
            this.previewUrl = URL.createObjectURL(file);
            this.resultName = file.name;

            const before = this.originalFile?.size ?? file.size;
            this.resultInfo =
                before > file.size
                    ? `Dikompres dari ${formatSize(before)} menjadi ${formatSize(file.size)}`
                    : `Ukuran ${formatSize(file.size)}`;

            if (persist) this.saveDraft(file);
        },

        closeEditor() {
            this.cropper?.destroy();
            this.cropper = null;
            this.revoke('sourceUrl');
        },

        // Kembalikan ke hasil sebelumnya (jika ada), atau kosongkan.
        restore() {
            if (this.resultFile && setInputFile(this.input, this.resultFile)) {
                this.state = 'ready';
            } else {
                this.input.value = '';
                this.resultFile = null;
                this.state = 'idle';
            }
        },

        cancelEditor() {
            if (this.state === 'processing') return;
            this.closeEditor();
            this.error = '';
            this.restore();
        },

        reject(message) {
            this.closeEditor();
            this.restore();
            this.error = message;
        },

        edit() {
            if (this.originalFile) {
                this.error = '';
                this.startEditor(this.originalFile);
            }
        },

        remove() {
            this.clearDraft();
            this.revoke('previewUrl');
            this.input.value = '';
            this.originalFile = null;
            this.resultFile = null;
            this.error = '';
            this.notice = '';
            this.state = 'idle';
        },

        // ── Draf sementara (sessionStorage, hanya hidup selama tab terbuka) ──
        async saveDraft(file) {
            try {
                const dataUrl = await fileToDataUrl(file);
                sessionStorage.setItem(this.cfg.draftKey, JSON.stringify({ name: file.name, dataUrl }));
            } catch (e) {
                // Storage penuh/diblokir: abaikan, fitur ini hanya untuk kenyamanan.
            }
        },

        restoreDraft() {
            try {
                const raw = sessionStorage.getItem(this.cfg.draftKey);
                if (!raw) return;

                const draft = JSON.parse(raw);
                const file = dataUrlToFile(draft.dataUrl, draft.name);

                if (file.size > this.cfg.maxUploadBytes || !setInputFile(this.input, file)) {
                    return this.clearDraft();
                }

                this.originalFile = file;
                this.setResult(file, false);
                this.state = 'ready';
            } catch (e) {
                this.clearDraft();
            }
        },

        clearDraft() {
            try {
                sessionStorage.removeItem(this.cfg.draftKey);
            } catch (e) { }
        },

        // ── Kontrol editor ──
        zoomIn() { this.cropper?.zoom(0.1); },
        zoomOut() { this.cropper?.zoom(-0.1); },
        rotate() { this.cropper?.rotate(90); },
        resetCrop() { this.cropper?.reset(); },
    };
}