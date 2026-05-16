// assets/js/password-generator.js
// Requires: Bootstrap 5 styles, and optionally your form-validation.js for meter updates.

(function () {
  'use strict';

  function rand(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  function pick(arr) {
    return arr[rand(0, arr.length - 1)];
  }

  function shuffle(str) {
    return str.split('').sort(function(){return 0.5 - Math.random();}).join('');
  }

  /**
   * Generate a password that satisfies:
   * - length >= 8 (default 12)
   * - at least 1 digit
   * - at least 1 symbol
   * - at most 6 letters (A-Z or a-z)
   */
  function generatePassword(opts) {
    opts = opts || {};
    var length     = Math.max(8, opts.length || 12);
    var maxLetters = typeof opts.maxLetters === 'number' ? Math.max(0, opts.maxLetters) : 6;
    var minDigits  = Math.max(1, opts.minDigits || 1);
    var minSymbols = Math.max(1, opts.minSymbols || 1);

    var letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var digits  = '0123456789';
    // Símbolos visibles comunes; puedes ajustar según necesidad
    var symbols = '!@#$%^&*()_+-=[]{};:,.?';

    var out = '';

    // 1) Garantías mínimas
    for (var i = 0; i < minDigits; i++) out += pick(digits);
    for (var j = 0; j < minSymbols; j++) out += pick(symbols);

    // 2) Relleno respetando maxLetters
    var lettersUsed = 0;
    while (out.length < length) {
      var bucket = [];

      if (lettersUsed < maxLetters) bucket.push('L');
      bucket.push('D'); // siempre podemos añadir dígitos
      bucket.push('S'); // siempre podemos añadir símbolos

      var choice = pick(bucket);
      if (choice === 'L') { out += pick(letters); lettersUsed++; }
      else if (choice === 'D') out += pick(digits);
      else out += pick(symbols);
    }

    // 3) Aleatorizar posiciones
    out = shuffle(out);

    // 4) Validación final (por si acaso)
    var lettersCount = (out.match(/[A-Za-z]/g) || []).length;
    var hasDigit     = /\d/.test(out);
    var hasSymbol    = /\W/.test(out) || /[_\-!@#$%^&*()+=\[\]{};:,.?]/.test(out);

    if (lettersCount > maxLetters || !hasDigit || !hasSymbol || out.length < 8) {
      // reintenta con parámetros iguales (debería ser raro)
      return generatePassword(opts);
    }
    return out;
  }

  function wireGenerator(form) {
    var passInput = form.querySelector('input[name="pass"][data-password="true"]');
    var genBtn    = form.querySelector('[data-generate-password]');
    if (!passInput || !genBtn) return;

    genBtn.addEventListener('click', function () {
      var pwd = generatePassword({ length: 12, maxLetters: 6, minDigits: 1, minSymbols: 1 });
      passInput.value = pwd;

      // Trigger input event para actualizar validación/meter externos
      var ev = new Event('input', { bubbles: true });
      passInput.dispatchEvent(ev);

      // Opcional: seleccionar el campo para copiar fácilmente
      try { passInput.focus(); passInput.select(); } catch(e) {}
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('login-form'); // mismo id reutilizado
    if (form) wireGenerator(form);
  });
})();
