(function(window, document) {
  function initValidate(form, cfg) {
    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.validate !== "function") return false;
    var $form = window.jQuery(form);
    if (!$form.length) return false;
    if ($form.data("validator")) return true;

    if (!window.jQuery.validator.methods.webformDate) {
      window.jQuery.validator.addMethod("webformDate", function(value, element) {
        if (this.optional(element)) return true;
        return /^\d{4}-\d{2}-\d{2}$/.test(value);
      }, "Ingresa una fecha valida (AAAA-MM-DD).");
    }

    if (!window.jQuery.validator.methods.webformDateMin) {
      window.jQuery.validator.addMethod("webformDateMin", function(value, element, minDate) {
        if (this.optional(element) || !value) return true;
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
        return String(value) >= String(minDate);
      }, "La fecha es menor al minimo permitido.");
    }

    if (!window.jQuery.validator.methods.webformDateMax) {
      window.jQuery.validator.addMethod("webformDateMax", function(value, element, maxDate) {
        if (this.optional(element) || !value) return true;
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
        return String(value) <= String(maxDate);
      }, "La fecha es mayor al maximo permitido.");
    }

    var options = {
      ignore: ':hidden:not([name="company_website"])',
      rules: cfg.rules || {},
      messages: cfg.messages || {},
      errorElement: "small",
      errorClass: "webform-error",
      highlight: function(element) {
        element.classList.add("webform-invalid");
      },
      unhighlight: function(element) {
        element.classList.remove("webform-invalid");
      }
    };
    if (cfg.ajaxEnabled) options.onsubmit = false;
    $form.validate(options);
    return true;
  }

  function bindAjax(form) {
    if (!form || form.dataset.webformAjax !== "1") return;
    if (form.dataset.webformAjaxBound === "1") return;
    form.dataset.webformAjaxBound = "1";

    form.addEventListener("submit", function(e) {
      e.preventDefault();

      if (window.jQuery && window.jQuery.fn && window.jQuery.fn.validate) {
        var $form = window.jQuery(form);
        if ($form.data("validator") && !$form.valid()) return;
      }

      var feedback = form.querySelector(".webform-feedback");
      var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (submitBtn && submitBtn.disabled) return;

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add("is-loading");
        submitBtn.setAttribute("aria-busy", "true");
      }

      if (feedback) {
        feedback.style.display = "none";
        feedback.textContent = "";
      }

      var data = new FormData(form);
      data.set("webform_transport", "ajax");
      var targetUrl = form.getAttribute("action") || window.location.href;
      targetUrl += (targetUrl.indexOf("?") > -1 ? "&" : "?") + "webform_ajax_endpoint=1";

      var csrfInput = form.querySelector('input[name^="_post_token"], input[name="_post_token"], input._post_token');
      var csrfHeaderName = "";
      var csrfHeaderValue = "";
      if (csrfInput && csrfInput.name && csrfInput.value) {
        data.set(csrfInput.name, csrfInput.value);
        csrfHeaderName = "X-" + csrfInput.name;
        csrfHeaderValue = csrfInput.value;
      }

      var headers = {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json"
      };
      if (csrfHeaderName) {
        headers[csrfHeaderName] = csrfHeaderValue;
      }

      fetch(targetUrl, {
        method: "POST",
        body: data,
        credentials: "same-origin",
        headers: headers
      })
        .then(function(res) {
          return res.text().then(function(text) {
            var json = null;
            try {
              json = JSON.parse(text);
            } catch (parseErr) {
              console.error("Webform AJAX JSON parse error", parseErr, text);
            }
            if (!res.ok) throw new Error("HTTP " + res.status);
            if (!json) throw new Error("Invalid JSON response");
            return json;
          });
        })
        .then(function(json) {
          if (feedback) {
            feedback.classList.remove("is-success", "is-error");
            feedback.textContent = json.message || "No se pudo procesar el envio.";
            feedback.style.display = "block";
            feedback.classList.add(json.success ? "is-success" : "is-error");
          }
          if (json.debug) console.warn("Webform debug:", json.debug);
          if (json.success) form.reset();
        })
        .catch(function(err) {
          console.error("Webform AJAX submit error", err);
          if (feedback) {
            feedback.classList.remove("is-success", "is-error");
            feedback.textContent = "No se pudo procesar el envio.";
            feedback.style.display = "block";
            feedback.classList.add("is-error");
          }
        })
        .finally(function() {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove("is-loading");
            submitBtn.removeAttribute("aria-busy");
          }
        });
    });
  }

  window.WebformBuilderInitForm = function(cfg) {
    if (!cfg || !cfg.formId) return;
    var form = document.getElementById(cfg.formId);
    if (!form) return;

    var attempts = 0;
    var maxAttempts = 80;
    var timer = window.setInterval(function() {
      attempts++;
      initValidate(form, cfg);
      bindAjax(form);
      if (attempts >= maxAttempts || (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.validate === "function")) {
        window.clearInterval(timer);
      }
    }, 100);
  };

  if (Array.isArray(window.__webformInitQueue) && window.__webformInitQueue.length) {
    for (var i = 0; i < window.__webformInitQueue.length; i++) {
      window.WebformBuilderInitForm(window.__webformInitQueue[i]);
    }
    window.__webformInitQueue = [];
  }
})(window, document);
