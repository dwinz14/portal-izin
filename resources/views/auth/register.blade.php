<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- NIK -->
        <div>
            <x-input-label for="nik" :value="__('NIK')" />
            <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik" :value="old('nik')" required
                autofocus autocomplete="nik" placeholder="AP123456789" />
            <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            <div id="nik-validation" class="mt-1 text-sm" style="display: none;"></div>
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Gender -->
        <div class="mt-4">
            <x-input-label for="gender" :value="__('Gender')" />
            <select id="gender" name="gender"
                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                required>
                <option value="">Pilih Gender</option>
                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Role')" />
            <select id="role" name="role"
                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                required>
                <option value="">Pilih Role</option>
                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="kasie" {{ old('role') == 'kasie' ? 'selected' : '' }}>Kasie</option>
                <option value="kabag-pincab" {{ old('role') == 'kabag-pincab' ? 'selected' : '' }}>kabag-pincab</option>
                <option value="hrd" {{ old('role') == 'hrd' ? 'selected' : '' }}>HRD</option>
                <option value="direksi" {{ old('role') == 'direksi' ? 'selected' : '' }}>Direksi</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Division -->
        <div class="mt-4">
            <x-input-label for="division_id" :value="__('Division')" />
            <select id="division_id" name="division_id"
                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">Pilih Divisi (Opsional)</option>
                @foreach (\App\Models\Division::all() as $division)
                    <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                        {{ $division->nama_divisi }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('division_id')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <div id="password-validation" class="mt-1 text-sm" style="display: none;">
                <div id="password-rule-1">Karakter pertama harus huruf besar</div>
                <div id="password-rule-2">Minimal 8 karakter</div>
                <div id="password-rule-3">Harus ada karakter angka</div>
                <div id="password-rule-4">Harus ada karakter simbol</div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.getElementById('nik').addEventListener('input', function() {
            const nik = this.value;
            const validationDiv = document.getElementById('nik-validation');
            if (nik.trim() === '') {
                validationDiv.style.display = 'none';
                return;
            }
            validationDiv.style.display = 'block';
            if (/^AP\d{9}$/.test(nik)) {
                validationDiv.textContent = 'NIK valid';
                validationDiv.className = 'mt-1 text-sm text-green-600';
            } else {
                validationDiv.textContent = 'NIK harus dimulai dengan "AP" diikuti 9 angka';
                validationDiv.className = 'mt-1 text-sm text-red-600';
            }
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
                    regex: /^[A-Z]/,
                    message: 'Karakter pertama harus huruf besar'
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
    </script>
</x-guest-layout>
