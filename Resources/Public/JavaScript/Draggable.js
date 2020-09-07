define([
  'jquery',
  'TYPO3/CMS/Mask/Contrib/jquery-ui/draggable',
], function ($) {
  // 1st column clone
  $('#dragstart li').draggable({
    connectToSortable: '.dragtarget',
    helper: 'clone',
    revert: 'invalid',
  });
});
