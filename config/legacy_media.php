<?php

return [
    // Temporary browser-side fallback. PHP never fetches a remote image.
    'enabled' => env('LEGACY_MEDIA_ENABLED', false),
    'directories' => array_values(array_filter(explode(',', env('LEGACY_MEDIA_DIRECTORIES', 'catalog')))),
    'origin' => env('LEGACY_MEDIA_ORIGIN', 'https://www.herrera.hr'),
    // Originals copied here at the final media-transfer step win over live URLs.
    'local_root' => public_path('image'),
];
