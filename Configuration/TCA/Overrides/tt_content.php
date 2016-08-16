<?php
// if there is already a itemsProcFunc in the tt_content colPos tca, save it to another key for later usage
if (!empty($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'])) {
   $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['m_itemsProcFunc'] = $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'];
}
// if there is already a itemsProcFunc in the tt_content CType tca, save it to another key for later usage
if (!empty($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemsProcFunc'])) {
   $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['m_itemsProcFunc'] = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemsProcFunc'];
}
// and set mask itemsProcFuncs
$GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'MASK\Mask\ItemsProcFuncs\ColPosList->itemsProcFunc';
$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemsProcFunc'] = 'MASK\Mask\ItemsProcFuncs\CTypeList->itemsProcFunc';