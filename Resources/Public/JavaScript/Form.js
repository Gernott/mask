define([
  'jquery',
  'TYPO3/CMS/Backend/Notification',
  'TYPO3/CMS/Mask/Utility',
  'TYPO3/CMS/Mask/Sortable',
  'TYPO3/CMS/Backend/DateTimePicker',
], function ($, Notification, Utility, Sortable, DateTimePicker) {
  return {
    elementTab: $('.tx_mask_tabcell3'),

    init: function () {
      this.registerEventListener();
      this.showMessages();
    },

    registerEventListener: function () {
      var MaskForm = this;
      // Transform inputs to lowercase and remove not allowed chars
      $(document).on('change', '.lowercase', function (event) {
        $(this).val($(this).val().toLowerCase());
        $(this).val($(this).val().replace(/[^a-z0-9_]/g, ''));
      });

      // Add title of field to 2nd column
      var labelSelector = ".tx_mask_field input[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']";
      this.elementTab.on('keyup blur', labelSelector, this.syncField);

      // Add key of field to 2nd column
      var keySelector = ".tx_mask_field input[name='tx_mask_tools_maskmask[storage][elements][columns][]']";
      this.elementTab.on('keyup', keySelector, this.syncField);

      // on focusout validate the field-key field
      $(document).on('focusout', keySelector, function () {
        var table = $("input[name='tx_mask_tools_maskmask[storage][type]']").val();
        MaskForm.validateKeyField(this, table);
      });

      // on focusout validate the element-key field
      $(document).on('focusout', "input[name='tx_mask_tools_maskmask[storage][elements][key]']", function () {
        MaskForm.validateElementKeyField(this);
      });

      // Field button clicked
      $('.tx_mask_tabcell4 .tx_mask_field_templates').on('click', 'li', this.insertField);

      var tablecell2 = $('.tx_mask_tabcell2');

      // 2nd column click
      tablecell2.on('click', 'li', function (event) {
        var fieldIndex = $('.tx_mask_tabcell2 ul li').index(this);
        $('.tx_mask_tabcell2 li').removeClass('active');
        $('.tx_mask_tabcell2 li').find('a').removeClass('active');
        $(this).addClass('active');
        $(this).find('a').addClass('active');
        $('.tx_mask_tabcell3 > div').hide(); // Hide all fieldconfigs
        $('.tx_mask_tabcell3 > div:eq(' + fieldIndex + ')').show(); // Show current fieldconfig
        event.stopPropagation(); // prevent other click events in Inline-Field
        Utility.openFirstTab(Utility.findBodyByHead(this));
      });

      // 2nd column delete
      tablecell2.on('click', '.id_delete', function (event) {
        event.stopPropagation();
        MaskForm.deleteField($(this).closest('li'));
      });

      $('input[type=submit]').on('click', function () {
        MaskForm.validateFields();
      });

      // Neues Feld: new oder existing anzeigen:
      $(document).on('change', '.tx_mask_fieldcontent_type', function () {
        var tableCell2Li = $('.tx_mask_tabcell2 li');
        var fieldIndex;
        var body;

        if ($(this).val() === '0') {
          // Show correct label and key in tabcell2
          fieldIndex = $(this).closest('.tx_mask_field').index();
          tableCell2Li.eq(fieldIndex).find('.id_keytext').html(
            $(this).closest('.tx_mask_field').find('.tx_mask_newfieldname').val()
          );
          tableCell2Li.eq(fieldIndex).find('.id_labeltext').html(
            $(this).closest('.tx_mask_field').find(".tx_mask_fieldcontent_new input[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val()
          );

          $(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_existing').hide();
          $(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_new').hide();
        } else if ($(this).val() === '-1') {

          // Hide inline-container if selected an 'existing inline'
          body = $(this).closest('.tx_mask_field');

          MaskForm.hideInlineContainer(body);

          $(body).find("input[name*='tx_mask_tools_maskmask[storage][tca]'], select[name*='tx_mask_tools_maskmask[storage][tca]'], textarea[name*='tx_mask_tools_maskmask[storage][tca]']").removeAttr('disabled');
          $(body).find('.t3js-tabmenu-item').show();

          // Show correct label and key in tabcell2
          fieldIndex = $(this).closest('.tx_mask_field').index();
          $(this).closest('.tx_mask_field').find("input[name='tx_mask_tools_maskmask[storage][elements][columns][]']").removeAttr('disabled');
          tableCell2Li.eq(fieldIndex).find('.id_keytext').html(
            $(this).closest('.tx_mask_field').find('.tx_mask_newfieldname').val()
          );
          tableCell2Li.eq(fieldIndex).find('.id_labeltext').html(
            $(this).closest('.tx_mask_field').find(".tx_mask_fieldcontent_new input[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val()
          );
          $(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_existing').hide();
          $(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_new').show();
        } else {
          // Hide inline-container if selected an 'existing inline'
          body = $(this).closest('.tx_mask_field');
          MaskForm.hideInlineContainer(body);

          // Show correct label and key in tabcell2
          fieldIndex = $(this).closest('.tx_mask_field').index();
          $(body).find("input[name*='tx_mask_tools_maskmask[storage][tca]'], select[name*='tx_mask_tools_maskmask[storage][tca]'], textarea[name*='tx_mask_tools_maskmask[storage][tca]']").attr('disabled', 'disabled');
          $(body).find(".t3js-tabmenu-item:not(.active)").hide();
          $(this).closest('.tx_mask_field').find("input[name='tx_mask_tools_maskmask[storage][elements][columns][]']").attr('disabled', 'disabled');
          tableCell2Li.eq(fieldIndex).find('.id_keytext').html($(this).val());
          tableCell2Li.eq(fieldIndex).find('.id_labeltext').html(
            $(this).closest('.tx_mask_field').find(".tx_mask_fieldcontent_existing input[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val()
          );
          $(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_existing').show();
          $(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_new').hide();
        }
      });

      $(document).on('change', '.js-internal-type', this.toggleAllowed);

      // Form Submit:
      $('form[name=storage]').on('submit', function () {

        // cType selectbox has wrong name when added fresh
        $("select[name='storage[tca][--index--][cTypes][]']").attr('name', 'tx_mask_tools_maskmask[storage][tca][--index--][cTypes][]');

        // Merge eval-fields options together
        var fields = $('.tx_mask_tabcell3 .tx_mask_field');

        $.each(fields, function (i, item) {
          var evalValues = MaskForm.evalFormValues('tx_mask_fieldcontent_eval', item);

          // search is_in field
          var isInField = $(item).find("input[name='tx_mask_tools_maskmask[storage][tca][--index--][config][is_in]']");
          if ($(isInField).length > 0) {
            var isInValue = $(isInField).val();
            if (isInValue !== '') {
              evalValues.push('is_in');
            }
          }

          $(item).find('.tx_mask_fieldcontent_evalresult').val(evalValues.join(','));
        });

        // Merge link-field options together
        $.each(fields, function (i, item) {
          var evalValues = MaskForm.evalFormValues('tx_mask_fieldcontent_link', item);
          $(item).find('.tx_mask_fieldcontent_linkresult').val(evalValues.join(','));
        });

        // Checkbox items:
        $('.tx_mask_fieldcontent_items').each(function () {
          var itemArray = this.value.split('\n');
          var output = '';
          $.each(itemArray, function (key, line) {
            var lineArray = line.split(',');
            for (var i = 0; i < 4; i++) {
              if (lineArray[i] !== undefined) {
                output += '<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][config][items][' + key + '][' + i + ']" value="' + lineArray[i].trim() + '" />';
              }
            }
          });
          $(this).parent().find('.tx_mask_fieldcontent_itemsresult').html(output);
        });
        // Delete Drag and Drop
        $('#dragstart .tx_mask_fieldcontent').remove();
        // Disable "field" Selectbox or Inputfield
        $('select.tx_mask_fieldcontent_type').each(function () {
          if ($(this).val() === '-1') {
            // Disable Selectbox
            $(this).prop('disabled', true);
          } else {
            // Remove Formfields for new Field
            $(this).closest('.tx_mask_fieldcontent').find('.tx_mask_fieldcontent_new').remove();
          }
        });

        // Do the magic to inline fields
        var inlineContainer = $('.inline-container');
        $.each(inlineContainer, function () {
          var isPalette = $(this).hasClass('palette-container');
          var inlinePalette = $(this).hasClass('palette-inline');

          $.each($(this).children(), function (i, field) {
            var mother = $(field).closest('ul').closest('li');
            var motherIndex = $('.tx_mask_tabcell2 ul li').index(mother);
            var motherContent = $('.tx_mask_tabcell3 > div:eq(' + motherIndex + ')');
            var motherFieldKey = $(motherContent).find('.tx_mask_newfieldname').val();

            if (inlinePalette) {
              var motherInline = mother.closest('ul').closest('li');
              var motherInlineIndex = $('.tx_mask_tabcell2 ul li').index(motherInline);
              var motherInlineContent = $('.tx_mask_tabcell3 > div:eq(' + motherInlineIndex + ')');
              var motherInlineFieldKey = $(motherInlineContent).find('.tx_mask_newfieldname').val();
            }

            var fieldIndex = $('.tx_mask_tabcell2 ul li').index(field);
            var fieldContent = $('.tx_mask_tabcell3 > div:eq(' + fieldIndex + ')');
            var fieldContentNew = $(fieldContent).find('.tx_mask_fieldcontent_new');

            var label = '';
            if (fieldContentNew.length > 0) {
              label = $(fieldContentNew).find("input[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val();
            } else {
              label = fieldContent.find("input[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']").val();
            }
            // Search key of mother field and replace "tt_content" with "tx_mask_motherfieldkey"
            var replaceKey = motherInlineFieldKey ? motherInlineFieldKey : motherFieldKey;
            if (!isPalette || inlinePalette) {
              $(fieldContent).find('input[name], select').attr('name', function (i, old) {
                return old.replace('[tt_content]', '[tx_mask_' + replaceKey + ']');
              });
              $(fieldContent).find('input[name], select').attr('name', function (i, old) {
                return old.replace('[pages]', '[tx_mask_' + replaceKey + ']');
              });
            }

            // Add inlineParent for back reference
            $(fieldContent).find('.tx_mask_fieldcontent').append('<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][inlineParent]" value="tx_mask_' + motherFieldKey + '" />');
            // Add label directly to tca as inline children are not listet in columns/labels
            $(fieldContent).find('.tx_mask_fieldcontent').append('<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][label]" value="' + label + '" />');

            // If palette add additional 'inPalette' attribute to distinguish from normal inline children
            if (isPalette) {
              $(fieldContent).find('.tx_mask_fieldcontent').append('<input type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][inPalette]" value="1" />');
            }
          });
        });

        // Write index to arrays
        $('.tx_mask_tabcell3 .tx_mask_fieldcontent').each(function (index) {
          var inputs = $(this).find('input[name], select, textarea');
            // Change all the keys in this field
            $.each(inputs, function (inputIndex, input) {
              $(input).attr('name', function (i, old) {
                return old.replace('--index--', index);
              });
            });
        });
      });

      $('.tx_mask_tabcell3').on('click', '.t3js-tabmenu-item a', function (e) {
        e.preventDefault();
        var currentTabContainer = $('.tx_mask_field[style$="block;"]');
        currentTabContainer.find('a.active').removeClass('active');
        $(this).addClass('active');
        var index = currentTabContainer.find('li').index($(this).parent());
        currentTabContainer.find('.tab-pane.active').removeClass('active');
        currentTabContainer.find('.tab-pane').eq(index).addClass('active');
      })
    },

    syncField: function () {
      Utility.syncBodyToHead($(this).closest('.tx_mask_field'));
    },

    insertField: function (e) {
      var activeFound = false;
      var activeHead = $('.tx_mask_tabcell2 .tx_mask_btn.active');
      var activeBody;

      // If there is an active field, remove active status
      if ($(activeHead).length > 0) {
        activeFound = true;
        $(activeHead).removeClass('active');
        $(activeHead).find('a').removeClass('active');
        activeBody = Utility.findBodyByHead(activeHead);
      }

      // Get html of chosen button type and insert it as new active
      var buttonCode = $.parseHTML($(e.currentTarget).outerHTML());
      $(buttonCode).addClass('active');
      if (activeFound) {
        $(activeHead).after(buttonCode);
      } else {
        $('.tx_mask_tabcell2 > ul').append(buttonCode);
      }

      // Hide last opened field config
      $('.tx_mask_tabcell3 > div').hide();

      // Get template by type and update ids
      var fieldType = $(buttonCode).data('type');
      var fieldTemplate = $("#templates div[data-type='" + fieldType + "']").outerHTML();
      fieldTemplate = Utility.updateIds(fieldTemplate);

      // if active field was found, new field is inserted after this
      if (activeFound) {
        // Place template after last inline element
        if (($(activeHead).hasClass('id_inline') || $(activeHead).hasClass('id_palette')) && $(activeHead).find('.inline-container').children().length > 0) {
          var tempActiveHead = $(activeHead).find('.inline-container > li:last');
          var tempActiveBody = Utility.findBodyByHead(tempActiveHead);
          $(tempActiveBody).after(fieldTemplate);
        } else {
          $(activeBody).after(fieldTemplate);
        }
      } else {
        $('.tx_mask_tabcell3').append(fieldTemplate);
      }

      // Show field config
      $(buttonCode).click();

      // Initialize DateTimePicker
      if (['date', 'datetime', 'timestamp'].includes(fieldType)) {
        $('.tx_mask_field[style$="block;"]').find('.t3js-datetimepicker').each(function () {
          // TODO unset value instead when resolved https://forge.typo3.org/issues/93729
          this.dataset.datepickerInitialized = 'undefined';
          DateTimePicker.initialize(this);
        });
      }

      // Set focus to key field
      $('.tx_mask_newfieldname:visible').focus();
      Sortable.initSortable();
      Utility.initializeTabs(Utility.findBodyByHead(buttonCode));
    },

    toggleAllowed: function () {
      var $allowed = $(this).closest('.tx_mask_fieldcontent').find('.js-allowed');
      if ($(this).val() === 'db') {
        $allowed.show();
        $allowed.find('input').attr('required', 'required');
      } else {
        $allowed.hide();
        $allowed.find('input').removeAttr('required');
      }
    },

    validateKeyField: function (field, table) {
      if ($(field).val() !== '' && !$(field).attr('readonly')) {
        // Get ajax url from global TYPO3 variable
        var ajaxUrl = TYPO3.settings.ajaxUrls['mask_check_field_key'];
        var maskKey = 'tx_mask_' + $(field).val();
        var params = {
          key: maskKey,
          table: table,
          type: $(field).closest('.tx_mask_field').data('type'),
          elementKey: $('#meta_key').val()
        };

        // check if field is inline-field
        var body = $(field).closest('.tx_mask_field');
        var head = Utility.findHeadByBody(body);
        var container = $(head).closest('.inline-container');

        // if field is not an inline-field
        if (container.length > 0 && !container.hasClass('palette-container')) {
          // if field is inline-field
          var motherHead = $(head).parent().closest('li');
          var motherBody = Utility.findBodyByHead(motherHead);
          params.table = 'tx_mask_' + $(motherBody).find("input[name='tx_mask_tools_maskmask[storage][elements][columns][]']").val();
        }

        // Make ajax call
        $.ajax({
          url: ajaxUrl,
          type: 'GET',
          cache: false,
          dataType: 'json',
          data: params
        }).done(function (result) {
          if (!result.isAvailable) {
            $(field).val('');
            $(field).addClass('not_unique');
          } else {
            $(field).removeClass('not_unique');
          }
          Utility.syncBodyToHead($(field).closest('.tx_mask_field'));
        });
      }
    },

    validateElementKeyField: function (field) {
      if ($(field).val() !== '' && !$(field).attr('readonly')) {
        // Get ajax url from global TYPO3 variable
        var ajaxUrl = TYPO3.settings.ajaxUrls['mask_check_element_key'];
        var key = $(field).val();
        var params = {
          key: key
        };

        // Make ajax call
        $.ajax({
          url: ajaxUrl,
          type: 'GET',
          cache: false,
          dataType: 'json',
          data: params
        }).done(function (result) {
          if (!result.isAvailable) {
            $(field).val('');
            $(field).addClass('not_unique');
          } else {
            $(field).removeClass('not_unique');
          }
        });
      }
    },

    validateFields: function () {
      $('form input').unbind('invalid').bind('invalid', function (e) {
        // get error message from element
        var errorMessage = $(this).attr('data-error');

        // search correct head to body for clicking it
        var body = $(this).closest('.tx_mask_field');
        var head = Utility.findHeadByBody(body);

        // open correct tab
        var tabBody = $(this).closest('.tab-pane');
        var tabHead = $(".t3js-tabmenu-item A[href='#" + $(tabBody).attr('id') + "']").parent('li');
        if ($(tabBody).length > 0) {
          $('.tx_mask_field .t3js-tabmenu-item').removeClass('active');
          $('.tx_mask_field .t3js-tabmenu-item a').removeClass('active');
          $('.tx_mask_field .tab-pane').removeClass('active');
          $(tabBody).addClass('active');
          $(tabHead).addClass('active');
          $(tabHead).find('a').addClass('active');
        }

        e.target.setCustomValidity('');
        if (!e.target.validity.valid) {
          // click head to make field visible
          $(head).click();
          if (errorMessage) {
            e.target.setCustomValidity(errorMessage);
          }
        }
      });
    },

    evalFormValues: function (cssClass, item) {
      var evalValues = [];
      $(item).find('.' + cssClass).each(function (index, value) {

        if ($(value).attr('type') === 'checkbox' || $(value).attr('type') === 'radio') {
          if ($(value).is(':checked')) {
            evalValues[index] = $(value).val();
          }
        } else if ($(value).attr('type') === 'hidden') {
          evalValues[index] = $(value).val();
        } else if ($(value).is('select')) {
          if ($(value).val() !== undefined) {
            evalValues[index] = $(value).val();
          }
        }
      });
      return $.grep(evalValues, function (n) {
        return (n);
      });
    },

    deleteField: function (field) {
      var MaskForm = this;
      // If this field is inline-field, delete all its children
      if ($(field).hasClass('id_inline') || $(field).hasClass('id_palette')) {
        var childrenFields = $(field).find(' > .inline-container > li, > .tx_mask_btn_caption > .inline-container > li');
        $.each(childrenFields, function (index, elem) {
          MaskForm.deleteField(elem);
        });
      }
      var fieldIndex = $('.tx_mask_tabcell2 ul li').index(field);
      var newItem = $(field).prev(); // Save item to activate
      if ($(newItem).length === 0) { // Save item to activate, if first is deleted
        newItem = $(field).next();
      }
      $(field).remove(); // remove from 2nd column
      $('.tx_mask_tabcell3 > div:eq(' + fieldIndex + ')').remove(); // remove from 3rd column
      $(newItem).click(); // Activate new item
    },

    hideInlineContainer: function (body) {
      var fieldType = $(body).attr('data-type');
      if (fieldType === 'inline') {
        var head = Utility.findHeadByBody(body);
        $(head).addClass('existing_inline');
      }
    },

    showMessages: function () {
      let messages = $('.typo3-messages > div');
      $.each(messages, function (index, message) {
        let title = $(message).find('.alert-title').html();
        if (typeof title === 'undefined') {
          title = '';
        }
        let text = $(message).find('.alert-message').html();
        if ($(this).hasClass('alert-danger')) {
          Notification.error(title, text);
        } else {
          Notification.success(title, text);
        }
      });
    }
  };
});
