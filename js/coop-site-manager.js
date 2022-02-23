/**
 * @package ContactInfo
 * @copyright BC Libraries Coop 2013
 *
 **/

jQuery().ready(function () {
  jQuery('#coop-ci-submit').on('click', function () {
    var data = {
      action: 'coop-save-ci-change',
    };

    jQuery('input.coop-ci').each(function () {
      $input = jQuery(this);

      data[$input.attr('name')] = $input.val();
    });

    jQuery.post(ajaxurl, data).done(function (res) {
      alert(res.feedback);
    });
  });
});
