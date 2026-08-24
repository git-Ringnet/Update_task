<?php

return [
    'subject' => env('VAPID_SUBJECT', 'mailto:admin@xuongrong.vn'),
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    // PHP on Windows/XAMPP does not always know the Windows certificate store.
    // This bundle keeps outbound push requests securely verified.
    'ca_cert_path' => env('WEBPUSH_CA_CERT_PATH', storage_path('certs/cacert.pem')),
];
