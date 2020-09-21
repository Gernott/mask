define([
  'jquery',
  'TYPO3/CMS/Mask/Utility',
  'TYPO3/CMS/Mask/Contrib/jquery-ui/sortable',
], function ($, Utility) {
  return {
    received: null,
    receivedNew: null,
    sorted: null,

    initSortable: function () {
      $('.dragtarget').sortable(this.config());
    },

    sort_li: function (a, b) {
      return (parseInt($(b).attr('data-index'))) < parseInt($(a).attr('data-index')) ? 1 : -1;
    },

    sortFields: function () {
      // for each 2nd column LI, assign correct index to 3rd column DIV
      var transfer = $('.dragtarget li').not('.tx_mask_fieldcontent_highlight');
      $.each(transfer, function (index, e) {
        var indexBeforeSorting = $(e).attr('data-index');
        $('.tx_mask_tabcell3 > div').eq(indexBeforeSorting).attr('data-index', index);
      });
      // sort via newly assigned data-index
      $('.tx_mask_tabcell3 > div').sort(this.sort_li).appendTo('.tx_mask_tabcell3');
    },

    prepareInlineFieldForInsert: function (field, template) {
      var newTemplate = $.parseHTML(template);
      // Inline-Fields don't have the option to use existing fields
      var inLineContainer = $(field).closest('.inline-container');
      if ((inLineContainer.length > 0 && !inLineContainer.hasClass('palette-container')) || inLineContainer.hasClass('palette-inline')) {
        $(newTemplate).find('.tx_mask_fieldcontent_existing').remove();
        $(newTemplate).find('.tx_mask_fieldcontent_type').closest('label').remove();
        $(newTemplate).find('.tx_mask_fieldcontent_type').closest('.row').remove();
        $(newTemplate).find('.tx_mask_fieldcontent_new').show();
      }
      return newTemplate;
    },

    config: function () {
      var Sortable = this;
      return {
        revert: true,
        placeholder: 'tx_mask_fieldcontent_highlight',
        connectWith: '.dragtarget',

        start: function (event, ui) {
          // save index for resorting the 3rd column in "update"-Event
          var sorting = $('.dragtarget li').not('.tx_mask_fieldcontent_highlight');
          $.each(sorting, function (index, e) {
            $(e).attr('data-index', index);
          });
        },

        update: function (event, ui) { // On Drop:
          // if list received a new element
          if (Sortable.received) {
            var head = ui.item;
            $('.tx_mask_tabcell2 li').removeClass('active');
            $(head).addClass('active');

            // if list received a new element from left column
            if (Sortable.receivedNew) {
              var index = $('.tx_mask_tabcell2 ul li').index(head);
              var bodyAppender = $('.tx_mask_tabcell3 > div').eq(index - 1);

              var fieldType = $(head).data('type');

              // If palette dropped in inline, no existing fields allowed.
              var isPalette = fieldType === 'Palette';
              if (isPalette && $(head).closest('.inline-container').length > 0) {
                $(head).find('.inline-container').addClass('palette-inline');
              }

              var fieldTemplate = $("#templates div[data-type='" + fieldType + "']").outerHTML();
              fieldTemplate = Utility.updateIds(fieldTemplate);
              $('.tx_mask_tabcell3 > div').hide(); // Hide all fieldconfigs
              var newTemplate = Sortable.prepareInlineFieldForInsert(head, fieldTemplate);
              $(newTemplate).attr('data-index', index);
              if (index === 0) {
                $('.tx_mask_tabcell3').prepend(newTemplate); // Add new fieldconfig
              } else {
                if (bodyAppender) {
                  $(bodyAppender).after(newTemplate); // Add new fieldconfig
                } else {
                  $('.tx_mask_tabcell3').append(newTemplate); // Add new fieldconfig
                }
              }
              $('.tx_mask_tabcell3 > div').each(function (i) {
                $(this).attr('data-index', i);
              });
              $('.tx_mask_newfieldname:visible').focus(); // Set focus to key field
            }
          } else {
            Sortable.received = false;
            Sortable.receivedNew = false;
            Sortable.sorted = false;
          }
        },

        stop: function (event, ui) {
          if (!Sortable.sorted) {
            Sortable.sortFields();
            Sortable.sorted = true;
          }

          var head = $(ui.item);
          $(head).click();
          if (Sortable.receivedNew) {
            Utility.initializeTabs(Utility.findBodyByHead(head));
            $('.tx_mask_newfieldname:visible').focus();
          }
          Sortable.received = false;
          Sortable.receivedNew = false;
          Sortable.sorted = false;
        },

        receive: function (event, ui) {
          Sortable.received = false;
          Sortable.receivedNew = false;
          Sortable.sorted = false;

          // get head of the dragged field
          var head = ui.item;

          // check if field is allowed to be dragged here
          var message = '';
          var allowed = true;
          var isMaskField = $(head).data('fieldtype') === 'mask';
          var isNew = $(head).data('fieldtype') === undefined;
          var isPalette = $(head).data('type') === 'Palette';
          var draggedIntoPalette = $(event.target).hasClass('palette-container');
          var container = $(head).closest('.inline-container');
          var isDraggedIntoInline = container.length > 0 && !draggedIntoPalette;

          if (isDraggedIntoInline && !isMaskField && !isNew) {
            allowed = false;
            message = 'You are trying to drag an element which relies on a tt_content-field into a repeating field. This is not allowed, because it does not make any sense. Create a new field instead.';
          }

          if (isPalette && draggedIntoPalette) {
            allowed = false;
            message = 'You are trying to drag a palette into another palette. Impossible.';
          }

          if (allowed) {

            Sortable.received = true;
            // check if the received element is from first column
            if ($(ui.sender).closest('ul').is('#dragstart')) {
              Sortable.receivedNew = true;
            }

            // if not already sorted by stop event and if the element is not from the first column, sort
            if (!Sortable.sorted) {
              Sortable.initSortable();
              if (!Sortable.receivedNew) {
                Sortable.sortFields();
              }
              Sortable.sorted = true;
            }

            // body can only be fetched after sorting
            var body = Utility.findBodyByHead(head);

            // if the drag target is in an inline field container
            if (isDraggedIntoInline) {

              // hide the option to use existing field
              $(body).find('.tx_mask_fieldcontent_existing').hide();
              $(body).find('.tx_mask_fieldcontent_type').closest('label').hide();
              $(body).find('.tx_mask_fieldcontent_type').closest('.row').hide();
              $(body).find('.tx_mask_fieldcontent_type').val('-1');
              $(body).find("input[name='tx_mask_tools_maskmask[storage][elements][columns][]']").removeAttr('disabled');
              $(body).find('.tx_mask_fieldcontent_new').show();

              // and copy the label to keep it, for better user experience
              if ($(body).find('#form_overwritelabel').length > 0) {
                var overwriteLabel = $(body).find('#form_overwritelabel').val();
                if (overwriteLabel !== '') {
                  $(body).find('#form_label').val(overwriteLabel);
                }
              }

              // then sync the body and the head
              Utility.syncBodyToHead(body);
            } else {
              // if it is not dragged into an inline element, just make sure all the options are shown
              $(body).find('.tx_mask_fieldcontent_type').closest('label').show();
              $(body).find('.tx_mask_fieldcontent_type').closest('.row').show();
              $(body).find('.tx_mask_fieldcontent_type').closest('.row').show();
            }
          } else {
            // if dragging is not allowed, abort
            alert(message);
            ui.sender.sortable('cancel');
          }
        }
      }
    }
  };
});
