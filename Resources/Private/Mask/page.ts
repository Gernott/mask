mod.wizards.newContentElement.wizardItems.mask {
	header = LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf:new_content_element_tab
	elements.mask_###KEY### {
		###ICON###
		title = ###LABEL###
		description = ###DESCRIPTION###
		tt_content_defValues.CType = mask_###KEY###
	}
	show := addToList(mask_###KEY###);
}