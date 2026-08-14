<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Backup & Restore Database') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Halaman untuk ekspor (backup) atau impor (restore) database.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="backupManager()" x-init="init">
        <!-- Notifikasi -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                <p class="font-medium">✅ {{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm" role="alert">
                <p class="font-medium">❌ {{ session('error') }}</p>
            </div>
        @endif

        <!-- Card Export -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Ekspor Database (Backup)
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Download seluruh database sebagai file SQL atau
                    ZIP.</p>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('backup.export') }}" class="space-y-4">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="compress" value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Kompres ke ZIP</span>
                        </label>
                        <button type="submit" x-ref="exportBtn"
                            @click="exporting = true; setTimeout(() => exporting = false, 3000)"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            <svg x-show="!exporting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <svg x-show="exporting" class="animate-spin w-4 h-4 mr-2" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="exporting ? 'Memproses...' : 'Ekspor Sekarang'"></span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">* Proses ekspor bisa memakan waktu beberapa
                        detik tergantung ukuran database.</p>
                </form>
            </div>
        </div>

        <!-- Card Import -->
        {{-- <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3 3m0 0l-3-3m3 3V3">
                        </path>
                    </svg>
                    Impor Database (Restore)
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload file SQL atau ZIP untuk mengembalikan
                    database. <span class="text-red-500 font-medium">⚠️ Peringatan: Proses ini akan menimpa data yang
                        ada.</span></p>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('backup.import') }}" enctype="multipart/form-data"
                    x-ref="importForm" @submit.prevent="handleImport">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                File SQL atau ZIP
                            </label>
                            <div class="flex items-center justify-center w-full">
                                <label
                                    class="flex flex-col w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10">
                                            </path>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-semibold">Klik untuk upload</span> atau drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">SQL atau ZIP (Maks. 50MB)
                                        </p>
                                        <p x-show="fileName" class="mt-2 text-xs text-blue-600 font-medium"
                                            x-text="fileName"></p>
                                    </div>
                                    <input type="file" name="sql_file" class="hidden" accept=".sql,.zip"
                                        @change="handleFileSelect($event)">
                                </label>
                            </div>
                            @error('sql_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" id="confirm_import" x-model="confirmChecked"
                                    class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500">
                                <label for="confirm_import" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Saya mengerti bahwa data yang ada akan ditimpa
                                </label>
                            </div>
                            <button type="submit" :disabled="!confirmChecked || uploading"
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                <span x-text="uploading ? 'Mengimpor...' : 'Impor Database'"></span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Progress Bar untuk Import -->
                <div x-show="uploading" x-transition class="mt-4">
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                        <span>Mengimpor data...</span>
                        <span x-text="uploadProgress + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                            :style="'width: ' + uploadProgress + '%'"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Jangan tutup halaman ini hingga proses selesai.</p>
                </div>
            </div>
        </div> --}}

        <!-- Daftar Backup Tersimpan (Opsional) -->
        {{-- @if (count($backupFiles) > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                            </path>
                        </svg>
                        Backup Tersimpan
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">File backup yang pernah diekspor (disimpan di
                        server).</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama File
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ukuran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terakhir
                                    Diubah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($backupFiles as $file)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $file['name'] }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $file['size'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $file['modified'] }}</td>
                                    <td class="px-6 py-3 text-sm">
                                        <a href="{{ route('backup.download', ['file' => basename($file['path'])]) }}"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Download</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif --}}
    </div>

    <script>
        function backupManager() {
            return {
                exporting: false,
                uploading: false,
                uploadProgress: 0,
                confirmChecked: false,
                fileName: '',
                init() {
                    // Inisialisasi jika diperlukan
                },
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                    } else {
                        this.fileName = '';
                    }
                },
                handleImport() {
                    if (!this.confirmChecked) {
                        alert('Harap konfirmasi bahwa Anda mengerti risiko menimpa data.');
                        return;
                    }
                    const form = this.$refs.importForm;
                    const formData = new FormData(form);
                    this.uploading = true;
                    this.uploadProgress = 0;

                    // Simulasi progress (opsional)
                    const interval = setInterval(() => {
                        if (this.uploadProgress < 90) {
                            this.uploadProgress += 10;
                        }
                    }, 500);

                    fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            clearInterval(interval);
                            this.uploadProgress = 100;
                            if (data.success) {
                                alert(data.message);
                                window.location.href = '/login'; // redirect ke halaman login
                            } else {
                                alert('Error: ' + data.message);
                                this.uploading = false;
                            }
                        })
                        .catch(error => {
                            clearInterval(interval);
                            this.uploading = false;
                            alert('Terjadi kesalahan: ' + error.message);
                        });
                }
            }
        }
    </script>
</x-app-layout>
