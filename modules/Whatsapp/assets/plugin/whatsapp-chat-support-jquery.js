(function (window, document) {
  'use strict';

  // Utilidad: merge profundo muy simple (objetos planos)
  function deepMerge(target, source) {
    var t = target || {};
    for (var k in source) {
      if (!Object.prototype.hasOwnProperty.call(source, k)) continue;
      var sv = source[k];
      if (sv && typeof sv === 'object' && !Array.isArray(sv)) {
        t[k] = deepMerge(t[k] || {}, sv);
      } else {
        t[k] = sv;
      }
    }
    return t;
  }

  // Detecta móvil
  function isMobileUA() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }

  // Helpers DOM
  function qs(root, sel) { return (root || document).querySelector(sel); }
  function qsa(root, sel) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
  function on(el, ev, cb) { el && el.addEventListener(ev, cb, false); }
  function addClass(el, c) { el && el.classList.add(c); }
  function removeClass(el, c) { el && el.classList.remove(c); }
  function hasClass(el, c) { return el && el.classList.contains(c); }

  // Día -> número 0..6
  function getDayOfWeek(name) {
    var n = (name || '').toLowerCase();
    if (n === 'sunday') return 0;
    if (n === 'monday') return 1;
    if (n === 'tuesday') return 2;
    if (n === 'wednesday') return 3;
    if (n === 'thursday') return 4;
    if (n === 'friday') return 5;
    if (n === 'saturday') return 6;
    return -1;
  }

  // Clase principal
  function WhatsappChatSupport(container, options) {
    this.init(container, options);
  }

  // Defaults
  WhatsappChatSupport.DEFAULTS = {
    popupFx                   : '1',
    now                       : '',
    timezone                  : 'America/Chicago',
    notAvailableMsg           : 'No estoy disponible Ahora',
    almostAvailableMsg        : 'Estare disponible en un rato',
    dialogNotAvailableMsg     : 'No estoy disponible hoy',
    dialogAlmostAvailableMsg  : 'Pronto estaré disponible',
    defaultMsg                : 'En que le puedo ayudar',
    debug                     : false,
    onPopupOpen               : function(){},
    onPopupClose              : function(){},
    whenGoingToWhatsApp       : function(/*number, text*/){}
  };

  // Prototipo
  WhatsappChatSupport.prototype.init = function (container, options) {
    // [1] SETUP
    this.container = (typeof container === 'string') ? qs(document, container) : container;
    if (!this.container) return;

    this.settings = deepMerge({}, WhatsappChatSupport.DEFAULTS);
    this.settings = deepMerge(this.settings, options || {});

    // Sustituye marcadores de URL
    this.settings.defaultMsg = (this.settings.defaultMsg || '')
      .split('{{url}}').join(window.location.href)
      .split('[url]').join(window.location.href);

    // Elementos
    this.button        = qs(this.container, '.wcs_button');
    this.label         = qs(this.container, '.wcs_button_label');
    this.popup         = qs(this.container, '.wcs_popup');
    this.popupInput    = qs(this.container, '.wcs_popup_input');
    this.popupPersons  = qs(this.container, '.wcs_popup_person_container');

    addClass(this.container, 'wcs-effect-' + this.settings.popupFx);

    this.debugBox = null;
    if (this.settings.debug) {
      this.debugBox = document.createElement('div');
      this.debugBox.className = 'wcs_debug';
      document.body.appendChild(this.debugBox);
    }

    // [2] ON CLICK
    var self = this;

    // Abrir/cerrar
    var toggleOpen = function () {
      if (!self.popup) return;
      if (hasClass(self.container, 'wcs-show')) {
        self.closePopup();
      } else {
        self.openPopup();
      }
    };

    if (this.button) on(this.button, 'click', function (e) {
      // Si es botón sin popup, también dispara WhatsApp (comportamiento original)
      if (!self.popup) {
        var number = self.button.getAttribute('data-number');
        if (number && !hasClass(self.button, 'wcs_button_person_offline')) {
          self.goToWhatsApp(number, self.settings.defaultMsg);
        }
      } else {
        toggleOpen();
      }
    });

    if (this.label) on(this.label, 'click', function () {
      toggleOpen();
    });

    // Cerrar
    var popupClose = qs(this.popup, '.wcs_popup_close');
    if (popupClose) on(popupClose, 'click', this.closePopup.bind(this));

    // Enviar (single person)
    if (this.popupInput) {
      var sendIcon = qs(this.popupInput, '.fa');
      if (sendIcon) on(sendIcon, 'click', function () {
        var number = self.popupInput.getAttribute('data-number');
        var textEl = qs(self.popupInput, 'input[type="text"]');
        var text = textEl ? textEl.value : '';
        self.goToWhatsApp(number, text);
      });

      var textEl = qs(this.popupInput, 'input[type="text"]');
      if (textEl) on(textEl, 'keydown', function (e) {
        if (e.key === 'Enter') {
          var number = self.popupInput.getAttribute('data-number');
          self.goToWhatsApp(number, textEl.value);
        }
      });
    }

    // Click en representante (multi-person)
    if (this.popupPersons) {
      on(this.popupPersons, 'click', function (e) {
        var person = e.target.closest('.wcs_popup_person');
        if (!person) return;
        if (hasClass(person, 'wcs_popup_person_offline')) return;

        var defaultMsg = self.settings.defaultMsg;
        var custom = person.getAttribute('data-default-msg');
        if (custom) {
          defaultMsg = custom.split('{{url}}').join(window.location.href);
        }
        var num = person.getAttribute('data-number');
        if (num) self.goToWhatsApp(num, defaultMsg);
      });
    }

    // [5] CHECK AVAILABILITY
    this.computeAvailability();
  };

  // [3] OPEN / CLOSE
  WhatsappChatSupport.prototype.openPopup = function () {
    this.settings.onPopupOpen && this.settings.onPopupOpen();

    // Cierra otros widgets
    qsa(document, '.whatsapp_chat_support').forEach(function (c) {
      removeClass(c, 'wcs-show');
      var inp = qs(c, '.wcs_popup_input input[type="text"]');
      if (inp) inp.value = '';
    });

    if (this.label) addClass(this.label, 'wcs_button_label_hide');
    addClass(this.container, 'wcs-show');

    var self = this;
    setTimeout(function () {
      var inp = qs(self.popup, 'input');
      if (inp) {
        inp.value = self.settings.defaultMsg || '';
        inp.focus();
      }
    }, 50);
  };

  WhatsappChatSupport.prototype.closePopup = function () {
    this.settings.onPopupClose && this.settings.onPopupClose();
    if (this.label) removeClass(this.label, 'wcs_button_label_hide');
    removeClass(this.container, 'wcs-show');

    var inp = qs(this.container, '.wcs_popup_input input[type="text"]');
    if (inp) inp.value = '';
  };

  // [4] GO TO WHATSAPP
  WhatsappChatSupport.prototype.goToWhatsApp = function (number, text) {
    if (!number) return;
    this.settings.whenGoingToWhatsApp && this.settings.whenGoingToWhatsApp(number, text);

    this.closePopup();

    var base = isMobileUA() ? 'https://api.whatsapp.com/send' : 'https://web.whatsapp.com/send';
    var url = base + '?phone=' + encodeURIComponent(number) + '&text=' + encodeURIComponent(text || '');
    var win = window.open(url, '_blank');
    if (win && win.focus) win.focus();
  };

  // [5] Availability
  WhatsappChatSupport.prototype.computeAvailability = function () {
    var self = this;
    var now;

    if (window.moment) {
      now = window.moment();
      if (this.debugBox) {
        this.debugBox.insertAdjacentHTML('beforeend',
          '<div><strong>Original Date</strong> ' + now.format('YYYY-MM-DD HH:mm:ss') +
          ' <br><strong>UTC Offset</strong> ' + (now.utcOffset() / 60) + '</div>');
      }

      if (this.settings.timezone && !this.settings.now && now.tz) {
        now.tz(this.settings.timezone);
        if (this.debugBox) {
          this.debugBox.insertAdjacentHTML('beforeend',
            '<div><strong>Setting Timezone</strong> ' + now.format('YYYY-MM-DD HH:mm:ss') +
            ' <br><strong>UTC Offset</strong> ' + (now.utcOffset() / 60) + '</div>');
        }
      }
      if (this.settings.now) {
        now = window.moment(this.settings.now, 'YYYY-MM-DD HH:mm:ss');
        if (this.debugBox) {
          this.debugBox.insertAdjacentHTML('beforeend',
            '<div><strong>Setting Manual Date</strong> ' + now.format('YYYY-MM-DD HH:mm:ss') +
            ' <br><strong>UTC Offset</strong> ' + (now.utcOffset() / 60) + '</div>');
        }
      }
    } else {
      // Sin moment: usar Date nativo (sin timezone avanzado)
      now = new Date();
    }

    // Helpers de disponibilidad (compat con moment y Date nativo)
    function parseHM(str) {
      // "HH:mm" -> Date con hoy/ahora o moment con hoy
      var parts = String(str || '').split(':');
      var h = parseInt(parts[0] || '0', 10);
      var m = parseInt(parts[1] || '0', 10);
      if (window.moment) {
        var t = window.moment();
        if (self.settings.timezone && t.tz) t.tz(self.settings.timezone);
        t.hour(h).minute(m).second(0).millisecond(0);
        return t;
      } else {
        var d = new Date();
        d.setHours(h, m, 0, 0);
        return d;
      }
    }

    function isAfter(a, b) {
      return window.moment ? a.isAfter(b) : (a.getTime() > b.getTime());
    }
    function isBefore(a, b) {
      return window.moment ? a.isBefore(b) : (a.getTime() < b.getTime());
    }
    function sameDayNumber(d) {
      // 0..6
      return window.moment ? now.day() : now.getDay();
    }
    function fmt(d) {
      if (window.moment) return d.format('HH:mm');
      return String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
    }

    function isAvailable(availabilityObj) {
      var is_available = false;
      var almost_available = false;

      for (var key in availabilityObj) {
        if (!Object.prototype.hasOwnProperty.call(availabilityObj, key)) continue;

        if (getDayOfWeek(key) === sameDayNumber(now)) {
          var rng = String(availabilityObj[key] || '');
          var parts = rng.split('-');
          var start = parseHM((parts[0] || '').trim());
          var end   = parseHM((parts[1] || '').trim());

          if (window.moment) {
            if (now.isAfter(start) && now.isBefore(end)) {
              is_available = true;
            } else if (now.isBefore(start)) {
              almost_available = true;
            }
          } else {
            if (isAfter(now, start) && isBefore(now, end)) {
              is_available = true;
            } else if (isBefore(now, start)) {
              almost_available = true;
            }
          }
        }
      }
      return { is_available: is_available, almost_available: almost_available };
    }

    // BUTTON ONLY
    if (this.button && this.button.hasAttribute('data-availability')) {
      try {
        var availBtn = JSON.parse(this.button.getAttribute('data-availability'));
        var resB = isAvailable(availBtn);
        if (!resB.is_available) {
          addClass(this.button, 'wcs_button_person_offline');
          var st = qs(this.button, '.wcs_button_person_status');
          if (st) st.innerHTML = resB.almost_available ? this.settings.almostAvailableMsg : this.settings.notAvailableMsg;
        }
      } catch (e) {}
    }

    // SINGLE PERSON
    if (this.popupInput && this.popupInput.hasAttribute('data-availability')) {
      try {
        var availIn = JSON.parse(this.popupInput.getAttribute('data-availability'));
        var resI = isAvailable(availIn);
        if (!resI.is_available) {
          addClass(this.popupInput, 'wcs_popup_input_offline');
          this.popupInput.innerHTML = resI.almost_available ? this.settings.dialogAlmostAvailableMsg : this.settings.dialogNotAvailableMsg;
        }
      } catch (e) {}
    }

    // MULTIPLE PERSON
    if (this.popupPersons) {
      qsa(this.popupPersons, '.wcs_popup_person').forEach(function (el) {
        var json = el.getAttribute('data-availability');
        if (!json) return;
        try {
          var avail = JSON.parse(json);
          var res = isAvailable(avail);
          if (!res.is_available) {
            addClass(el, 'wcs_popup_person_offline');
            var st = qs(el, '.wcs_popup_person_status');
            if (st) st.innerHTML = res.almost_available ? self.settings.dialogAlmostAvailableMsg : self.settings.dialogNotAvailableMsg;
          }
        } catch (e) {}
      });
    }
  };

  // Método estático: montar fácilmente
  WhatsappChatSupport.mount = function (selector, options) {
    return qsa(document, selector).map(function (el) {
      return new WhatsappChatSupport(el, options);
    });
  };

  // Exponer global
  window.WhatsappChatSupport = WhatsappChatSupport;

  // Adaptador (opcional) para compat con $.fn.whatsappChatSupport
  if (window.jQuery && window.jQuery.fn) {
    window.jQuery.fn.whatsappChatSupport = function (options /*, content, callback */) {
      return this.each(function () {
        var inst = this.__wcs_instance;
        if (!inst && typeof options !== 'string') {
          inst = new WhatsappChatSupport(this, options || {});
          this.__wcs_instance = inst;
        } else if (inst && typeof options === 'string' && typeof inst[options] === 'function') {
          var args = Array.prototype.slice.call(arguments, 1);
          inst[options].apply(inst, args);
        }
      });
    };
  }

})(window, document);