<?php

namespace App\Concerns;

use App\Services\ImageService;
use Illuminate\Http\Request;

/**
 * One way of handling an uploaded file across every controller: optimise and
 * convert on upload, replace the previous file, or honour a removal.
 *
 * The removal flag is the `remove_<field>` hidden input that <x-image-upload />
 * posts, so a field can be cleared without uploading a replacement.
 */
trait HandlesImageUploads
{
    /**
     * Work out the new value for an image column.
     *
     * @param  string  $field    The request key, e.g. 'featured_image'
     * @param  ?string $current  What is stored now
     * @return ?string           The value to save
     */
    protected function resolveUpload(
        Request $request,
        string $field,
        ?string $current,
        string $directory,
        string $preset = 'default',
    ): ?string {
        $images = app(ImageService::class);

        if ($request->hasFile($field)) {
            return $images->replace($current, $request->file($field), $directory, $preset);
        }

        if ($request->boolean('remove_'.$field)) {
            $images->delete($current);

            return null;
        }

        return $current;
    }

    /**
     * Apply resolveUpload() to a validated array in place.
     */
    protected function applyUpload(
        Request $request,
        array &$data,
        string $field,
        ?string $current,
        string $directory,
        string $preset = 'default',
        ?string $column = null,
    ): void {
        $column ??= $field;
        $data[$column] = $this->resolveUpload($request, $field, $current, $directory, $preset);
    }
}
