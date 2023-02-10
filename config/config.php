<?php
/**
 * @author 小日日
 * @time 2023/2/8
 */

return [
    'server' => [
        'host' => '0.0.0.0',
        'port' => 9501
    ],
    'watchFile' => [
        'dir' => ['src', 'config'],
        'ext' => ['.php'],
        'scan_interval' => 2000,
    ]
];