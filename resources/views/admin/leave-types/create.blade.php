<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Tambah Jenis Cuti') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Tambahkan jenis cuti baru ke dalam sistem.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-2">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-gray-200 dark:border-slate-700 p-4">
            <form method="POST" action="{{ route('admin.leave-types.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Nama Jenis Cuti')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                        :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="quota" :value="__('Kuota Cuti (Hari)')" />
                    <x-text-input id="quota" name="quota" type="number" class="mt-1 block w-full"
                        :value="old('quota')" required min="0" placeholder="Masukkan 0 untuk tanpa batas" />
                    <x-input-error :messages="$errors->get('quota')" class="mt-2" />
                    <p class="text-sm text-gray-500 mt-1">Masukkan 0 jika jenis cuti ini tidak memiliki batas kuota</p>
                </div>

                <div>
                    <x-input-label for="min_years" :value="__('Minimal Masa Kerja (Tahun)')" />
                    <x-text-input id="min_years" name="min_years" type="number" class="mt-1 block w-full"
                        :value="old('min_years')" min="0" placeholder="Masukkan 0 untuk tanpa batasan" />
                    <x-input-error :messages="$errors->get('min_years')" class="mt-2" />
                    <p class="text-sm text-gray-500 mt-1">Minimal masa kerja dalam tahun untuk dapat mengajukan jenis
                        cuti ini</p>
                </div>

                <div>
                    <x-input-label for="gender" :value="__('Batasan Gender')" />
                    <select id="gender" name="gender"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Semua Gender</option>
                        <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                    <p class="text-sm text-gray-500 mt-1">Pilih gender jika jenis cuti ini khusus untuk laki-laki atau
                        perempuan saja</p>
                </div>

                {{-- <div class="flex items-center">
                    <input id="is_active" name="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" {{ old('is_active') ? 'checked' : '' }}>
                    <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                        {{ __('Aktifkan jenis cuti ini') }}
                    </label>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                </div> --}}

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.leave-types.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-400 dark:bg-slate-600 border border-gray-300 dark:border-gray-500 rounded-full font-semibold text-xs text-slate-50 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-rose-300 dark:hover:bg-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition ease-in-out duration-150">
                        Batal
                    </a>

                    <x-primary-button>
                        {{ __('Simpan') }}
                    </x-primary-button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
