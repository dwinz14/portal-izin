@props([
'name' => 'proof_image',
'label' => 'Foto Bukti',
'requiredWhen' => 'false', // ekspresi Alpine dari scope parent, mis. "showProof"
])

@php
$cfg = config('image_compression');
$mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
$mimes = array_values(array_filter(array_unique(array_map(fn ($e) => $mimeMap[$e] ?? null,
$cfg['allowed_extensions']))));
$formatLabel = strtoupper(implode(', ', array_unique(str_replace('jpeg', 'jpg', $cfg['allowed_extensions']))));
$maxLabel = rtrim(rtrim(number_format($cfg['max_upload_kb'] / 1024, 1), '0'), '.') . ' MB';

$options = [
'maxUploadBytes' => $cfg['max_upload_kb'] * 1024,
'targetBytes' => $cfg['target_kb'] * 1024,
'maxDimension' => $cfg['max_dimension'],
'qualityStart' => $cfg['quality_start'],
'qualityStep' => $cfg['quality_step'],
'qualityFloor' => $cfg['quality_floor'],
'allowedMimes' => $mimes,
'allowedLabel' => $formatLabel,
'restoreDraft' => session()->hasOldInput(),
'draftKey' => 'imageUploader:' . request()->path() . ':' . $name,
];

$tool = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs
font-medium transition focus:outline-none focus:ring-2 focus:ring-white/50';
@endphp

<div x-data="imageUploader(@js($options))" x-effect="required = ({!! $requiredWhen !!})"
    @image-uploader-reset.window="$event.detail?.name === @js($name) && remove()"
    @keydown.escape.window="state === 'cropping' && cancelEditor()" class="space-y-2">

    <label for="{{ $name }}" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
        {{ $label }}
        <span x-show="required" class="text-red-500">*</span>
        <span x-show="!required" class="text-gray-400 font-normal">(Opsional)</span>
    </label>

    <input type="file" x-ref="input" id="{{ $name }}" name="{{ $name }}" accept="{{ implode(',', $mimes) }}"
        class="sr-only" @change="handleFile($event.target.files[0])">

    {{-- Dropzone --}}
    <label for="{{ $name }}" x-show="state === 'idle'" @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; handleFile($event.dataTransfer.files[0])"
        :class="dragging ? 'border-primary-500 bg-primary-50/60 dark:bg-slate-700/60' : 'border-gray-300 dark:border-slate-600'"
        class="flex flex-col items-center justify-center gap-1 px-4 py-5 rounded-xl border-2 border-dashed cursor-pointer hover:border-primary-500 bg-gray-50/50 dark:bg-slate-700/30 transition text-center">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Pilih atau seret foto ke sini</span>
        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $formatLabel }} · maks. {{ $maxLabel }}</span>
    </label>

    {{-- Hasil --}}
    <div x-show="state === 'ready'" style="display:none"
        class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800">
        <button type="button" @click="edit()" title="Atur / zoom foto"
            class="shrink-0 rounded-lg overflow-hidden focus:outline-none focus:ring-2 focus:ring-primary-500">
            <img :src="previewUrl" alt="Pratinjau bukti" class="h-16 w-16 object-cover">
        </button>
        <div class="min-w-0 flex-1">
            <p class="truncate text-xs font-semibold text-gray-800 dark:text-gray-100" x-text="resultName"></p>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400" x-text="resultInfo"></p>
        </div>
        <button type="button" @click="edit()"
            class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
            Atur
        </button>
        <button type="button" @click="remove()" title="Hapus foto"
            class="px-2.5 py-1.5 text-xs font-medium rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            Hapus
        </button>
    </div>

    <p x-show="notice" x-text="notice" style="display:none" class="text-[11px] text-amber-600 dark:text-amber-400"></p>
    <p x-show="error && state !== 'cropping' && state !== 'processing'" x-text="error" style="display:none" role="alert"
        class="text-xs font-semibold text-red-600 dark:text-red-400"></p>
    @error($name)
    <p class="text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    {{-- Editor crop (di-teleport ke body agar tidak terpengaruh overflow/transform parent) --}}
    <template x-teleport="body">
        <div x-show="state === 'cropping' || state === 'processing'" x-transition.opacity style="display:none"
            class="fixed inset-0 z-[70] flex flex-col bg-gray-900/95" role="dialog" aria-modal="true"
            aria-label="Atur foto bukti">

            <div class="flex items-center justify-between px-4 py-3 text-white">
                <div>
                    <p class="text-sm font-semibold">Atur Foto Bukti</p>
                    <p class="text-[11px] text-gray-300">Geser untuk memindah, scroll atau cubit untuk zoom, tarik sudut
                        untuk crop.</p>
                </div>
                <button type="button" @click="cancelEditor()" class="p-2 rounded-full hover:bg-white/10"
                    aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="relative flex-1 min-h-0 px-4">
                <div class="h-full w-full">
                    <img x-ref="cropImage" alt="" class="block max-w-full">
                </div>
                <div x-show="state === 'processing'" style="display:none"
                    class="absolute inset-0 grid place-items-center bg-gray-900/70 text-white">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Mengompres foto...
                    </div>
                </div>
            </div>

            <p x-show="error" x-text="error" style="display:none" role="alert"
                class="px-4 pt-2 text-xs text-center text-red-300"></p>

            <div class="flex flex-wrap items-center justify-center gap-2 px-4 pt-3">
                <button type="button" @click="zoomOut()" class="{{ $tool }}">− Zoom</button>
                <button type="button" @click="zoomIn()" class="{{ $tool }}">+ Zoom</button>
                <button type="button" @click="rotate()" class="{{ $tool }}">Putar 90°</button>
                <button type="button" @click="resetCrop()" class="{{ $tool }}">Reset</button>
            </div>

            <div class="flex items-center justify-end gap-2 px-4 py-4">
                <button type="button" @click="cancelEditor()" :disabled="state === 'processing'"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/20 disabled:opacity-50 transition">
                    Batal
                </button>
                <button type="button" @click="confirmCrop()" :disabled="state === 'processing'"
                    class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 disabled:opacity-50 transition">
                    Gunakan Foto
                </button>
            </div>
        </div>
    </template>
</div>