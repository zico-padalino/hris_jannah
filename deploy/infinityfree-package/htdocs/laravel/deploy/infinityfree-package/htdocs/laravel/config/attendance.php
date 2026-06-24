<?php

return [
    'face_match_threshold' => env('FACE_MATCH_THRESHOLD', 0.6),
    'location_buffer_meters' => env('LOCATION_BUFFER_METERS', 0),
    'fingerprint_auto_pull_seconds' => (int) env('FINGERPRINT_AUTO_PULL_SECONDS', 30),
    /*
    | tcp — tarik log via TCP port 4370 (default, untuk mesin tanpa ADMS push)
    | scheduled — alias tcp, tarik via php artisan schedule:work
    | hybrid — cadangan TCP saat mesin polling ADMS (mesin hybrid)
    | adms — hanya terima push ADMS (mesin cloud)
    */
    'fingerprint_log_mode' => env('FINGERPRINT_LOG_MODE', 'tcp'),
];
