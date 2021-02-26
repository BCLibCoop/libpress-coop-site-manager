/**
 * @package ContactInfo
 * @copyright BC Libraries Coop 2013
 *
 **/

function coop_save_ci_change() {
  var data = {
    action: 'coop-save-ci-change',
    heading: jQuery('#coop-ci-heading').val().trim(),
    email: jQuery('#coop-ci-email').val().trim(),
    phone: jQuery('#coop-ci-phone').val().trim(),
    fax: jQuery('#coop-ci-fax').val().trim(),
    address: jQuery('#coop-ci-address').val().trim(),
    address2: jQuery('#coop-ci-address-2').val().trim(),
    city: jQuery('#coop-ci-city').val().trim(),
    prov: jQuery('#coop-ci-prov').val().trim(),
    pcode: jQuery('#coop-ci-pcode').val().trim()
    // enable_form: jQuery('#coop-ci-enable_form').is(':checked')
  };

  jQuery.post(ajaxurl, data).done(function (res) {
    alert(res.feedback);
  });
}

jQuery().ready(function () {
  jQuery('#coop-ci-submit').on('click', coop_save_ci_change);
});
