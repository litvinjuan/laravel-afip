<?php

return [
    'certificate' => env('AFIP_CERTIFICATE', ''),

    'key' => env('AFIP_KEY', ''),

    'key-passphrase' => env('AFIP_KEY_PASSPHRASE'),

    'production' => (bool) env('AFIP_PRODUCTION', false),
];
