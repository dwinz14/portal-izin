<?php

namespace App\Http\Requests\Concerns;

trait ValidatesProofImage
{
    protected function proofImageRules(bool $required): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'image',
            'mimes:' . implode(',', config('image_compression.allowed_extensions')),
            'max:' . config('image_compression.max_upload_kb'),
        ];
    }

    protected function proofImageMessages(string $requiredMessage): array
    {
        $maxMb = round(config('image_compression.max_upload_kb') / 1024, 1);

        return [
            'proof_image.required' => $requiredMessage,
            'proof_image.image'    => 'File bukti harus berupa gambar.',
            'proof_image.mimes'    => 'Format bukti harus JPG, PNG, atau GIF.',
            'proof_image.max'      => "Ukuran gambar maksimal {$maxMb} MB.",
            'proof_image.uploaded' => "Gagal mengunggah gambar. Pastikan ukurannya tidak lebih dari {$maxMb} MB.",
        ];
    }
}
