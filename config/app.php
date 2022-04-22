<?php

return [
    'providers' => [
        Maatwebsite\Excel\ExcelServiceProvider::class,
    ],
    'aliases' => [
        'excel' => Maatwebsite\Excel\ExcelServiceProvider::class
    ],
    'timezone' => 'America/Mexico_City',
    'debug' => true,
];