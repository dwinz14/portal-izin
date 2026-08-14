<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Tambah User Baru') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Tambahkan user baru ke dalam sistem.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-4x2">
            <div
                class="bg-white dark:bg-slate-800 shadow-xl hover:shadow-xl/30 hover:-translate-y-1 transition-all duration-300 rounded-xl p-4 md:p-6">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="nik" value="NIK" />
                                <x-text-input id="nik" name="nik" type="text" value="{{ old('nik') }}"
                                    placeholder="Masukkan NIK..." required />
                                @error('nik')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-input-label for="name" value="Nama" />
                                <x-text-input id="name" name="name" type="text" value="{{ old('name') }}"
                                    placeholder="Masukkan nama user..." required />
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" name="email" type="email" value="{{ old('email') }}"
                                    placeholder="Masukkan email user..." required />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="password" value="Password" />
                                <x-text-input id="password" name="password" type="password" class="block"
                                    value="{{ config('app.default_user_password') }}" placeholder="Masukkan password..."
                                    required readonly />
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-input-label for="gender" value="Jenis Kelamin" />
                                <select id="gender" name="gender"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki
                                    </option>
                                    <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan
                                    </option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="role" value="Role" />
                                <select id="role" name="role"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2"
                                    required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach (['super_admin', 'hrd', 'kabag-pincab', 'kasie', 'staff', 'direksi'] as $roleOption)
                                        <option value="{{ $roleOption }}"
                                            {{ old('role') == $roleOption ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $roleOption)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="division_id" value="Divisi" />
                                <select id="division_id" name="division_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}"
                                            {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ strtoupper($division->nama_divisi) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('division_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="position_id" value="Jabatan" />
                                <select id="position_id" name="position_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2">
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}"
                                            {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                            {{ strtoupper($position->nama_jabatan) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="office_id" value="Kantor" />
                                <select id="office_id" name="office_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2">
                                    <option value="">-- Pilih Kantor --</option>
                                    @foreach ($offices as $office)
                                        <option value="{{ $office->id }}"
                                            {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                            {{ strtoupper($office->nama_kantor) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="tanggal_aktif_kerja" value="Tanggal Aktif Kerja" />
                                <x-text-input id="tanggal_aktif_kerja" name="tanggal_aktif_kerja" type="date"
                                    value="" placeholder="Pilih tanggal aktif kerja..." />
                                @error('tanggal_aktif_kerja')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-6 flex justify-end space-x-3">
                        <a href="{{ route('admin.users.index') }}"
                            class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-600 border border-gray-300 dark:border-gray-500 rounded-full font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:ring-offset-slate-800 disabled:opacity-25 transition ease-in-out duration-150">
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition ease-in-out duration-150">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
