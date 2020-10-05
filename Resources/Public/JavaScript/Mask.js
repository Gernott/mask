define([
  'jquery',
  'TYPO3/CMS/Mask/Form',
  'TYPO3/CMS/Mask/Sortable',
  'TYPO3/CMS/Mask/Draggable',
  'TYPO3/CMS/Mask/List',
  'TYPO3/CMS/Mask/FontIconPicker',
], function ($, MaskForm, Sortable) {
  MaskForm.init();
  Sortable.initSortable();

  // Add $ outerHTML Function
  $.fn.outerHTML = function (s) {
    return s
      ? this.before(s).remove()
      : $('<p>').append(this.eq(0).clone()).html();
  };
});
