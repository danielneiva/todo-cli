<?php

$homeDir = getenv('HOME') ?: getenv('USERPROFILE') ?: '/tmp';
$todoDir = $homeDir . '/.todo';

return [

    'default' => 'sqlite',

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', $todoDir . '/database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ],

    'migrations' => 'migrations',
];