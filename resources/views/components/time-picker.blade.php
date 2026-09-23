@props([
    'name', // Nama field untuk dikirim ke server
    'id' => null, // Optional, default ke name
    'value' => '', // Pre-filled value (format HH:MM)
    'label' => '', // Label teks (opsional, untuk aria)
    'required' => false,
    'disabled' => false,
    'placeholder' => 'HH : MM',
    'xModelKey' => null, // Nama property Alpine.js di parent (untuk event bubbling)
])

@php
    $inputId = $id ?? $name;
    $safeId = str_replace(['.', '[', ']'], '_', $inputId);
    $alpineId = 'timePicker_' . $safeId;
@endphp

<div x-data="timePicker_component('{{ $safeId }}', @js($value), @js($xModelKey))" x-id="['{{ $alpineId }}']" class="relative w-full"
    @keydown.escape.window="closeIfFocused()">
    {{-- Hidden input yang dikirim ke server --}}
    <input type="hidden" name="{{ $name }}" :value="hiddenValue"
        @if ($required) x-bind:required="!hiddenValue" @endif />

    {{-- Trigger: Styled button / input display --}}
    <button type="button" @click="toggle()" @keydown.space.prevent="toggle()" :aria-expanded="open.toString()"
        :aria-controls="'panel_{{ $safeId }}'" aria-haspopup="true" :disabled="{{ $disabled ? 'true' : 'false' }}"
        class="group relative flex w-full items-center rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3.5 py-2.5 text-left text-sm shadow-sm transition-all duration-200
               hover:border-primary-400 dark:hover:border-primary-500
               focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20
               disabled:opacity-60 disabled:cursor-not-allowed
               {{ $disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }}"
        :class="open ? 'border-primary-500 ring-2 ring-primary-500/20' : ''">
        {{-- Clock icon --}}
        <span
            class="mr-2.5 flex-shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
            :class="displayTime ? 'text-primary-500 dark:text-primary-400' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>

        {{-- Display value --}}
        <span class="flex-1 font-mono tracking-widest text-sm"
            :class="displayTime ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-400 dark:text-gray-500'"
            x-text="displayTime || '{{ $placeholder }}'"></span>

        {{-- Clear button --}}
        <span x-show="displayTime && !{{ $disabled ? 'true' : 'false' }}" x-cloak @click.stop="clearTime()"
            role="button" tabindex="0" @keydown.enter.stop="clearTime()" aria-label="Hapus waktu"
            class="ml-1 mr-1 flex-shrink-0 rounded-full p-0.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors hover:bg-red-50 dark:hover:bg-red-900/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </span>

        {{-- Chevron --}}
        <span class="flex-shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200"
            :class="open ? 'rotate-180' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    {{-- Dropdown Panel --}}
    <div id="panel_{{ $safeId }}" x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95" @click.outside="close()"
        class="absolute left-0 top-full z-50 mt-2 w-full min-w-[220px] overflow-hidden rounded-2xl border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-xl shadow-gray-200/60 dark:shadow-black/30 origin-top"
        role="dialog" aria-label="Pilih Waktu">
        {{-- Panel Header --}}
        <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-slate-700">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pilih
                Waktu</span>
            <div
                class="font-mono text-base font-bold tracking-widest text-primary-600 dark:text-primary-400 min-w-[64px] text-center tabular-nums">
                <span x-text="pad(selectedHour)">00</span>
                <span class="opacity-60 animate-pulse">:</span>
                <span x-text="pad(selectedMinute)">00</span>
            </div>
        </div>

        {{-- Column Labels --}}
        <div class="grid grid-cols-2 border-b border-gray-100 dark:border-slate-700">
            <div
                class="py-1.5 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 border-r border-gray-100 dark:border-slate-700">
                Jam</div>
            <div
                class="py-1.5 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                Menit (00-59)</div>
        </div>

        {{-- Scrollable Columns --}}
        <div class="grid grid-cols-2" style="height: 224px;">

            {{-- Kolom Jam: 00 – 23 --}}
            <div class="overflow-y-auto overscroll-contain border-r border-gray-100 dark:border-slate-700 scroll-smooth"
                style="height: 224px;" x-ref="hourCol">
                <template x-for="h in 24" :key="h - 1">
                    <button type="button" @click="selectHour(h - 1)"
                        :class="selectedHour === (h - 1) ?
                            'bg-primary-600 text-white font-bold shadow-sm' :
                            'text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-300'"
                        class="w-full py-2.5 text-center text-sm font-mono transition-colors duration-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500/40"
                        :aria-pressed="selectedHour === (h - 1)" x-text="pad(h - 1)"></button>
                </template>
            </div>

            {{-- Kolom Menit: 00 – 59 --}}
            <div class="overflow-y-auto overscroll-contain scroll-smooth" style="height: 224px;" x-ref="minCol">
                <template x-for="m in 60" :key="m - 1">
                    <button type="button" @click="selectMinute(m - 1)"
                        :class="selectedMinute === (m - 1) ?
                            'bg-primary-600 text-white font-bold shadow-sm' :
                            'text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-300'"
                        class="w-full py-2.5 text-center text-sm font-mono transition-colors duration-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500/40"
                        :aria-pressed="selectedMinute === (m - 1)" x-text="pad(m - 1)"></button>
                </template>
            </div>
        </div>

        {{-- Confirm Button --}}
        <div class="p-3 border-t border-gray-100 dark:border-slate-700">
            <button type="button" @click="confirm()"
                class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold py-1.5 px-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 shadow-sm">
                <span class="mr-1">✓</span> Pilih
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        if (window.__timePickerComponentDefined) return;
        window.__timePickerComponentDefined = true;

        window.timePicker_component = function(uid, initialValue, xModelKey) {
            return {
                uid,
                open: false,
                selectedHour: 0,
                selectedMinute: 0,
                displayTime: '',
                hiddenValue: '',

                init() {
                    if (initialValue && /^\d{2}:\d{2}$/.test(initialValue)) {
                        const [h, m] = initialValue.split(':').map(Number);
                        this.selectedHour = h;
                        this.selectedMinute = m;
                        this.displayTime = this.pad(h) + ':' + this.pad(m);
                        this.hiddenValue = this.displayTime;
                    }

                    if (xModelKey) {
                        this.$watch('hiddenValue', val => {
                            this.$el.dispatchEvent(new CustomEvent('time-changed', {
                                detail: {
                                    key: xModelKey,
                                    value: val
                                },
                                bubbles: true,
                            }));
                        });
                    }
                },

                pad(n) {
                    return String(n).padStart(2, '0');
                },

                toggle() {
                    if (this.open) {
                        this.close();
                    } else {
                        this.open = true;
                        this.$nextTick(() => this.scrollToSelected());
                    }
                },

                close() {
                    this.open = false;
                },

                closeIfFocused() {
                    if (this.open) this.close();
                },

                scrollToSelected() {
                    this.$nextTick(() => {
                        const hCol = this.$refs.hourCol;
                        if (hCol) {
                            const hBtn = hCol.children[this.selectedHour];
                            if (hBtn) hBtn.scrollIntoView({
                                block: 'center',
                                behavior: 'smooth'
                            });
                        }
                        const mCol = this.$refs.minCol;
                        if (mCol) {
                            const mBtn = mCol.children[this.selectedMinute];
                            if (mBtn) mBtn.scrollIntoView({
                                block: 'center',
                                behavior: 'smooth'
                            });
                        }
                    });
                },

                selectHour(h) {
                    this.selectedHour = h;
                },

                selectMinute(m) {
                    this.selectedMinute = m;
                },

                confirm() {
                    const val = this.pad(this.selectedHour) + ':' + this.pad(this.selectedMinute);
                    this.displayTime = val;
                    this.hiddenValue = val;
                    this.close();
                },

                clearTime() {
                    this.displayTime = '';
                    this.hiddenValue = '';
                    this.selectedHour = 0;
                    this.selectedMinute = 0;
                },
            };
        };
    })();
</script>
