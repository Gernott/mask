<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace MASK\Mask\Tests\Unit\Migrations;

use MASK\Mask\Loader\JsonLoader;
use MASK\Mask\Migrations\MigrationManager;
use MASK\Mask\Migrations\OrphanRemoverMigration;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class MigrationTest extends UnitTestCase
{
    use PackageManagerTrait;

    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function orphanTablesRemoved(): void
    {
        $this->registerPackageManager();

        $jsonLoader = new JsonLoader(
            [
                'json' => 'EXT:mask/Tests/Unit/Fixtures/Configuration/orphanTables.json',
            ],
            new MigrationManager(
                [
                    new OrphanRemoverMigration(),
                ]
            )
        );

        $tableDefinitionCollection = $jsonLoader->load();
        self::assertFalse($tableDefinitionCollection->hasTable('tx_mask_im_an_orphan'));
        self::assertTrue($tableDefinitionCollection->hasTable('tx_mask_im_just_empty'));
    }
}
