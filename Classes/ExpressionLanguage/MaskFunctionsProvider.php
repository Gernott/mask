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

namespace MASK\Mask\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class MaskFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            $this->maskBeLayout(),
        ];
    }

    /**
     * @return ExpressionFunction
     */
    protected function maskBeLayout(): ExpressionFunction
    {
        return new ExpressionFunction(
            'maskBeLayout',
            static function ($param) {},
            static function ($arguments, $param = null) {
                $layout = (string)$param;
                $backend_layout = (string)($arguments['page']['backend_layout'] ?? '');
                $layoutWithPrefix = 'pagets__' . $layout;

                // If backend_layout is set on current page
                if ($backend_layout !== '') {
                    return in_array($backend_layout, [$layout, $layoutWithPrefix], true);
                }

                // If backend_layout is not set on current page, check backend_layout_next_level on rootline
                $rootline = $arguments['tree']->rootLine;
                // Sort rootline by (indexed) key, because the order depends on the context.
                // For example calling the condition matcher in the FormEngineDataProvider
                // returns the rootline in reversed order.
                ksort($rootline);
                $rootline = array_reverse($rootline);
                $rootline = array_splice($rootline, 1, -1);
                foreach ($rootline as $page) {
                    $backend_layout_next_level = (string)$page['backend_layout_next_level'];
                    if ($backend_layout_next_level !== '') {
                        return in_array($backend_layout_next_level, [$layout, $layoutWithPrefix], true);
                    }
                }

                return false;
            }
        );
    }
}
