define(['jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Severity'], function ($, Modal, Severity) {
  $(document).on('click', '.deleteCe', function (event) {
    event.preventDefault();
    var purgeUrl = $(this).data('purge-url');
    var deleteUrl = $(this).attr('href');
    Modal.confirm(
      $(this).data('title'),
      $(this).data('content'),
      Severity.warning,
      [
        {
          text: $(this).data('button-purge-text'),
          btnClass: 'btn-danger',
          trigger: function () {
            Modal.dismiss();
            window.location.href = purgeUrl;
          }
        },
        {
          text: $(this).data('button-close-text'),
          trigger: function () {
            Modal.dismiss();
          }
        },
        {
          text: $(this).data('button-ok-text'),
          active: true,
          btnClass: 'btn-warning',
          trigger: function () {
            Modal.dismiss();
            window.location.href = deleteUrl;
          }
        }
      ]);
  });
});
