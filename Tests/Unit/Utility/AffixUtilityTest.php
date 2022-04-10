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

namespace MASK\Mask\Test\Utility;

use MASK\Mask\Utility\AffixUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class AffixUtilityTest extends BaseTestCase
{
    /**
     * @test
     */
    public function maskPrefixSuffixTest(): void
    {
        self::assertSame('abc', AffixUtility::removeMaskPrefix('tx_mask_abc'));
        self::assertSame('tx_mask_abc', AffixUtility::addMaskPrefix('abc'));
        self::assertSame('tx_mask_abc', AffixUtility::addMaskPrefix('tx_mask_abc'));
        self::assertTrue(AffixUtility::hasMaskPrefix('tx_mask_abc'));
        self::assertFalse(AffixUtility::hasMaskPrefix('tx_maskabc'));

        self::assertSame('abc', AffixUtility::removeCTypePrefix('mask_abc'));
        self::assertSame('mask_abc', AffixUtility::addMaskCTypePrefix('abc'));
        self::assertSame('mask_abc', AffixUtility::addMaskCTypePrefix('mask_abc'));
        self::assertTrue(AffixUtility::hasMaskCTypePrefix('mask_abc'));
        self::assertFalse(AffixUtility::hasMaskCTypePrefix('maskabc'));

        self::assertSame('tx_mask_abc', AffixUtility::removeMaskParentSuffix('tx_mask_abc_parent'));
        self::assertSame('tx_mask_abc_parent', AffixUtility::addMaskParentSuffix('tx_mask_abc'));
        self::assertTrue(AffixUtility::hasMaskParentSuffix('tx_mask_abc_parent'));
        self::assertFalse(AffixUtility::hasMaskParentSuffix('tx_mask_abcparent'));
    }
}
