export function formatSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

/** Return string pesan error, atau null jika file valid. */
export function validateFile(file, cfg) {
    const isHeic = /heic|heif/i.test(file.type) || /\.(heic|heif)$/i.test(file.name);

    if (isHeic) {
        return 'Format HEIC belum didukung. Ubah ke JPG, atau ambil screenshot foto tersebut.';
    }
    if (!cfg.allowedMimes.includes(file.type)) {
        return `Format file tidak didukung. Gunakan ${cfg.allowedLabel}.`;
    }
    if (file.size > cfg.maxUploadBytes) {
        return `Ukuran file ${formatSize(file.size)} melebihi batas ${formatSize(cfg.maxUploadBytes)}. Pilih foto yang lebih kecil.`;
    }
    return null;
}

const toBlob = (canvas, quality) =>
    new Promise((resolve, reject) =>
        canvas.toBlob(
            (blob) => (blob ? resolve(blob) : reject(new Error('canvas.toBlob gagal'))),
            'image/jpeg',
            quality
        )
    );

/** Turunkan kualitas bertahap sampai <= target (atau menyentuh batas bawah). */
export async function compressCanvas(canvas, cfg) {
    let quality = cfg.qualityStart; // integer persen, hindari error floating point
    let blob;

    for (; ;) {
        blob = await toBlob(canvas, quality / 100);
        if (blob.size <= cfg.targetBytes || quality <= cfg.qualityFloor) break;
        quality = Math.max(cfg.qualityFloor, quality - cfg.qualityStep);
    }

    return blob;
}

export function blobToFile(blob, originalName) {
    const base = originalName.replace(/\.[^.]+$/, '').replace(/[^\w\-]+/g, '_') || 'bukti';
    return new File([blob], `${base}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
}

/** Masukkan File ke input[type=file]. Return false jika browser tidak mendukung. */
export function setInputFile(input, file) {
    try {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        return true;
    } catch (e) {
        return false;
    }
}

export function fileToDataUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(reader.error);
        reader.readAsDataURL(file);
    });
}

export function dataUrlToFile(dataUrl, name) {
    const [meta, base64] = dataUrl.split(',');
    const type = /data:(.*?);base64/.exec(meta)?.[1] || 'image/jpeg';
    const binary = atob(base64);
    const bytes = new Uint8Array(binary.length);

    for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);

    return new File([bytes], name, { type, lastModified: Date.now() });
}