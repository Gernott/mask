<?php

declare(strict_types=1);

return [
    MASK\Mask\Domain\Model\BackendLayout::class => [
        'tableName' => 'backend_layout',
        'properties' => [
            'uid' => [
                'fieldname' => 'uid'
            ],
            'title' => [
                'fieldname' => 'title'
            ]
        ]
    ]
];
