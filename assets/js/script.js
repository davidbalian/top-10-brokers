/**
 * Top 10 Brokers Frontend Script
 */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // Add data-label attributes for responsive tables
    $(".top10-brokers-table").each(function () {
      var headers = [];

      // Get all header text
      $(this)
        .find("thead th")
        .each(function () {
          headers.push($(this).text());
        });

      // Add data-label attribute to each cell
      $(this)
        .find("tbody tr")
        .each(function () {
          $(this)
            .find("td")
            .each(function (index) {
              $(this).attr("data-label", headers[index]);
            });
        });
    });

    // Example of AJAX functionality if needed
    $(".top10-brokers-refresh").on("click", function (e) {
      e.preventDefault();

      var category = $(this).data("category");
      var tableContainer = $(this)
        .closest(".top10-brokers-container")
        .find(".top10-brokers-table-container");

      $.ajax({
        url: top10BrokersAjax.ajax_url,
        type: "POST",
        data: {
          action: "top10_brokers_get_brokers",
          nonce: top10BrokersAjax.nonce,
          category: category,
        },
        beforeSend: function () {
          tableContainer.addClass("loading");
        },
        success: function (response) {
          if (response.success) {
            // Update table with new data
            // Implementation would depend on your specific needs
          }
        },
        complete: function () {
          tableContainer.removeClass("loading");
        },
      });
    });

    // Star rating functionality
    var starWidth = 40; // Set this to the width of one star

    $.fn.stars = function () {
      return $(this).each(function () {
        $(this).html(
          $("<span />").width(
            Math.max(0, Math.min(5, parseFloat($(this).html()))) * starWidth
          )
        );
      });
    };

    // Initialize star ratings
    $("span.stars").stars();

    // Function to update button styles
    function updateButtonStyles() {
      if (typeof top10BrokersButtonColors !== "undefined") {
        $(".learn-more-button").each(function () {
          $(this).css({
            "background-color": top10BrokersButtonColors.bgColor,
            color: top10BrokersButtonColors.textColor,
          });
        });
      }
    }

    // Update styles on page load
    updateButtonStyles();

    // Update styles when AJAX content is loaded
    $(document).ajaxComplete(function () {
      updateButtonStyles();
    });

    // Update styles when the table is updated
    $(document).on("top10_brokers_table_updated", function () {
      updateButtonStyles();
    });
  });
})(jQuery);
