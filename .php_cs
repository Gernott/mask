<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$headerComment = <<<DOC
This file is part of the TYPO3 CMS project.

It is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License, either version 2
of the License, or any later version.

For the full copyright and license information, please read the
LICENSE.txt file that was distributed with this source code.

The TYPO3 project - inspiring people to share!
DOC;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'ordered_imports' => true,
    ])
    ->setFinder($finder)
;
