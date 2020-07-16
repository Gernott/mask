<?php

declare(strict_types=1);

return [
    MASK\Mask\Domain\Model\Content::class => [
        'tableName' => 'tt_content',
        'properties' => [
            'uid' => [
                'fieldName' => 'uid'
            ],
            'pid' => [
                'fieldName' => 'pid'
            ],
            'sorting' => [
                'fieldName' => 'sorting'
            ],
            'contentType' => [
                'fieldName' => 'CType'
            ],
            'header' => [
                'fieldName' => 'header'
            ]
        ],
    ],

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
