(function ($, window) {
  if (typeof window.acf !== "undefined") {
    window.acf.addAction('new_field/type=flexible_content', function (field) {
      // Remove default handler
      field.off('click [data-name="add-layout"]');

      var thisField = field;

      // Hook our new handler which just adds the hard-coded layout
      field.on('click [data-name="add-layout"]', function (e) {
        thisField.add({
          layout: "content",
          before: false
        })
      })
    })
  }
})(jQuery, window);
