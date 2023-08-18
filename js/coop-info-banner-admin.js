(function ($, window) {
  /**
   * Ready
   */
  $(function () {
    if (typeof window.acf !== "undefined") {
      window.acf.addFilter(
        "wysiwyg_tinymce_settings",
        function (init, id, field) {
          init.wpautop = false;
          init.remove_livebreaks = true;
          init.forced_root_block = false;
          init.relative_urls = true;

          if (field.data.toolbar === "basic") {
            init.toolbar1 = [
              "bold",
              "italic",
              "underline",
              "link",
              "undo",
              "redo",
            ].join(",");
            init.toolbar2 = "";
            init.toolbar3 = "";
            init.toolbar4 = "";
          }

          return init;
        }
      );

      window.acf.addFilter(
        "wysiwyg_quicktags_settings",
        function (init, id, field) {
          if (field.data.toolbar === "basic") {
            init.buttons = ["strong", "em", "link"].join(",");
          }

          return init;
        }
      );

      // Link start/end as range
      window.acf.addFilter(
        "date_time_picker_args",
        function (args, field) {
          args.onClose = function(dateText, instance) {
            var startDateTextBox = window.acf.getField('field_coop-info-banner_start').$inputText();
            var endDateTextBox = window.acf.getField('field_coop-info-banner_expires').$inputText();
            var thisDateBox = instance.input;
            var otherDateBox = instance.settings.altField.attr('id').indexOf('start') == -1 ? startDateTextBox : endDateTextBox;

            if (otherDateBox.valueOf() != '') {
              var testStartDate = startDateTextBox.datetimepicker('getDate');
              var testEndDate = endDateTextBox.datetimepicker('getDate');

              if (testStartDate > testEndDate) {
                otherDateBox.datetimepicker('setDate', thisDateBox.datetimepicker('getDate'));
              }
            } else {
              otherDateBox.valueOf(dateText);
            }
          };
          args.onSelect = function(selectedDateTime, instance) {
            var startDateTextBox = window.acf.getField('field_coop-info-banner_start').$inputText();
            var endDateTextBox = window.acf.getField('field_coop-info-banner_expires').$inputText();
            var thisDateBox = instance.input;
            var otherDateBox = instance.settings.altField.attr('id').indexOf('start') == -1 ? startDateTextBox : endDateTextBox;
            var option = instance.settings.altField.attr('id').indexOf('start') == -1 ? 'maxDate' : 'minDate';
            otherDateBox.datetimepicker('option', option, thisDateBox.datetimepicker('getDate') );
          }

          return args;
        }
      );
    }
  });
})(jQuery, window);
