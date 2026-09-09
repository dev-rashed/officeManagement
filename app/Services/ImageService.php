<?php

namespace App\Services;

use App\Exceptions\UnreadableUploadException;
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
     *
     * @throws UnreadableUploadException when the temporary upload cannot be read
     */
    public function store(UploadedFile $file, string $directory, string $preset = 'default', string $disk = 'public'): string
    {
        $directory = trim($directory, '/');

        // Read once, up front. Everything below works on the bytes, so a failure
        // to reach the temp file surfaces here rather than halfway through.
        $contents = $this->read($file);
        $settings = $this->preset($preset);

        if ($this->shouldOptimise($file, $settings)) {
            try {
                $encoded = $this->optimise($contents, $file, $settings);

                $path = $directory.'/'.Str::random(40).'.webp';
                Storage::disk($disk)->put($path, $encoded);

                return $path;
            } catch (\Throwable $e) {
                // A conversion failure must never lose the user's upload; fall
                // through and store what arrived.
                Log::warning('Image optimisation failed; storing the original', [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $path = $directory.'/'.Str::random(40).'.'.$this->extension($file);
        Storage::disk($disk)->put($path, $contents);

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

    /**
     * Where the uploaded bytes actually are.
     *
     * getRealPath() resolves through realpath(), which returns false on some
     * Windows temp-directory setups even though the file is perfectly readable.
     * getPathname() is the raw upload path and is never resolved, so it is the
     * reliable fallback. Callers must handle null.
     */
    private function readablePath(UploadedFile $file): ?string
    {
        foreach ([$file->getRealPath(), $file->getPathname()] as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @throws UnreadableUploadException
     */
    private function read(UploadedFile $file): string
    {
        $path = $this->readablePath($file);

        $contents = $path === null ? false : @file_get_contents($path);

        if ($contents === false || $contents === '') {
            Log::error('Uploaded file could not be read', [
                'name' => $file->getClientOriginalName(),
                'real_path' => $file->getRealPath(),
                'pathname' => $file->getPathname(),
                'error' => $file->getErrorMessage(),
            ]);

            throw new UnreadableUploadException(
                'The uploaded file could not be read from temporary storage.'
            );
        }

        return $contents;
    }

    /**
     * The extension to store an unoptimised file under.
     *
     * Derived from the MIME type where possible, falling back to the client's
     * extension, sanitised — it ends up in a filename.
     */
    private function extension(UploadedFile $file): string
    {
        $extension = $file->extension() ?: $file->getClientOriginalExtension();
        $extension = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $extension));

        return $extension !== '' ? $extension : 'bin';
    }

    private function shouldOptimise(UploadedFile $file, array $preset = []): bool
    {
        // Some uses need the original format kept — a favicon, for instance,
        // because browser support for a WebP icon is still patchy.
        if (! empty($preset['passthrough'])) {
            return false;
        }

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
    private function optimise(string $contents, UploadedFile $file, array $preset): string
    {
        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            throw new \RuntimeException('GD could not read the image.');
        }

        try {
            $image = $this->applyExifOrientation($image, $contents, $file);
            $image = $this->downscale($image, $preset['max_width'], $preset['max_height']);

            // WebP carries alpha, so transparency survives a PNG conversion --
            // but only if the flags are set before encoding.
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $encoded = $this->capture(function () use ($image, $preset) {
                if (! config('images.convert_to_webp', true)) {
                    imagejpeg($image, null, (int) $preset['quality']);
                } elseif (! empty($preset['lossless'])) {
                    imagewebp($image, null, IMG_WEBP_LOSSLESS);
                } else {
                    imagewebp($image, null, (int) $preset['quality']);
                }
            });

            if ($encoded === '') {
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
     * GD writes to stdout when handed a null filename, so the encoders are run
     * inside an output buffer. The finally is what matters: without it a throw
     * mid-encode would leave the buffer open and corrupt the response.
     */
    private function capture(callable $encode): string
    {
        ob_start();

        try {
            $encode();

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Phone cameras record rotation in EXIF rather than rotating the pixels.
     * Without this, portrait photos appear on their side.
     */
    private function applyExifOrientation(\GdImage $image, string $contents, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || $file->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        // Read the EXIF out of the bytes rather than the path, for the same
        // reason read() does -- the temp path is not always resolvable.
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $contents);
        rewind($stream);

        try {
            $exif = @exif_read_data($stream);
        } catch (\Throwable) {
            $exif = false;
        } finally {
            fclose($stream);
        }

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
