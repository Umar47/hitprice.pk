document.addEventListener("DOMContentLoaded", function () {
  var panel = document.querySelector("[data-hitprice-filter-panel]");
  var openButton = document.querySelector("[data-hitprice-filter-open]");
  var closeButton = document.querySelector("[data-hitprice-filter-close]");
  var filterForm = document.querySelector("[data-hitprice-filter-form]");
  var sortForm = document.querySelector("[data-hitprice-sort-form]");
  var archive = document.querySelector(".hitprice-shop-archive");
  var results = document.querySelector("[data-hitprice-results]");
  var pagination = document.querySelector("[data-hitprice-pagination]");
  var summary = document.querySelector(".hitprice-shop-toolbar__summary .woocommerce-result-count");
  var debounceTimer = null;

  if (panel && openButton) {
    openButton.addEventListener("click", function () {
      panel.classList.add("is-open");
      document.documentElement.classList.add("hitprice-shop-filters-open");
    });
  }

  if (panel && closeButton) {
    closeButton.addEventListener("click", function () {
      panel.classList.remove("is-open");
      document.documentElement.classList.remove("hitprice-shop-filters-open");
    });
  }

  function buildFormData() {
    var data = new FormData();
    data.append("action", hitpriceShopArchive.action);
    data.append("nonce", hitpriceShopArchive.nonce);

    if (filterForm) {
      new FormData(filterForm).forEach(function (value, key) {
        data.append("query[" + key + "]", value);
      });
    }

    if (sortForm) {
      new FormData(sortForm).forEach(function (value, key) {
        if (key === "orderby") {
          data.set("query[" + key + "]", value);
        }
      });
    }

    return data;
  }

  function refreshArchive() {
    if (!filterForm || !archive || !results || !pagination || !window.hitpriceShopArchive) {
      return;
    }

    archive.classList.add("is-loading");

    fetch(hitpriceShopArchive.ajaxUrl, {
      method: "POST",
      body: buildFormData(),
      credentials: "same-origin"
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.success) {
          return;
        }

        results.innerHTML = payload.data.products;
        pagination.innerHTML = payload.data.pagination;

        if (summary && payload.data.resultCount) {
          summary.outerHTML = payload.data.resultCount;
          summary = document.querySelector(".hitprice-shop-toolbar__summary .woocommerce-result-count");
        }
      })
      .finally(function () {
        archive.classList.remove("is-loading");
      });
  }

  if (filterForm) {
    filterForm.addEventListener("change", function (event) {
      var input = event.target;
      if (!input || input.tagName !== "INPUT") {
        return;
      }

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(refreshArchive, 150);
    });
  }

  if (sortForm) {
    sortForm.addEventListener("submit", function (event) {
      event.preventDefault();
      refreshArchive();
    });
  }

  document.querySelectorAll("[data-filter-group]").forEach(function (group) {
    var toggle = group.querySelector(".hitprice-filter-group__toggle");
    var content = group.querySelector(".hitprice-filter-group__content");

    if (!toggle || !content) {
      return;
    }

    toggle.addEventListener("click", function () {
      var isExpanded = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", isExpanded ? "false" : "true");
      content.hidden = isExpanded;
    });
  });
});
