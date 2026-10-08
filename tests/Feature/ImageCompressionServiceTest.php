<?php

namespace Tests\Feature;

use App\Services\ImageCompressionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCompressionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_gambar_besar_diresize_dan_menjadi_jpeg_kecil(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg', 4000, 3000);

        $path = app(ImageCompressionService::class)->compress($file, 'proof_images');

        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsWith('.jpg', $path);

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertLessThanOrEqual(1600, max($w, $h));
        $this->assertLessThanOrEqual(
            config('image_compression.max_upload_kb') * 1024,
            Storage::disk('public')->size($path)
        );
    }

    public function test_png_transparan_menjadi_latar_putih(): void
    {
        $gd = imagecreatetruecolor(100, 100);
        imagealphablending($gd, false);
        imagesavealpha($gd, true);
        imagefill($gd, 0, 0, imagecolorallocatealpha($gd, 0, 0, 0, 127));
        $tmp = tempnam(sys_get_temp_dir(), 'png');
        imagepng($gd, $tmp);

        $file = new UploadedFile($tmp, 'transparan.png', 'image/png', null, true);
        $path = app(ImageCompressionService::class)->compress($file, 'proof_images');

        $img = imagecreatefromstring(Storage::disk('public')->get($path));
        $rgb = imagecolorsforindex($img, imagecolorat($img, 50, 50));

        $this->assertGreaterThan(240, $rgb['red']);
        $this->assertGreaterThan(240, $rgb['green']);
        $this->assertGreaterThan(240, $rgb['blue']);
    }

    public function test_file_bukan_gambar_ditolak(): void
    {
        $this->expectException(\RuntimeException::class);

        $file = UploadedFile::fake()->createWithContent('palsu.jpg', 'ini bukan gambar');
        app(ImageCompressionService::class)->compress($file, 'proof_images');
    }
}
