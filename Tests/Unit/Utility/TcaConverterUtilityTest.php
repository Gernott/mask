<?php

namespace MASK\Mask\Test\Utility;

use MASK\Mask\Utility\TcaConverterUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class TcaConverterUtilityTest extends BaseTestCase
{

    public function convertTcaArrayToFlatTestDataProvider()
    {
        return [
            'Simple array converted to flat' => [
                [
                    'type' => 'input',
                    'max' => '1'
                ],
                [
                    'config.type' => 'input',
                    'config.max' => '1'
                ]
            ],
            'Nested array converted to flat' => [
                [
                    'type' => 'input',
                    'nested' => [
                        'option' => '1'
                    ]
                ],
                [
                    'config.type' => 'input',
                    'config.nested.option' => '1'
                ]
            ],
            'Items converted to comma separated list with newline' => [
                [
                    'items' => [
                        ['label', 'item'],
                        ['label2', 'item2'],
                    ]
                ],
                [
                    'config.items' => "label,item\nlabel2,item2"
                ]
            ],
            'Eval values converted as seperate entries' => [
                [
                    'eval' => 'required,int'
                ],
                [
                    'config.eval.required' => 1,
                    'config.eval.int' => 1,
                ]
            ],
            'Empty eval values are ignored' => [
                [
                    'eval' => ''
                ],
                []
            ],
            'Date types in eval moved to config.eval instead' => [
                [
                    'eval' => 'date'
                ],
                [
                    'config.eval' => 'date'
                ]
            ],
            'blindLinkOptions values converted as seperate entries' => [
                [
                    'fieldControl' => [
                        'linkPopup' => [
                            'options' => [
                                'blindLinkOptions' => 'file,folder'
                            ]
                        ]
                    ]
                ],
                [
                    'config.fieldControl.linkPopup.options.blindLinkOptions.file' => 1,
                    'config.fieldControl.linkPopup.options.blindLinkOptions.folder' => 1,
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider convertTcaArrayToFlatTestDataProvider
     */
    public function convertTcaArrayToFlatTest($array, $expected)
    {
        self::assertSame($expected, TcaConverterUtility::convertTcaArrayToFlat($array));
    }

    public function convertFlatTcaToArrayTestDataProvider()
    {
        return [
            'Simple flat array is converted to original TCA array' => [
                [
                    'config.type' => 'input'
                ],
                [
                    'config' => [
                        'type' => 'input'
                    ]
                ]
            ],
            'Nested flat array is converted to original TCA array' => [
                [
                    'config.nested.option' => 'value'
                ],
                [
                    'config' => [
                        'nested' => [
                            'option' => 'value'
                        ]
                    ]
                ]
            ],
            'Items converted to items array' => [
                [
                    'config.items' => "label,item\nlabel2,item2"
                ],
                [
                    'config' => [
                        'items' => [
                            ['label', 'item'],
                            ['label2', 'item2'],
                        ]
                    ]
                ]
            ],
            'Eval values converted to comma separated list' => [
                [
                    'config.eval.required' => 1,
                    'config.eval.int' => 1,
                ],
                [
                    'config' => [
                        'eval' => 'required,int'
                    ]
                ]
            ],
            'Date eval value moves back to eval list' => [
                [
                    'config.eval.required' => 1,
                    'config.eval' => 'date',
                ],
                [
                    'config' => [
                        'eval' => 'required,date'
                    ]
                ]
            ],
            'blindLinkOptions values converted to comma separated list' => [
                [
                    'config.fieldControl.linkPopup.options.blindLinkOptions.file' => 1,
                    'config.fieldControl.linkPopup.options.blindLinkOptions.folder' => 1,
                ],
                [
                    'config' => [
                        'fieldControl' => [
                            'linkPopup' => [
                                'options' => [
                                    'blindLinkOptions' => 'file,folder'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider convertFlatTcaToArrayTestDataProvider
     * @param $array
     * @param $expected
     */
    public function convertFlatTcaToArrayTest($array, $expected)
    {
        self::assertSame($expected, TcaConverterUtility::convertFlatTcaToArray($array));
    }
}
