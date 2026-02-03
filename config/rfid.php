<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RFID Reader Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your RFID readers and their connection settings here.
    |
    */

    // Default RFID data parsing settings
    'rfid_start' => env('RFID_START', 4),
    'rfid_length' => env('RFID_LENGTH', 24),

    // Duplicate scan threshold (in seconds)
    // Scans within this window at the same checkpoint are considered duplicates
    'duplicate_threshold' => env('RFID_DUPLICATE_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | Device Mapping
    |--------------------------------------------------------------------------
    |
    | Map reader IP addresses to checkpoint IDs.
    | This is used when the Python scanner requests configuration.
    |
    | Format: 'IP_ADDRESS' => ['checkpoint_id' => X, 'name' => 'Description']
    |
    */

    'device_mapping' => [
        // Example mappings - configure these for your setup
        '192.168.1.100' => [
            'checkpoint_id' => 1,
            'name' => 'Start Line Reader',
        ],
        '192.168.1.101' => [
            'checkpoint_id' => 2,
            'name' => 'Checkpoint 1 Reader',
        ],
        '192.168.1.102' => [
            'checkpoint_id' => 3,
            'name' => 'Finish Line Reader',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timing Settings
    |--------------------------------------------------------------------------
    */

    // Auto-recalculate positions when a finish time is recorded
    'auto_recalculate_positions' => true,

    // Store invalid scans in raw log for debugging
    'store_invalid_scans' => true,

    // Send notifications on specific events
    'notifications' => [
        'on_finish' => env('RFID_NOTIFY_ON_FINISH', false),
        'on_invalid_scan' => env('RFID_NOTIFY_ON_INVALID', false),
    ],
];
