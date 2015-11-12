jQuery.noConflict();
jQuery(document).ready(function () {
	// Transform Lowercase Inputs to Lowercase
	jQuery(document).on("change", "INPUT.lowercase", function (event) {
		jQuery(this).val(jQuery(this).val().toLowerCase());
	});

	// Add title of field to 2nd column
	jQuery(".tx_mask_tabcell3").on("keyup", ".tx_mask_field INPUT[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']", function (event) {
		syncBodyToHead(jQuery(this).closest(".tx_mask_field"));
	});

	// Add key of field to 2nd column
	jQuery(".tx_mask_tabcell3").on("keyup", ".tx_mask_field INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']", function (event) {
		syncBodyToHead(jQuery(this).closest(".tx_mask_field"));
	});

	// on focusout validate the field-key field
	jQuery(document).on("focusout", ".tx_mask_field INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']", function (event) {
		var table = jQuery("INPUT[name='tx_mask_tools_maskmask[storage][type]']").val();
		validateKeyField(this, table);
	});

	// on focusout validate the element-key field
	jQuery(document).on("focusout", "INPUT[name='tx_mask_tools_maskmask[storage][elements][key]']", function (event) {
		validateElementKeyField(this);
	});

	initSortable();

	// 1st column clone
	jQuery("#dragstart LI").draggable({
		connectToSortable: ".dragtarget",
		helper: "clone",
		revert: "invalid"
	});

	// 1st column click
	jQuery(".tx_mask_tabcell1").on("click", "LI", function (event) {
		buttonCode = jQuery.parseHTML(jQuery(this).outerHTML());
		jQuery(".tx_mask_tabcell2 LI").removeClass("active");
		jQuery(buttonCode).addClass("active");
		jQuery(".tx_mask_tabcell2 > UL").append(buttonCode);
		fieldType = jQuery(buttonCode).data("type");
		fieldTemplate = jQuery("#templates DIV[data-type='" + fieldType + "']").outerHTML();
		jQuery(".tx_mask_tabcell3>DIV").hide(); // Hide all fieldconfigs
		jQuery(".tx_mask_tabcell3").append(fieldTemplate); // Add new fieldconfig
		jQuery(buttonCode).click();
		jQuery(".tx_mask_newfieldname:visible").focus(); // Set focus to key field
		initSortable();
	});

	// 2nd column click
	jQuery(".tx_mask_tabcell2").on("click", "LI", function (event) {
		fieldIndex = jQuery(".tx_mask_tabcell2 UL LI").index(this);
		jQuery(".tx_mask_tabcell2 LI").removeClass("active");
		jQuery(this).addClass("active");
		jQuery(".tx_mask_tabcell3>DIV").hide(); // Hide all fieldconfigs
		jQuery(".tx_mask_tabcell3>DIV:eq(" + fieldIndex + ")").show(); // Show current fieldconfig
		event.stopPropagation(); // prevent other click events in Inline-Field
	});

	// 2nd column delete
	jQuery(".tx_mask_tabcell2").on("click", ".id_delete", function (event) {
		event.stopPropagation();
		var field = jQuery(this).closest("LI");
		deleteField(field);
		return false;
	});

	// 2nd column equal height to 1st column
	jQuery(".tx_mask_tabcell2 > .dragtarget").css("minHeight", jQuery('.tx_mask_tabcell1').innerHeight()+"px");

	jQuery("INPUT[type=submit]").on("click", function (e) {
		validateFields();
	});

	// Form Submit:
	jQuery('FORM[name=storage]').bind('submit', function (event) {

		// Merge eval fields:
		evalFields();
		linkFields();

		// Checkbox items:
		jQuery('.tx_mask_fieldcontent_items').each(function () {
			itemArray = this.value.split('\n');
			output = "";
			jQuery.each(itemArray, function (key, line) {
				lineArray = line.split(',');
				output += '<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][config][items][' + key + '][0]" value="' + lineArray[0] + '" />';
				if (lineArray[1] !== undefined) {
					output += '<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][config][items][' + key + '][1]" value="' + lineArray[1] + '" />';
				}
			});
			jQuery(this).parent().find(".tx_mask_fieldcontent_itemsresult").html(output);
		});
		// Drag and Drop löschen:
		jQuery("#dragstart .tx_mask_fieldcontent").remove();
		// Disable "field" Selectbox or Inputfield:
		jQuery("SELECT.tx_mask_fieldcontent_type").each(function (index) {
			if (jQuery(this).val() == -1) {
				// Disable Selectbox
				jQuery(this).prop('disabled', true);
			} else {
				// Remove Formfields for new Field
				jQuery(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_new').remove();
			}
		});
		editInlineFields();
		// Index in Arrays schreiben und inline-elemente zu ihren Eltern zuordnen
		jQuery(".tx_mask_fieldcontent").each(function (index, field) {
			var inputs = jQuery(this).find("INPUT, SELECT");
			// If the field is an line-field
			if (jQuery(field).find(".inline-container").size() > 0) {
				jQuery.each(inputs, function (inputIndex, input) {
					// Only change index of inputs not in the inlines
					if (jQuery(input).closest(".inline-container").size() == 0) {
						jQuery(input).attr('name', function (i, old) {
							return old.replace("--index--", index);
						});
					}
				});
			} else {
				// Change all the keys in this field
				jQuery.each(inputs, function (inputIndex, input) {
					jQuery(input).attr('name', function (i, old) {
						return old.replace("--index--", index);
					});
				});
			}
		});
	});
	// Neues Feld: new oder existing anzeigen:
	jQuery(document).on("change", ".tx_mask_fieldcontent_type", function (a) {
		if (jQuery(this).val() == '0') {
			// Show correct label and key in tabcell2
			var fieldIndex = jQuery(this).closest(".tx_mask_field").index();
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_keytext").html(
					  jQuery(this).closest(".tx_mask_field").find(".tx_mask_newfieldname").val()
					  );
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_labeltext").html(
					  jQuery(this).closest(".tx_mask_field").find(".tx_mask_fieldcontent_new INPUT[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val()
					  );

			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_existing').hide();
			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_new').hide();
		} else if (jQuery(this).val() == '-1') {

			// Hide inline-container if selected an "existing inline"
			var body = jQuery(this).closest(".tx_mask_field");
			showInlineContainer(body);

			// Show correct label and key in tabcell2
			var fieldIndex = jQuery(this).closest(".tx_mask_field").index();
			jQuery(this).closest(".tx_mask_field").find("INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']").removeAttr("disabled");
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_keytext").html(
					  jQuery(this).closest(".tx_mask_field").find(".tx_mask_newfieldname").val()
					  );
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_labeltext").html(
					  jQuery(this).closest(".tx_mask_field").find(".tx_mask_fieldcontent_new INPUT[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val()
					  );
			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_existing').hide();
			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_new').show();
		} else {
			// Hide inline-container if selected an "existing inline"
			var body = jQuery(this).closest(".tx_mask_field");
			hideInlineContainer(body);

			// Show correct label and key in tabcell2
			var fieldIndex = jQuery(this).closest(".tx_mask_field").index();

			jQuery(this).closest(".tx_mask_field").find("INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']").attr("disabled", "disabled");
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_keytext").html(jQuery(this).val());
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_labeltext").html(
					  jQuery(this).closest(".tx_mask_field").find(".tx_mask_fieldcontent_existing INPUT[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val()
					  );
			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_existing').show();
			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_new').hide();

		}
	});
});

//Do the magic to inline fields
function editInlineFields() {
	var inlineFields = jQuery(".inline-container LI");
	jQuery.each(inlineFields, function (i, field) {

		var mother = jQuery(field).closest("UL").closest("LI");

		var motherIndex = jQuery(".tx_mask_tabcell2 UL LI").index(mother);
		var fieldIndex = jQuery(".tx_mask_tabcell2 UL LI").index(field);

		var motherContent = jQuery(".tx_mask_tabcell3>DIV:eq(" + motherIndex + ")");
		var fieldContent = jQuery(".tx_mask_tabcell3>DIV:eq(" + fieldIndex + ")");

		// Search key of mother field and replace "tt_content" with "tx_mask_motherfieldkey"
		var motherFieldKey = jQuery(motherContent).find(".tx_mask_newfieldname").val();
		var label = jQuery(fieldContent).find(".tx_mask_fieldcontent_new INPUT[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val();

		jQuery(fieldContent).find("INPUT, SELECT").attr('name', function (i, old) {
			return old.replace("tt_content", "tx_mask_" + motherFieldKey);
		});
		jQuery(fieldContent).find("INPUT, SELECT").attr('name', function (i, old) {
			return old.replace("pages", "tx_mask_" + motherFieldKey);
		});
		jQuery(fieldContent).find(".tx_mask_fieldcontent").append('<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][inlineParent]" value="tx_mask_' + motherFieldKey + '" />');
		jQuery(fieldContent).find(".tx_mask_fieldcontent").append('<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][label]" value="' + label + '" />');
	});
}

// Merge eval-fields options together
function evalFields() {
	var fields = jQuery(".tx_mask_tabcell3 .tx_mask_field");
	jQuery.each(fields, function (i, item) {
		evalValues = new Array();
		jQuery(item).find('.tx_mask_fieldcontent_eval').each(function (index, value) {

			if (jQuery(value).attr("type") == "checkbox") {
				if (jQuery(value).is(':checked')) {
					evalValues[index] = jQuery(value).val();
				}
			} else if (jQuery(value).attr("type") == "hidden") {
				evalValues[index] = jQuery(value).val();
			} else if (jQuery(value).is("select")) {
				if (jQuery(value).val() !== undefined) {
					evalValues[index] = jQuery(value).val();
				}
			}
		});
		evalValues = jQuery.grep(evalValues, function (n) {
			return(n);
		});
		eval = evalValues.join(",");
		jQuery(item).find('.tx_mask_fieldcontent_evalresult').val(eval);
	});
}

// Merge link-field options together
function linkFields() {
	var fields = jQuery(".tx_mask_tabcell3 .tx_mask_field");
	jQuery.each(fields, function (i, item) {
		evalValues = new Array();
		jQuery(item).find('.tx_mask_fieldcontent_link').each(function (index, value) {

			if (jQuery(value).attr("type") == "checkbox") {
				if (jQuery(value).is(':checked')) {
					evalValues[index] = jQuery(value).val();
				}
			} else if (jQuery(value).attr("type") == "hidden") {
				evalValues[index] = jQuery(value).val();
			} else if (jQuery(value).is("select")) {
				if (jQuery(value).val() !== undefined) {
					evalValues[index] = jQuery(value).val();
				}
			}
		});
		evalValues = jQuery.grep(evalValues, function (n) {
			return(n);
		});
		eval = evalValues.join(",");
		jQuery(item).find('.tx_mask_fieldcontent_linkresult').val(eval);
	});
}

function prepareInlineFieldForInsert(field, template) {
	var newTemplate = jQuery.parseHTML(template);
	// Inline-Fields don't have the option to use existing fields
	if (jQuery(field).closest(".inline-container").size() > 0) {
		jQuery(newTemplate).find(".tx_mask_fieldcontent_existing").remove();
		jQuery(newTemplate).find(".tx_mask_fieldcontent_type").closest("LABEL").remove();
		jQuery(newTemplate).find(".tx_mask_fieldcontent_type").closest(".row").remove();
		jQuery(newTemplate).find(".tx_mask_fieldcontent_new").show();
	}
	return newTemplate;
}

function initSortable() {
	received = false;
	receivedNew = false;
	jQuery(".dragtarget").sortable({
		revert: true,
		placeholder: "tx_mask_fieldcontent_highlight",
		connectWith: ".dragtarget",
		start: function (event, ui) {
			// save index for resorting the 3rd column in "update"-Event
			var sorting = jQuery(".dragtarget LI").not(".tx_mask_fieldcontent_highlight");
			jQuery.each(sorting, function (index, e) {
				jQuery(e).attr("data-index", index);
			});
		},
		update: function (event, ui) { // On Drop:
			// if list received a new element
			if (received) {
				jQuery(".tx_mask_tabcell2 LI").removeClass("active");
				jQuery(ui.item).addClass("active");

				// if list received a new element from left column
				if (receivedNew) {
					var fieldType = jQuery(ui.item).data("type");
					var fieldTemplate = jQuery("#templates DIV[data-type='" + fieldType + "']").outerHTML();
					jQuery(".tx_mask_tabcell3>DIV").hide(); // Hide all fieldconfigs
					var newTemplate = prepareInlineFieldForInsert(ui.item, fieldTemplate);
					jQuery(".tx_mask_tabcell3").append(newTemplate); // Add new fieldconfig
					jQuery(".tx_mask_newfieldname:visible").focus(); // Set focus to key field
				}
			}
		},
		stop: function (event, ui) {
			initSortable();
			sortFields();
			jQuery(ui.item).click();
			if (receivedNew) {
				jQuery(".tx_mask_newfieldname:visible").focus();
			}
			receivedNew = false;
			received = false;
		},
		receive: function (event, ui) {
			received = true;
			if (jQuery(ui.sender).closest("UL").is("#dragstart")) {
				receivedNew = true;
			}
		}
	});
}

function sortFields() {
	// for each 2nd column LI, assign correct index to 3rd column DIV
	var transfer = jQuery(".dragtarget LI").not(".tx_mask_fieldcontent_highlight");
	jQuery.each(transfer, function (index, e) {
		jQuery(".tx_mask_tabcell3>DIV").eq(jQuery(e).attr("data-index")).attr("data-index", index);
	});
	// sort via newly assigned data-index
	jQuery(".tx_mask_tabcell3>DIV").sort(sort_li).appendTo('.tx_mask_tabcell3');
}

function sort_li(a, b) {
	return (parseInt(jQuery(b).attr('data-index'))) < parseInt(jQuery(a).attr('data-index')) ? 1 : -1;
}

function deleteField(field) {
	// If this field is inline-field, delete all its children
	if (jQuery(field).hasClass("id_Inline")) {
		var childrenFields = jQuery(field).find(" > .inline-container > LI");
		jQuery.each(childrenFields, function (index, elem) {
			deleteField(elem);
		});
	}
	fieldIndex = jQuery(".tx_mask_tabcell2 UL LI").index(field);
	var newItem = jQuery(field).prev(); // Save item to activate
	if (jQuery(newItem).size() == 0) { // Save item to activate, if first is deleted
		var newItem = jQuery(field).next();
	}
	jQuery(field).remove(); // remove from 2nd column
	jQuery(".tx_mask_tabcell3>DIV:eq(" + fieldIndex + ")").remove(); // remove from 3rd column
	jQuery(newItem).click(); // Activate new item
}

function validateFields() {
	jQuery("form input").unbind("invalid").bind('invalid', function (e) {
		// get error message from element
		var errorMessage = jQuery(this).attr("data-error");
		// search correct head to body for clicking it
		var body = jQuery(this).closest(".tx_mask_field");
		var head = findHeadByBody(body);
		e.target.setCustomValidity("");
		if (!e.target.validity.valid) {
			// click head to make field visible
			jQuery(head).click();
			if (errorMessage) {
				e.target.setCustomValidity(errorMessage);
			}
		}
	});
}
function validateKeyField(field, table) {
	if (jQuery(field).val() !== "" && !jQuery(field).attr("readonly")) {
		// Get ajax url from global TYPO3 variable
		var ajaxUrl = TYPO3.settings.ajaxUrls['WizardController::checkFieldKey'];
		var maskKey = "tx_mask_" + jQuery(field).val();
		var key = "tx_mask_" + jQuery(field).val();
		var params = {
			key: maskKey,
			table: table
		};


		// check if field is inline-field
		var body = jQuery(field).closest(".tx_mask_field");
		var head = findHeadByBody(body);

		// if field is not an inline-field
		if (jQuery(head).closest(".inline-container").size() > 0) {
			// if field is inline-field
			var motherHead = jQuery(head).parent().closest("LI");
			var motherBody = findBodyByHead(motherHead);
			var inlineTable = "tx_mask_" + jQuery(motherBody).find("INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']").val();
			params.table = inlineTable;
		}

		// Make ajax call
		jQuery.ajax({
			url: ajaxUrl,
			type: "GET",
			cache: false,
			dataType: "json",
			data: params
		}).done(function (isAvailable) {
			if (!isAvailable) {
				jQuery(field).val("");
				jQuery(field).addClass("not_unique");
			} else {
				jQuery(field).removeClass("not_unique");
			}
			syncBodyToHead(jQuery(field).closest(".tx_mask_field"));
		});
	}
}
function validateElementKeyField(field) {
	if (jQuery(field).val() !== "" && !jQuery(field).attr("readonly")) {
		// Get ajax url from global TYPO3 variable
		var ajaxUrl = TYPO3.settings.ajaxUrls['WizardController::checkElementKey'];
		var key = jQuery(field).val();
		var params = {
			key: key
		};

		// Make ajax call
		jQuery.ajax({
			url: ajaxUrl,
			type: "GET",
			cache: false,
			dataType: "json",
			data: params
		}).done(function (isAvailable) {
			if (!isAvailable) {
				jQuery(field).val("");
				jQuery(field).addClass("not_unique");
			} else {
				jQuery(field).removeClass("not_unique");
			}
		});
	}
}
function findHeadByBody(body) {
	var fieldIndex = jQuery(".tx_mask_tabcell3>DIV").index(body);
	var head = jQuery(".tx_mask_tabcell2 UL LI").eq(fieldIndex);
	return head;
}
function findBodyByHead(head) {
	var fieldIndex = jQuery(".tx_mask_tabcell2 UL LI").index(head);
	var body = jQuery(".tx_mask_tabcell3>DIV").eq(fieldIndex);
	return body;
}
// Add jQuery outerHTML Function
jQuery.fn.outerHTML = function (s) {
	return s
			  ? this.before(s).remove()
			  : jQuery("<p>").append(this.eq(0).clone()).html();
};
function syncBodyToHead(body) {
	var key = jQuery(body).find("INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']:visible").val();
	var title = jQuery(body).find("INPUT[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']:visible").val();

	var head = findHeadByBody(body);
	jQuery(head).find(" > .tx_mask_btn_row .id_keytext").html(key);
	var head = findHeadByBody(body);
	jQuery(head).find(" > .tx_mask_btn_row .id_labeltext").html(title);

	// Show correct label and key in tabcell3 on top
	jQuery(body).find(".tx_mask_fieldheader_text H1").html(title);
	jQuery(body).find(".tx_mask_fieldheader_text P").html(key);
}
function hideInlineContainer(body) {
	var fieldType = jQuery(body).attr("data-type");
	if (fieldType === "Inline") {
		var head = findHeadByBody(body);
		jQuery(head).addClass("existing_inline");
	}
}
function showInlineContainer(body) {
	var fieldType = jQuery(body).attr("data-type");
	if (fieldType === "Inline") {
		var head = findHeadByBody(body);
		jQuery(head).removeClass("existing_inline");
	}
}
