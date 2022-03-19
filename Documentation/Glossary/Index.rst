.. include:: ../Includes.txt

.. _glossary:

========
Glossary
========

Here you can find explanations for common terms when working with Mask.

.. _fluid-styled-content:

Fluid Styled Content
====================

:ref:`Fluid Styled Content<t3fsc:start>` is a core extension of TYPO3 which provides all the regular content elements
you can find in your installation like Header, Text and Text & Images. Mask does not depend on it directly, but it is
good practice to include it nevertheless. Most of the time you will want to have some generic content elements for your
site. Even if you don't need them, this extension includes the TypoScript snippet `lib.parseFunc_RTE` too, which is
essential for TYPO3 richtext parsing. Also you can use the standard layout of Fluid Styled Content by
:ref:`specifying the paths in TypoScript <fsc-guide>`.
