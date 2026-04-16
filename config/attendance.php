<?php

return [
    'session_start'        => env('SESSION_START', '08:00:00'),
    'late_after_minutes'   => env('LATE_AFTER_MINUTES', 10),
    'absent_after_minutes' => env('ABSENT_AFTER_MINUTES', 30),
];
