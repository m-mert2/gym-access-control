(function () {
  "use strict";
  window.SporSalonu = window.SporSalonu || {};

SporSalonu.showAlert = function (type, message, container = "body") {
    const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

    $(container).prepend(alertHtml);
    setTimeout(function () {
      $(".alert").fadeOut("slow", function () {
        $(this).remove();
      });
    }, 5000);
  };

SporSalonu.showLoading = function (show = true) {
    if (show) {
      if ($("#loadingOverlay").length === 0) {
        const loadingHtml = `
                    <div id="loadingOverlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                `;
        $("body").append(loadingHtml);
      }
    } else {
      $("#loadingOverlay").fadeOut("fast", function () {
        $(this).remove();
      });
    }
  };

SporSalonu.formatDate = function (dateString) {
    if (!dateString) return "-";

    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return `${day}.${month}.${year} ${hours}:${minutes}`;
  };

SporSalonu.formToJSON = function (formElement) {
    const formData = new FormData(formElement);
    const json = {};

    for (let [key, value] of formData.entries()) {
      json[key] = value;
    }

    return json;
  };

SporSalonu.confirm = function (message, callback) {
    if (confirm(message)) {
      callback();
    }
  };

SporSalonu.handleAjaxError = function (xhr, status, error) {
    console.error("AJAX Hatası:", { xhr, status, error });

    let errorMessage = "Bir hata oluştu!";

    if (xhr.responseJSON && xhr.responseJSON.message) {
      errorMessage = xhr.responseJSON.message;
    } else if (xhr.statusText) {
      errorMessage = xhr.statusText;
    }

    SporSalonu.showAlert("danger", errorMessage);
  };

SporSalonu.checkSession = function () {
    $.ajax({
      url: "/spor_salonu/api/check_session.php",
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (!response.loggedIn) {
          window.location.href = "/spor_salonu/index.php";
        }
      },
      error: function () {
      },
    });
  };

SporSalonu.logout = function () {
    SporSalonu.confirm("Çıkış yapmak istediğinize emin misiniz?", function () {
      window.location.href = "/spor_salonu/api/logout.php";
    });
  };

SporSalonu.validateTC = function (tc) {
    if (tc.length !== 11) return false;
    if (tc[0] === "0") return false;

    let sum = 0;
    for (let i = 0; i < 10; i++) {
      sum += parseInt(tc[i]);
    }

    if (sum % 10 !== parseInt(tc[10])) return false;

    return true;
  };

SporSalonu.validateEmail = function (email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  };

SporSalonu.validatePhone = function (phone) {
    const cleaned = phone.replace(/\D/g, "");
    return cleaned.length === 10 && cleaned[0] === "5";
  };

SporSalonu.initDataTable = function (tableId, options = {}) {
    const defaultOptions = {
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json",
      },
      pageLength: 25,
      responsive: true,
      order: [[0, "desc"]],
    };

    const mergedOptions = $.extend(true, {}, defaultOptions, options);

    return $(tableId).DataTable(mergedOptions);
  };

  $(document).ready(function () {
    if ($("body").hasClass("dashboard-page")) {
      setInterval(SporSalonu.checkSession, 300000);
    }
    $(document).on("click", ".logout-btn", function (e) {
      e.preventDefault();
      SporSalonu.logout();
    });
    $('form[data-ajax="true"]').on("submit", function (e) {
      e.preventDefault();
      SporSalonu.showLoading(true);

      const form = this;
      const url = $(form).attr("action");
      const method = $(form).attr("method") || "POST";

      $.ajax({
        url: url,
        type: method,
        data: $(form).serialize(),
        dataType: "json",
        success: function (response) {
          SporSalonu.showLoading(false);

          if (response.success) {
            SporSalonu.showAlert("success", response.message);

            if (response.redirect) {
              setTimeout(function () {
                window.location.href = response.redirect;
              }, 1000);
            }
          } else {
            SporSalonu.showAlert(
              "danger",
              response.message || "İşlem başarısız!"
            );
          }
        },
        error: SporSalonu.handleAjaxError,
      });
    });
    if (typeof bootstrap !== "undefined") {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }
    if (typeof bootstrap !== "undefined") {
      const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]')
      );
      popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
      });
    }
    setTimeout(function () {
      $(".alert-auto-dismiss").fadeOut("slow", function () {
        $(this).remove();
      });
    }, 5000);

    console.log("🏋️ ESOĞÜ Spor Salonu Sistemi v3.0 yüklendi");
  });
})();
