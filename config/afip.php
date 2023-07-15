<?php

return [
    'certificates-disk' => 'afip',
    'certificates-directory' => '/certificates',

    'key-passphrase' => env('AFIP-KEY-PASSPHRASE'),

    'production' => (bool) env('AFIP-PRODUCTION', false),
];
