<?php

/**
 * Parameter kompresi gambar otomatis (server-side) untuk semua upload foto
 */
return [

    // ── Batas upload (dipakai backend DAN dikirim ke frontend, satu sumber angka) ──
    'max_upload_kb'      => 1024,                      // 1 MB, file asli yang dipilih user
    'allowed_extensions' => ['jpeg', 'jpg', 'png', 'gif'],
    'max_pixels'         => 25_000_000,                // guard memori GD (25 MP)

    // ── Parameter kompresi ──
    'max_dimension' => 1600,   // sisi terpanjang (px), tidak pernah diperbesar
    'target_kb'     => 150,    // target ukuran hasil
    'quality_start' => 80,
    'quality_step'  => 10,
    'quality_floor' => 30,

];
