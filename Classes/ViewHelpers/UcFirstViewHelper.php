<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class UcFirstViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('string', 'string', 'The string to upper case.', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return ucfirst($arguments['string']);
    }

    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        return 'ucfirst(' . $argumentsName . '[\'string\'])';
    }
}
