define(['jquery'], function ($) {
  return {
    findHeadByBody: function (body) {
      var fieldIndex = $('.tx_mask_tabcell3 > div').index(body);
      return $('.tx_mask_tabcell2 ul li').eq(fieldIndex);
    },

    findBodyByHead: function (head) {
      var fieldIndex = $('.tx_mask_tabcell2 ul li').index(head);
      return $('.tx_mask_tabcell3 > div').eq(fieldIndex);
    },

    syncBodyToHead: function (body) {
      var key = $(body).find("input[name='tx_mask_tools_maskmask[storage][elements][columns][]']:visible").val();
      var title = $(body).find("input[name='tx_mask_tools_maskmask[storage][elements][labels][--index--]']:visible").val();

      var head = this.findHeadByBody(body);
      $(head).find(' > .tx_mask_btn_row .id_keytext, > .id_keytext').html(key);
      head = this.findHeadByBody(body);
      $(head).find(' > .tx_mask_btn_row .id_labeltext, > .id_labeltext').html(title);

      // Show correct label and key in tabcell3 on top
      $(body).find('.tx_mask_fieldheader_text h1').html(title);
      $(body).find('.tx_mask_fieldheader_text p').html(key);
    },

    initializeTabs: function (body) {
      var uniqueKey = this.getUniqueKey();
      var tabContents = $(body).find('.tab-content .tab-pane');
      var tabHeads = $(body).find('.nav-tabs');
      var tabLinks = $(body).find('.nav-tabs li a');
      $.each(tabContents, function (index, content) {
        var id = $(content).attr('id');
        $(content).attr('id', id + uniqueKey);
      });
      $.each(tabHeads, function (index, head) {
        var id = $(head).attr('id');
        $(head).attr('id', id + uniqueKey);
      });
      $.each(tabLinks, function (index, link) {
        var href = $(link).attr('href');
        $(link).attr('href', href + uniqueKey);
      });
      this.openFirstTab(body);
    },

    getUniqueKey: function () {
      return Math.random().toString(36).substr(2, 9);
    },

    openFirstTab: function (body) {
      // if there is no tab open already, open the first
      var openedTab = $(body).find('.tab-content .tab-pane.active');
      if ($(openedTab).length === 0) {
        var tabContents = $(body).find('.tab-content .tab-pane');
        var tabLinks = $(body).find('.nav-tabs li a');
        $(tabLinks).first().closest('li').addClass('active');
        $(tabLinks).first().closest('li').find('a').addClass('active');
        $(tabContents).first().addClass('active');
      }
    },

    updateIds: function (fieldTemplate) {
      var $updateIds = $(fieldTemplate).find('.js-update-id');
      var Utility = this;
      $updateIds.each(function () {
        var oldId = $(this).find('.checkbox-input').attr('id');
        var newId = 'new_' + Utility.getUniqueKey();
        var regExp = new RegExp(oldId, 'g');
        fieldTemplate = fieldTemplate.replace(regExp, newId);
      });
      return fieldTemplate;
    }
  };
});
