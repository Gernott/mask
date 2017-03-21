tt_content.mask_###KEY### = USER
tt_content.mask_###KEY### {
	userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
	extensionName = Mask
	pluginName = ContentRenderer
	vendorName = MASK
	settings.file = ###PATH###
	switchableControllerActions.Frontend.1 = contentelement
	action = contentelement
	controller = Frontend
}
