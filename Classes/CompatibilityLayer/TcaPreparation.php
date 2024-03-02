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

namespace MASK\Mask\CompatibilityLayer;

if (class_exists(\TYPO3\CMS\Core\Preparations\TcaPreparation::class)) {
    class TcaPreparation extends \TYPO3\CMS\Core\Preparations\TcaPreparation {}
} elseif (class_exists(\TYPO3\CMS\Core\Configuration\Tca\TcaPreparation::class)) {
    class TcaPreparation extends \TYPO3\CMS\Core\Configuration\Tca\TcaPreparation {}
} else {
    throw new \Exception('Could not find TcaPreparation class.', 1707468989);
}
