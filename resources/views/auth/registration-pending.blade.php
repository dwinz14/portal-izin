<x-guest-layout>
    <div class="max-w-md mx-auto mt-12 p-6 bg-white dark:bg-gray-800 shadow-md rounded-lg text-center">
        <div class="flex flex-col items-center">
            <!-- Ikon status -->
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1zm0 0c0 1.5-2 2-2 4h4c0-2-2-2.5-2-4zM12 19h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Judul -->
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2">
                {{ __('Pendaftaran Berhasil') }}
            </h2>

            <!-- Pesan informasi -->
            <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ __('Terima kasih telah mendaftar. Akun Anda saat ini sedang menunggu proses verifikasi oleh admin.') }}
            </p>

            <!-- Info tambahan -->
            <p class="text-sm text-red dark:text-gray-500 mt-3">
                {{ __('Jika membutuhkan konfirmasi lebih cepat, silakan hubungi admin secara langsung.') }}
            </p>

            <!-- Tombol kembali -->
            <div class="mt-6">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 underline">
                    ← {{ __('Kembali ke Halaman Login') }}
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
