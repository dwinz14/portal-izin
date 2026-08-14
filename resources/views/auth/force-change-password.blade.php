<x-app-layout>
    <!-- Force Password Change Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true">
            </div>

            <!-- Modal panel -->
            <div
                class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-stone-200"
                                id="modal-title">
                                {{ __('Keamanan Akun') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('Untuk melanjutkan, Anda diharuskan mengubah password Anda. Pastikan password baru Anda kuat dan aman.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form method="post" action="{{ route('password.update') }}" class="w-full">
                        @csrf
                        @method('put')

                        <div class="space-y-4">
                            <!-- Current Password -->
                            <div>
                                <x-input-label for="current_password" :value="__('Password Saat Ini')" />
                                <div class="relative mt-1">
                                    <x-text-input id="current_password" name="current_password" type="password"
                                        class="block w-full pr-12" autocomplete="current-password"
                                        placeholder="Masukkan password saat ini" />
                                    <button type="button"
                                        class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors select-none"
                                        data-target="current_password">
                                        <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <!-- New Password -->
                            <div>
                                <x-input-label for="password" :value="__('Password Baru')" />
                                <div class="relative mt-1">
                                    <x-text-input id="password" name="password" type="password"
                                        class="block w-full pr-12" autocomplete="new-password"
                                        placeholder="Masukkan password baru" />
                                    <button type="button"
                                        class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors select-none"
                                        data-target="password">
                                        <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                                <div id="password-validation" class="mt-1 text-sm" style="display: none;">
                                    <div id="password-rule-1">Karakter pertama harus huruf besar</div>
                                    <div id="password-rule-2">Minimal 8 karakter</div>
                                    <div id="password-rule-3">Harus ada karakter angka</div>
                                    <div id="password-rule-4">Harus ada karakter simbol</div>
                                </div>
                            </div>

                            <!-- Confirm New Password -->
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                                <div class="relative mt-1">
                                    <x-text-input id="password_confirmation" name="password_confirmation"
                                        type="password" class="block w-full pr-12" autocomplete="new-password"
                                        placeholder="Konfirmasi password baru" />
                                    <button type="button"
                                        class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors select-none"
                                        data-target="password_confirmation">
                                        <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <div id="password-match-indicator" class="mt-2 text-sm hidden">
                                    <span id="match-text"></span>
                                </div>
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-4 flex justify-center">
                            <x-primary-button class="w-full sm:w-auto text-center">
                                {{ __('Perbarui Password') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prevent closing the modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
            }
        });

        // Prevent clicking outside the modal to close it
        document.querySelector('.fixed.inset-0.bg-gray-500').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const validationDiv = document.getElementById('password-validation');
            if (password.trim() === '') {
                validationDiv.style.display = 'none';
                return;
            }
            validationDiv.style.display = 'block';
            const rules = [{
                    id: 'password-rule-1',
                    regex: /[A-Z]/,
                    message: 'Harus mengandung minimal 1 huruf besar'
                },
                {
                    id: 'password-rule-2',
                    regex: /.{8,}/,
                    message: 'Minimal 8 karakter'
                },
                {
                    id: 'password-rule-3',
                    regex: /\d/,
                    message: 'Harus ada karakter angka'
                },
                {
                    id: 'password-rule-4',
                    regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/,
                    message: 'Harus ada karakter simbol'
                }
            ];

            rules.forEach(rule => {
                const element = document.getElementById(rule.id);
                if (rule.regex.test(password)) {
                    element.className = 'text-green-600';
                } else {
                    element.className = 'text-red-600';
                }
            });
        });

        (function() {
            // Click & Hold untuk semua password toggle buttons
            document.querySelectorAll('.password-toggle').forEach(function(btn) {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const eyeOpen = btn.querySelector('.eye-open');
                const eyeClosed = btn.querySelector('.eye-closed');

                let isShowing = false;

                function showPassword() {
                    if (!isShowing && input) {
                        input.type = 'text';
                        eyeOpen.classList.add('hidden');
                        eyeClosed.classList.remove('hidden');
                        isShowing = true;
                    }
                }

                function hidePassword() {
                    if (isShowing && input) {
                        input.type = 'password';
                        eyeOpen.classList.remove('hidden');
                        eyeClosed.classList.add('hidden');
                        isShowing = false;
                    }
                }

                // Mouse events
                btn.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    showPassword();
                });
                btn.addEventListener('mouseup', hidePassword);
                btn.addEventListener('mouseleave', hidePassword);

                // Touch events
                btn.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    showPassword();
                });
                btn.addEventListener('touchend', hidePassword);
                btn.addEventListener('touchcancel', hidePassword);

                // Prevent context menu
                btn.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                });
            });

            // Password confirmation match indicator
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const matchIndicator = document.getElementById('password-match-indicator');
            const matchText = document.getElementById('match-text');

            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirm = confirmInput.value;

                if (confirm.length === 0) {
                    matchIndicator.classList.add('hidden');
                    confirmInput.classList.remove('border-red-500', 'border-green-500', 'focus:border-red-500',
                        'focus:ring-red-500', 'focus:border-green-500', 'focus:ring-green-500');
                    return;
                }

                matchIndicator.classList.remove('hidden');

                if (password === confirm) {
                    // Match - Green
                    confirmInput.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                    confirmInput.classList.add('border-green-500', 'focus:border-green-500', 'focus:ring-green-500');
                    matchText.textContent = '✓ Password cocok';
                    matchText.className = 'text-green-600 dark:text-green-400 font-medium';
                } else {
                    // Not match - Red
                    confirmInput.classList.remove('border-green-500', 'focus:border-green-500', 'focus:ring-green-500');
                    confirmInput.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                    matchText.textContent = '✗ Password tidak cocok';
                    matchText.className = 'text-red-600 dark:text-red-400 font-medium';
                }
            }

            if (passwordInput && confirmInput) {
                passwordInput.addEventListener('input', checkPasswordMatch);
                confirmInput.addEventListener('input', checkPasswordMatch);
            }
        })();
    </script>
</x-app-layout>
