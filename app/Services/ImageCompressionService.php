<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;

/**
 * Kompresi gambar server-side
 */
class ImageCompressionService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Kompres & simpan. Mengembalikan path relatif (sama seperti ->store()).
     *
     * @throws RuntimeException berisi pesan yang aman ditampilkan ke user
     */
    public function compress(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $realPath = $file->getRealPath();

        $this->assertSafeImage($realPath);

        $maxDimension = (int) config('image_compression.max_dimension', 1600);
        $targetKb     = (int) config('image_compression.target_kb', 150);
        $quality      = (int) config('image_compression.quality_start', 80);
        $qualityStep  = (int) config('image_compression.quality_step', 10);
        $qualityFloor = (int) config('image_compression.quality_floor', 30);

        try {
            $image = $this->manager->read($realPath);

            $image->orient();                                   // koreksi rotasi EXIF
            $image->scaleDown(width: $maxDimension, height: $maxDimension);
            $image->blendTransparency('ffffff');                // PNG/GIF transparan -> latar putih

            do {
                $binary = (string) $image->toJpeg(quality: $quality);

                if (strlen($binary) / 1024 <= $targetKb || $quality <= $qualityFloor) {
                    break;
                }

                $quality -= $qualityStep;
            } while ($quality > 0);
        } catch (\Throwable $e) {
            Log::error('[ImageCompressionService] Gagal memproses gambar: ' . $e->getMessage());

            throw new RuntimeException('Gagal memproses gambar. Pastikan file adalah gambar yang valid.');
        }

        // Re-encode ke JPEG otomatis membuang EXIF (termasuk GPS) -> lebih aman untuk privasi.
        $filename = trim($directory, '/') . '/' . now()->format('Y/m') . '/' . Str::uuid() . '.jpg';

        Storage::disk($disk)->put($filename, $binary);

        return $filename;
    }

    public function delete(?string $path, string $disk = 'public'): void
    {
        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Cek header gambar SEBELUM didecode penuh (decode gambar raksasa bisa menghabiskan memori).
     */
    protected function assertSafeImage(string $path): void
    {
        $info = @getimagesize($path);

        if ($info === false) {
            throw new RuntimeException('File yang diupload bukan gambar yang valid.');
        }

        [$width, $height] = $info;

        if ($width * $height > (int) config('image_compression.max_pixels', 25_000_000)) {
            throw new RuntimeException('Resolusi gambar terlalu besar. Gunakan gambar yang lebih kecil.');
        }
    }
}
