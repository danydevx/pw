$(document).ready(function () {
  const form = $("form.form");

  form.each(function () {
    let $this = $(this);
    if (formHasGDPR($this)) {
      let gdpr = $this.find(".gdpr-input");

      gdpr.on("change", (e) => {
        let el = $(e.currentTarget);
        if (el.prop("checked")) {
          $this.find("button[type=submit]").prop("disabled", false);
        } else {
          $this.find("button[type=submit]").prop("disabled", true);
        }
      });
    }

    $this.on("submit", (e) => {
      e.preventDefault();
      let spinner = ` <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
  <span role="status">Sending...</span>`;

      let actionInput = $this.find("input[name=action]");

      if (actionInput.length > 0) {
        if (actionInput.val() === "subscribe") {
          spinner = `<div class="spinner-border" role="status">
  <span class="visually-hidden">Loading...</span>
</div>`;
        }
      }

      let button = $this.find("button[type=submit]");
      button.prop("disabled", true);
      let currentHTML = button.html();
      button.html(spinner);
      const toastMessage = new bootstrap.Toast($(".success_msg")[0]);
      var formData = $this.serialize();
      $.ajax({
        type: "POST",
        url: "php/form_process.php",
        data: formData,
        success: function (response) {
          if (response == "success") {
            if (actionInput.length > 0) {
              button.html(currentHTML);
              const toast_subscribe = new bootstrap.Toast(
                $(".success_subscribe")[0]
              );
              toast_subscribe.show();
            } else {
              toastMessage.show();
              button.html(currentHTML);
              button.prop("disabled", false);
            }
          } else {
            // errtoast.show();
            alert("Message Send Failed");
            button.html(currentHTML);
            button.prop("disabled", false);
          }
        },
      });
    });
  });

  function formHasGDPR(form) {
    const gdpr = form.find(".gdpr-input");
    return gdpr.length !== 0;
  }
});
