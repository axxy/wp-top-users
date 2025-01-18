jQuery(document).ready(function ($) {
  function handleAjaxError(jqXHR, textStatus, errorThrown) {
    console.error("AJAX Error:", textStatus, errorThrown);
    console.error("Response:", jqXHR.responseText);
    alert("An error occurred. Please check the console for more details.");
  }

  function triggerBackgroundProcessing() {
    $.ajax({
      url: ay_test_plugin.ajax_url,
      type: "POST",
      data: {
        action: "trigger_background_processing",
        nonce: ay_test_plugin.nonce,
      },
      success: function (response) {
        if (response.success) {
          alert(response.data);
          $("#processing-status").text("Background processing is running...");
        } else {
          alert("Error: " + response.data);
        }
      },
      error: handleAjaxError,
    });
  }

  function clearApiCache() {
    $.ajax({
      url: ay_test_plugin.ajax_url,
      type: "POST",
      data: {
        action: "clear_api_cache",
        nonce: ay_test_plugin.nonce,
      },
      success: function (response) {
        if (response.success) {
          alert(response.data);
          location.reload();
        } else {
          alert("Error: " + response.data);
        }
      },
      error: handleAjaxError,
    });
  }

  // Pagination functionality
  const apiEntries = $(".api-entry");
  const entriesPerPage = 8;
  let currentPage = 1;

  const prevPageBtn = $("#prevPage");
  const nextPageBtn = $("#nextPage");
  const pageInfo = $("#pageInfo");

  function showPage(page) {
    const startIndex = (page - 1) * entriesPerPage;
    const endIndex = startIndex + entriesPerPage;

    apiEntries.each(function (index) {
      $(this).toggle(index >= startIndex && index < endIndex);
    });

    updatePageInfo();
    updateButtonStates();
  }

  function updatePageInfo() {
    const totalPages = Math.ceil(apiEntries.length / entriesPerPage);
    pageInfo.text(`Page ${currentPage} of ${totalPages}`);
  }

  function updateButtonStates() {
    prevPageBtn.prop("disabled", currentPage === 1);
    nextPageBtn.prop(
      "disabled",
      currentPage === Math.ceil(apiEntries.length / entriesPerPage)
    );
  }

  prevPageBtn.on("click", function () {
    if (currentPage > 1) {
      currentPage--;
      showPage(currentPage);
    }
  });

  nextPageBtn.on("click", function () {
    if (currentPage < Math.ceil(apiEntries.length / entriesPerPage)) {
      currentPage++;
      showPage(currentPage);
    }
  });

  // Event listeners
  // $(document).on(
  //   "click",
  //   "#trigger-background-processing",
  //   triggerBackgroundProcessing
  // );
  // $(document).on("click", "#clear-cache", clearApiCache);

  // Initialize pagination
  showPage(currentPage);
});
