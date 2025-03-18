/**
 * Top 10 Brokers Admin Script
 */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // Dynamically load taxonomies for the dropdown
    var taxonomySelect = $(
      'select[name="top10_brokers_options[top10_brokers_taxonomy]"]'
    );

    if (taxonomySelect.length) {
      $.ajax({
        url: top10BrokersAjax.ajax_url,
        type: "POST",
        data: {
          action: "top10_brokers_get_taxonomies",
          nonce: top10BrokersAjax.nonce,
        },
        success: function (response) {
          if (response.success && response.data) {
            var currentValue = taxonomySelect.val();
            taxonomySelect.empty();

            $.each(response.data, function (key, value) {
              var option = $("<option></option>")
                .attr("value", key)
                .text(value);
              if (key === currentValue) {
                option.attr("selected", "selected");
              }
              taxonomySelect.append(option);
            });
          }
        },
      });
    }

    // Add any other admin functionality here
  });
})(jQuery);
