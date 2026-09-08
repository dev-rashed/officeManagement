<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The single place uploaded files are stored.
 *
 * Images are downscaled, stripped of metadata and re-encoded as WebP. Anything
 * that is not a raster image -- a PDF invoice, an SVG logo -- is stored exactly
 * as it arrived.
 *
 * Uses GD, which this server has with WebP support. No Imagick, no external
 * binary, nothing to install.
 */
class ImageService
{
    /**
     * Store an upload, optimising it if it is an image.
     *
     * @return string The path relative to the public disk.
     */
    public function store(UploadedFile $file, string $directory, string $preset = 'default', string $disk = 'public'): string
    {
        $directory = trim($directory, '/');

        if (! $this->shouldOptimise($file)) {
            return $file->store($directory, $disk);
        }

        try {
            $encoded = $this->optimise($file, $this->preset($preset));
        } catch (\Throwable $e) {
            // A conversion failure must never lose the user's upload.
            Log::warning('Image optimisation failed; storing the original', [
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'error' => $e->getMessage(),
            ]);

            return $file->store($directory, $disk);
        }

        $path = $directory.'/'.Str::random(40).'.webp';
        Storage::disk($disk)->put($path, $encoded);

        return $path;
    }

    /**
     * Store a replacement and delete what it replaces.
     */
    public function replace(?string $oldPath, UploadedFile $file, string $directory, string $preset = 'default', string $disk = 'public'): string
    {
        $new = $this->store($file, $directory, $preset, $disk);

        $this->delete($oldPath, $disk);

        return $new;
    }

    public function delete(?string $path, string $disk = 'public'): void
    {
        if (blank($path)) {
            return;
        }

        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            Log::warning('Could not delete file', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }

    public function isImage(?UploadedFile $file): bool
    {
        return $file && str_starts_with((string) $file->getMimeType(), 'image/');
    }

    private function shouldOptimise(UploadedFile $file): bool
    {
        if (! $this->isImage($file)) {
            return false;
        }

        if (in_array($file->getMimeType(), config('images.passthrough_mimes', []), true)) {
            return false;
        }

        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    private function preset(string $name): array
    {
        return array_merge(
            config('images.presets.default'),
            config("images.presets.{$name}", []),
        );
    }

    /**
     * Decode, orient, downscale and re-encode.
     */
    private function optimise(UploadedFile $file, array $preset): string
    {
        $contents = file_get_contents($file->getRealPath());
        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            throw new \RuntimeException('GD could not read the image.');
        }

        try {
            $image = $this->applyExifOrientation($image, $file);
            $image = $this->downscale($image, $preset['max_width'], $preset['max_height']);

            // WebP carries alpha, so transparency survives a PNG conversion --
            // but only if the flags are set before encoding.
            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();

            if (! config('images.convert_to_webp', true)) {
                imagejpeg($image, null, (int) $preset['quality']);
            } elseif (! empty($preset['lossless'])) {
                imagewebp($image, null, IMG_WEBP_LOSSLESS);
            } else {
                imagewebp($image, null, (int) $preset['quality']);
            }

            $encoded = ob_get_clean();

            if ($encoded === false || $encoded === '') {
                throw new \RuntimeException('Encoding produced no output.');
            }

            return $encoded;
        } finally {
            if ($image instanceof \GdImage) {
                imagedestroy($image);
            }
        }
    }

    /**
     * Phone cameras record rotation in EXIF rather than rotating the pixels.
     * Without this, portrait photos appear on their side.
     */
    private function applyExifOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || $file->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = $exif['Orientation'] ?? null;

        if (! $orientation || $orientation === 1) {
            return $image;
        }

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    /**
     * Fit inside the box, keeping the aspect ratio. Never enlarges.
     */
    private function downscale(\GdImage $image, int $maxWidth, int $maxHeight): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency through the resample.
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
