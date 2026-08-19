<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class HolidayGuideSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN TEMPLATE HARI LIBUR'],
            [''],
            ['1. Data bisa diambil dari Surat Keputusan Bersama (SKB) 3 Menteri tentang Hari Libur Nasional dan Cuti Bersama.'],
            ['2. Isi satu baris untuk setiap hari libur, dimulai dari baris ke-3 di sheet "Template".'],
            ['3. Kolom Tanggal: format YYYY-MM-DD (contoh: 2026-03-20). Tanggal otomatis diubah ke format lain jika perlu.'],
            ['4. Kolom Nama Hari Libur: nama lengkap sesuai SKB (contoh: Cuti Bersama Hari Raya Idul Fitri 1447 H).'],
            ['5. Kolom Tipe: pilih dari dropdown - "nasional" (libur nasional, tidak potong kuota) atau "cuti_bersama" (cuti bersama, potong kuota tahunan).'],
            ['6. Dua baris contoh sudah terisi dan boleh dibiarkan (sistem akan memverifikasinya) atau dihapus.'],
            ['7. Simpan file, lalu unggah melalui menu Import Massal > Excel. Sistem menampilkan verifikasi sebelum data disimpan.'],
            [''],
            ['Catatan:'],
            ['- Sistem HANYA membaca sheet "Template". Sheet ini (Petunjuk) tidak ikut dibaca.'],
            ['- Jika kolom Tipe dikosongkan, sistem otomatis mendeteksi: nama yang mengandung kata "Cuti Bersama" dianggap cuti_bersama, sisanya libur nasional.'],
            ['- File tanpa baris judul/header juga tetap terbaca - cukup urutkan kolom Tanggal | Nama | Tipe dari kiri.'],
        ];
    }
}
