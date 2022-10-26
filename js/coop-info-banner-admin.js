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
    }
  });
})(jQuery, window);
