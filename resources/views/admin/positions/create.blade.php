<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Tambah Jabatan') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Tambahkan jabatan baru ke dalam sistem.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <form method="POST" action="{{ route('admin.positions.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="nama_jabatan" :value="__('Nama Jabatan')" />
                    <x-text-input id="nama_jabatan" name="nama_jabatan" type="text" class="mt-1 block w-full"
                        :value="old('nama_jabatan')" required autofocus autocomplete="nama_jabatan" />
                    <x-input-error :messages="$errors->get('nama_jabatan')" class="mt-2" />
                </div>

                <div>
                    <label for="work_schedule"
                        class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Pola Hari Kerja <span class="text-red-500">*</span>
                    </label>
                    <select id="work_schedule" name="work_schedule" required
                        class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition">
                        @foreach (\App\Enums\WorkSchedule::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('work_schedule', 'weekday') === $value)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Pilih "Setiap hari" untuk jabatan shift 7 hari.
                    </p>
                    <x-input-error :messages="$errors->get('work_schedule')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.positions.index') }}"
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
