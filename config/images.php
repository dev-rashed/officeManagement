<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image optimisation
    |--------------------------------------------------------------------------
    |
    | Every uploaded image is downscaled and re-encoded as WebP, which is
    | typically 25-35% smaller than JPEG at the same visual quality and is
    | supported by every browser in current use.
    |
    | Set `convert_to_webp` to false to keep the original format while still
    | getting the resize and strip.
    |
    */

    'convert_to_webp' => env('IMAGE_CONVERT_WEBP', true),

    /*
    | Files that are never touched.
    |
    | SVG is already small and vector -- rasterising it would make it worse,
    | and parsing untrusted SVG is a security problem in its own right. PDFs
    | and documents are stored exactly as uploaded.
    */
    'passthrough_mimes' => [
        'image/svg+xml',
        'application/pdf',
    ],

    /*
    | Presets. Dimensions are a maximum bounding box -- aspect ratio is always
    | kept and an image smaller than the box is never scaled up.
    */
    'presets' => [

        'default' => ['max_width' => 1600, 'max_height' => 1600, 'quality' => 82],

        // Faces. Square-ish crop-free bound, decent quality.
        'photo' => ['max_width' => 1000, 'max_height' => 1000, 'quality' => 82],

        // Hero and card images on the public site.
        'featured' => ['max_width' => 1600, 'max_height' => 1200, 'quality' => 82],

        // Open Graph wants 1200x630; capping the width is enough.
        'social' => ['max_width' => 1200, 'max_height' => 1200, 'quality' => 85],

        // Logos and signatures: small, but quality matters and alpha must
        // survive, so these stay lossless.
        'logo' => ['max_width' => 600, 'max_height' => 600, 'quality' => 92, 'lossless' => true],

        // Scans of invoices and receipts -- legibility over file size.
        'document' => ['max_width' => 2000, 'max_height' => 2000, 'quality' => 88],
    ],

];
