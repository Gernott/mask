jQuery.noConflict();
jQuery(document).ready(function () {

	// delete modal of content elements
	jQuery(document).on("click", ".deleteCe", function (event) {
		event.preventDefault();
		var deleteUrl = jQuery(this).attr("href");
		var purgeUrl = jQuery(this).data("purge-url");
		top.TYPO3.Modal.confirm(jQuery(this).data("title"), jQuery(this).data("content"), top.TYPO3.Severity.warning, [
			{
				text: jQuery(this).data("button-purge-text"),
				btnClass: 'btn-danger',
				trigger: function () {
					top.TYPO3.Modal.dismiss();
					window.location.href = purgeUrl;
				}
			}, {
				text: jQuery(this).data("button-close-text"),
				trigger: function () {
					top.TYPO3.Modal.dismiss();
				}
			}, {
				text: jQuery(this).data("button-ok-text"),
				active: true,
				btnClass: 'btn-warning',
				trigger: function () {
					top.TYPO3.Modal.dismiss();
					window.location.href = deleteUrl;
				}
			}
		]);
		return false;
	});


	// Transform inputs to lowercase and remove not allowed chars
	jQuery(document).on("change", "INPUT.lowercase", function (event) {
        jQuery(this).val(jQuery(this).val().toLowerCase());
        jQuery(this).val(jQuery(this).val().replace(/[^a-z0-9_]/g,''));
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

		// search for active field
		var activeFound = false;
		var activeHead = jQuery(".tx_mask_tabcell2 .tx_mask_btn.active");
		if (jQuery(activeHead).size() > 0) {
			activeFound = true;
			var activeBody = findBodyByHead(activeHead);
		}

		buttonCode = jQuery.parseHTML(jQuery(this).outerHTML());
		jQuery(".tx_mask_tabcell2 LI").removeClass("active");
		jQuery(buttonCode).addClass("active");

		// if active field was found, new field is inserted after this
		if (activeFound) {
			jQuery(activeHead).after(buttonCode);
		} else {
			jQuery(".tx_mask_tabcell2 > UL").append(buttonCode);
		}
		fieldType = jQuery(buttonCode).data("type");
		fieldTemplate = jQuery("#templates DIV[data-type='" + fieldType + "']").outerHTML();
		jQuery(".tx_mask_tabcell3>DIV").hide(); // Hide all fieldconfigs

		// if active field was found, new field is inserted after this
		if (activeFound) {
			if (jQuery(activeHead).hasClass("id_Inline")) {
				var tempActiveHead = jQuery(activeHead).find(".inline-container > LI:last");
				var tempActiveBody = findBodyByHead(tempActiveHead);
				jQuery(tempActiveBody).after(fieldTemplate);
			} else {
				jQuery(activeBody).after(fieldTemplate);
			}

		} else {
			jQuery(".tx_mask_tabcell3").append(fieldTemplate);
		}
		jQuery(buttonCode).click();
		jQuery(".tx_mask_newfieldname:visible").focus(); // Set focus to key field
		initSortable();
		var body = findBodyByHead(buttonCode);
		initializeTabs(body);
	});

	// 2nd column click
	jQuery(".tx_mask_tabcell2").on("click", "LI", function (event) {
		fieldIndex = jQuery(".tx_mask_tabcell2 UL LI").index(this);
		jQuery(".tx_mask_tabcell2 LI").removeClass("active");
		jQuery(this).addClass("active");
		jQuery(".tx_mask_tabcell3>DIV").hide(); // Hide all fieldconfigs
		jQuery(".tx_mask_tabcell3>DIV:eq(" + fieldIndex + ")").show(); // Show current fieldconfig
		event.stopPropagation(); // prevent other click events in Inline-Field
		var body = findBodyByHead(this);
		openFirstTab(body);
	});

	// 2nd column delete
	jQuery(".tx_mask_tabcell2").on("click", ".id_delete", function (event) {
		event.stopPropagation();
		var field = jQuery(this).closest("LI");
		deleteField(field);
		return false;
	});

	// 2nd column equal height to 1st column
	jQuery(".tx_mask_tabcell2 > .dragtarget").css("minHeight", jQuery('.tx_mask_tabcell1').innerHeight() + "px");

	jQuery("INPUT[type=submit]").on("click", function (e) {
		validateFields();
	});

	// Form Submit:
	jQuery('FORM[name=storage]').bind('submit', function (event) {

		// cType selectbox has wrong name when added fresh
		jQuery("SELECT[name='storage[tca][--index--][cTypes][]']").attr("name", "tx_mask_tools_maskmask[storage][tca][--index--][cTypes][]");

		// Merge eval fields:
		evalFields();
		linkFields();
		jsOpenParamsFields();
		rteTransformFields();

		// Checkbox items:
		jQuery('.tx_mask_fieldcontent_items').each(function () {
			itemArray = this.value.split('\n');
			output = "";
			jQuery.each(itemArray, function (key, line) {
				lineArray = line.split(',');
				output += '<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][config][items][' + key + '][0]" value="' + lineArray[0] + '" />';
				if (lineArray[1] !== undefined) {
					output += '<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][config][items][' + key + '][1]" value="' + lineArray[1].trim() + '" />';
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
			var inputs = jQuery(this).find("INPUT, SELECT, TEXTAREA");
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

			showTcaSettings(body);

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

			hideTcaSettings(body);

			jQuery(this).closest(".tx_mask_field").find("INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']").attr("disabled", "disabled");
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_keytext").html(jQuery(this).val());
			jQuery(".tx_mask_tabcell2 LI").eq(fieldIndex).find(".id_labeltext").html(
					  jQuery(this).closest(".tx_mask_field").find(".tx_mask_fieldcontent_existing INPUT[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val()
					  );
			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_existing').show();
			jQuery(this).closest(".tx_mask_fieldcontent").find('.tx_mask_fieldcontent_new').hide();

		}
	});

	// initialize font-icon-picker
	jQuery('#meta_icon').fontIconPicker({
		iconsPerPage: 20
	});

});

function hideTcaSettings(body) {
	jQuery(body).find("INPUT[name*='tx_mask_tools_maskmask[storage][tca]'], SELECT[name*='tx_mask_tools_maskmask[storage][tca]'], TEXTAREA[name*='tx_mask_tools_maskmask[storage][tca]']").attr("disabled", "disabled");
	jQuery(body).find(".t3js-tabmenu-item:not(.active)").hide();
}
function showTcaSettings(body) {
	jQuery(body).find("INPUT[name*='tx_mask_tools_maskmask[storage][tca]'], SELECT[name*='tx_mask_tools_maskmask[storage][tca]'], TEXTAREA[name*='tx_mask_tools_maskmask[storage][tca]']").removeAttr("disabled");
	jQuery(body).find(".t3js-tabmenu-item").show();
}

function initializeTabs(body) {
	var uniqueKey = getUniqueKey();
	var tabContents = jQuery(body).find(".tab-content .tab-pane");
	var tabHeads = jQuery(body).find(".nav-tabs");
	var tabLinks = jQuery(body).find(".nav-tabs LI A");
	jQuery.each(tabContents, function (index, content) {
		var id = jQuery(content).attr("id");
		jQuery(content).attr("id", id + uniqueKey);
	});
	jQuery.each(tabHeads, function (index, head) {
		var id = jQuery(head).attr("id");
		jQuery(head).attr("id", id + uniqueKey);
	});
	jQuery.each(tabLinks, function (index, link) {
		var href = jQuery(link).attr("href");
		jQuery(link).attr("href", href + uniqueKey);
	});
	openFirstTab(body);
}
function getUniqueKey() {
	return Math.random().toString(36).substr(2, 9);
}
function openFirstTab(body) {
	// if there is no tab open already, open the first
	var openedTab = jQuery(body).find(".tab-content .tab-pane.active");
	if (jQuery(openedTab).size() === 0) {
		var tabContents = jQuery(body).find(".tab-content .tab-pane");
		var tabLinks = jQuery(body).find(".nav-tabs LI A");
		jQuery(tabLinks).first().closest("LI").addClass("active");
		jQuery(tabContents).first().addClass("active");
	}
}

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

		// search is_in field
		var isInField = jQuery(item).find("INPUT[name='tx_mask_tools_maskmask[storage][tca][--index--][config][is_in]']");
		if (jQuery(isInField).size() > 0) {
			var isInValue = jQuery(isInField).val();
			if (isInValue !== "") {
				evalValues.push("is_in");
			}
		}

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

// Merge jsOpenParams options together
function jsOpenParamsFields() {
	var fields = jQuery(".tx_mask_tabcell3 .tx_mask_field");
	jQuery.each(fields, function (i, item) {
		evalValues = new Array();
		jQuery(item).find('.tx_mask_fieldcontent_jsopenparams').each(function (index, value) {
			var property = jQuery(value).attr("data-property");
			if (jQuery(value).attr("type") === "checkbox") {
				if (jQuery(value).is(':checked')) {
					evalValues[index] = property + "=" + jQuery(value).val();
				} else {
					evalValues[index] = property + "=0";
				}
			} else if (jQuery(value).attr("type") === "hidden" || jQuery(value).attr("type") === "text" || jQuery(value).attr("type") === "number") {
				evalValues[index] = property + "=" + jQuery(value).val();
			} else if (jQuery(value).is("select")) {
				if (jQuery(value).val() !== undefined) {
					evalValues[index] = property + "=" + jQuery(value).val();
				}
			}
		});
		evalValues = jQuery.grep(evalValues, function (n) {
			return(n);
		});
		eval = evalValues.join(",");
		jQuery(item).find('.tx_mask_fieldcontent_jsopenparams_result').val(eval);
	});
}
// Merge rte_transform fields together
function rteTransformFields() {
	var fields = jQuery(".tx_mask_tabcell3 .tx_mask_field");
	jQuery.each(fields, function (i, item) {
		var rteTransform = "";
		var mode = jQuery(item).find('.tx_mask_fieldcontent_rte_transform').val();
		if (mode !== "") {
			rteTransform = "richtext[]:rte_transform[" + mode + "]";
			jQuery(item).find('.tx_mask_fieldcontent_rte_transform_result').val(rteTransform);
		}
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
var received;
var receivedNew;
var sorted;

function initSortable() {

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
				var head = ui.item;
				jQuery(".tx_mask_tabcell2 LI").removeClass("active");
				jQuery(head).addClass("active");

				// if list received a new element from left column
				if (receivedNew) {
					var index = jQuery(".tx_mask_tabcell2 UL LI").index(head);
					var bodyAppender = jQuery(".tx_mask_tabcell3>DIV").eq(index - 1);

					var fieldType = jQuery(head).data("type");
					var fieldTemplate = jQuery("#templates DIV[data-type='" + fieldType + "']").outerHTML();
					jQuery(".tx_mask_tabcell3>DIV").hide(); // Hide all fieldconfigs
					var newTemplate = prepareInlineFieldForInsert(head, fieldTemplate);
					if (index === 0) {
						jQuery(".tx_mask_tabcell3").prepend(newTemplate); // Add new fieldconfig
					} else {
						if (bodyAppender) {
							jQuery(bodyAppender).after(newTemplate); // Add new fieldconfig
						} else {
							jQuery(".tx_mask_tabcell3").append(newTemplate); // Add new fieldconfig
						}
					}
					jQuery(".tx_mask_newfieldname:visible").focus(); // Set focus to key field
				}
			} else {
				received = false;
				receivedNew = false;
				sorted = false;
			}
		},
		stop: function (event, ui) {
			initSortable();
			if (!sorted) {
				sortFields();
				sorted = true;
			}

			var head = jQuery(ui.item);
			var body = findBodyByHead(head);
			jQuery(head).click();
			if (receivedNew) {
				initializeTabs(body);
				jQuery(".tx_mask_newfieldname:visible").focus();

			}
			receivedNew = false;
			received = false;
			sorted = false;
		},
		receive: function (event, ui) {

			received = false;
			receivedNew = false;
			sorted = false;

			// get head of the dragged field
			var head = ui.item;

			// check if field is allowed to be dragged here
			var allowed = true;
			var isMaskField = jQuery(head).attr("data-fieldtype") === "mask";
			var isNew = jQuery(head).attr("data-fieldtype") === undefined;
			var isDraggedIntoInline = jQuery(head).closest(".inline-container").size() > 0;

			if (isDraggedIntoInline && !isMaskField && !isNew) {
				allowed = false;
			}

			if (allowed) {

				received = true;
				// check if the received element is from first column
				if (jQuery(ui.sender).closest("UL").is("#dragstart")) {
					receivedNew = true;
				}

				// if not already sorted by stop event and if the element is not from the first column, sort
				if (!sorted) {
					initSortable();
					sortFields();
					sorted = true;
				}

				// body can only be fetched after sorting
				var body = findBodyByHead(head);

				// if the drag target is in an inline field container
				if (isDraggedIntoInline) {

					// hide the option to use existing field
					jQuery(body).find(".tx_mask_fieldcontent_existing").hide();
					jQuery(body).find(".tx_mask_fieldcontent_type").closest("LABEL").hide();
					jQuery(body).find(".tx_mask_fieldcontent_type").closest(".row").hide();
					jQuery(body).find(".tx_mask_fieldcontent_type").val("-1");
					jQuery(body).find("INPUT[name='tx_mask_tools_maskmask[storage][elements][columns][]']").removeAttr("disabled");
					jQuery(body).find(".tx_mask_fieldcontent_new").show();

					// and copy the label to keep it, for better user experience
					if (jQuery(body).find("#form_overwritelabel").size() > 0) {
						var overwriteLabel = jQuery(body).find("#form_overwritelabel").val();
						if (overwriteLabel !== "") {
							jQuery(body).find("#form_label").val(overwriteLabel);
						}
					}

					// then sync the body and the head
					syncBodyToHead(body);
				} else {
					// if it is not dragged into an inline element, just make sure all the options are shown
					jQuery(body).find(".tx_mask_fieldcontent_type").closest("LABEL").show();
					jQuery(body).find(".tx_mask_fieldcontent_type").closest(".row").show();
					jQuery(body).find(".tx_mask_fieldcontent_type").closest(".row").show();
				}
			} else {
				// if dragging is not allowed, abort
				alert("You are trying to drag an element which relies on a tt_content-field into a repeating field. This is not allowed, because it does not make any sense. Create a new field instead.");
				ui.sender.sortable('cancel');
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
		var childrenFields = jQuery(field).find(" > .inline-container > LI, > .tx_mask_btn_caption > .inline-container > LI");
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

		// open correct tab
		var tabBody = jQuery(this).closest(".tab-pane");
		var tabHead = jQuery(".t3js-tabmenu-item A[href='#" + jQuery(tabBody).attr("id") + "']").parent("LI");
		if (jQuery(tabBody).size() > 0) {
			jQuery(".tx_mask_field .t3js-tabmenu-item").removeClass("active");
			jQuery(".tx_mask_field .tab-pane").removeClass("active");
			jQuery(tabBody).addClass("active");
			jQuery(tabHead).addClass("active");
		}

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
	jQuery(head).find(" > .tx_mask_btn_row .id_keytext, > .id_keytext").html(key);
	var head = findHeadByBody(body);
	jQuery(head).find(" > .tx_mask_btn_row .id_labeltext, > .id_labeltext").html(title);

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
