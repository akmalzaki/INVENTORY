<?php

$table_columns_mapping = [
    'users' => [
        'id',
        'first_name',
        'last_name',
        'password',
        'email',
        'created_at'
    ],
    'assets' => [
        'id',
        'asset_name',
        'description',
        'img',
        'asset_type',
        'created_by',
        'created_at',
        'updated_at'
    ],
    'stocks' => [
        'id',
        'asset_id',
        'created_by',
        'quantity',
        'created_at',
        'updated_at'
    ],
];
?>