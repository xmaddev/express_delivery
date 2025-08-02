<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

$schema['controllers']['express_delivery'] = [
    'modes' => [
        'store_locator_picker' => [
            'permissions' => true, 
        ],
    ],
];

return $schema;