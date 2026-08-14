<div x-data="toastNotification()" class="relative">
    @if (session('success'))
        <div x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-5 right-5 z-50 w-full max-w-sm min-w-0 rounded-md bg-green-50 border border-gray-300 border-l-[6px] border-l-green-600 shadow-md">

            <div class="flex items-start p-3">
                <div class="mr-3 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-green-600 inline" viewBox="0 0 512 512">
                        <ellipse cx="246" cy="246" rx="246" ry="246" />
                        <path class="fill-white"
                            d="m235.472 392.08-121.04-94.296 34.416-44.168 74.328 57.904 122.672-177.016 46.032 31.888z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h6 class="text-green-600 text-[15px] font-semibold tracking-wide">Berhasil</h6>
                    <p class="text-sm text-slate-600 mt-0.5" x-text="message || '{{ session('success') }}'"></p>
                </div>
                <button @click="show = false" type="button"
                    class="ml-4 cursor-pointer border-0 outline-0 bg-transparent">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-3 cursor-pointer shrink-0 fill-gray-500 hover:fill-red-500"
                        viewBox="0 0 320.591 320.591">
                        <path
                            d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z" />
                        <path
                            d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z" />
                    </svg>
                </button>
            </div>
        </div>
    @elseif (session('error'))
        <div x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-5 right-5 z-50 w-full max-w-sm min-w-0 rounded-md bg-red-50 border border-gray-300 border-l-[6px] border-l-red-600 shadow-md">

            <div class="flex items-start p-3">
                <div class="mr-3 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-red-600 inline" viewBox="0 0 32 32">
                        <path
                            d="M16 1a15 15 0 1 0 15 15A15 15 0 0 0 16 1zm6.36 20L21 22.36l-5-4.95-4.95 4.95L9.64 21l4.95-5-4.95-4.95 1.41-1.41L16 14.59l5-4.95 1.41 1.41-5 4.95z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h6 class="text-red-600 text-[15px] font-semibold tracking-wide">Error</h6>
                    <p class="text-sm text-slate-600 mt-0.5" x-text="message || '{{ session('error') }}'"></p>
                </div>
                <button @click="show = false" type="button"
                    class="ml-4 cursor-pointer border-0 outline-0 bg-transparent">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-3 cursor-pointer shrink-0 fill-gray-500 hover:fill-red-500"
                        viewBox="0 0 320.591 320.591">
                        <path
                            d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z" />
                        <path
                            d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Dynamic Toast -->
    <div x-show="dynamicShow" x-init="setTimeout(() => dynamicShow = false, 5000)" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
        :class="dynamicType === 'success' ? 'bg-green-50 border-l-green-600' : 'bg-red-50 border-l-red-600'"
        class="fixed bottom-5 right-5 z-50 w-full max-w-sm min-w-0 rounded-md border border-gray-300 border-l-[6px] shadow-md">

        <div class="flex items-start p-3">
            <div class="mr-3 shrink-0">
                <svg x-show="dynamicType === 'success'" xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 fill-green-600 inline" viewBox="0 0 512 512">
                    <ellipse cx="246" cy="246" rx="246" ry="246" />
                    <path class="fill-white"
                        d="m235.472 392.08-121.04-94.296 34.416-44.168 74.328 57.904 122.672-177.016 46.032 31.888z" />
                </svg>
                <svg x-show="dynamicType === 'error'" xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 fill-red-600 inline" viewBox="0 0 32 32">
                    <path
                        d="M16 1a15 15 0 1 0 15 15A15 15 0 0 0 16 1zm6.36 20L21 22.36l-5-4.95-4.95 4.95L9.64 21l4.95-5-4.95-4.95 1.41-1.41L16 14.59l5-4.95 1.41 1.41-5 4.95z" />
                </svg>
            </div>
            <div class="flex-1">
                <h6 :class="dynamicType === 'success' ? 'text-green-600' : 'text-red-600'"
                    class="text-[15px] font-semibold tracking-wide"
                    x-text="dynamicType === 'success' ? 'Berhasil' : 'Error'"></h6>
                <p class="text-sm text-slate-600 mt-0.5" x-text="dynamicMessage"></p>
            </div>
            <button @click="dynamicShow = false" type="button"
                class="ml-4 cursor-pointer border-0 outline-0 bg-transparent">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-3 cursor-pointer shrink-0 fill-gray-500 hover:fill-red-500" viewBox="0 0 320.591 320.591">
                    <path
                        d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z" />
                    <path
                        d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    function toastNotification() {
        return {
            show: @if (session('success') || session('error'))
                true
            @else
                false
            @endif ,
            message: '',
            dynamicShow: false,
            dynamicType: '',
            dynamicMessage: '',

            init() {
                window.addEventListener('toast-notification', (event) => {
                    this.dynamicType = event.detail.type;
                    this.dynamicMessage = event.detail.message;
                    this.dynamicShow = true;
                });
            }
        }
    }
</script>
