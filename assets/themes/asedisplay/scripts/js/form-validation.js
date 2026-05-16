(function () {
  'use strict';

  function isEmpty(el) {
    if (!el) return true;
    if (el.type === 'checkbox' || el.type === 'radio') return !el.checked;
    return (el.value || '').trim().length === 0;
  }

  function isRequired(el) {
    return el.required
      || el.classList.contains('required')
      || !!el.closest('.InputfieldStateRequired');
  }

  function setValidityClass(el) {
    const form = el && el.closest('form');
    if (!form) return;

    // Solo pinto clases si ya intentamos validar el form
    if (!form.classList.contains('was-validated')) return;

    const required = isRequired(el);
    if (isEmpty(el)) {
      if (required) {
        el.classList.remove('is-valid');
        el.classList.add('is-invalid');
      } else {
        el.classList.remove('is-valid', 'is-invalid');
      }
      return;
    }

    const valid = el.checkValidity();
    el.classList.toggle('is-valid', valid);
    el.classList.toggle('is-invalid', !valid);
  }

  function wireForm(form) {
    if (!form || form.__wired) return;
    form.__wired = true;

    // Limpieza inicial (por si el backend marcó algo)
    form.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));

    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');

    form.addEventListener('submit', function (event) {
      // Forzar evaluación nativa HTML5
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();

        form.classList.add('was-validated');
        form.querySelectorAll('input, select, textarea').forEach(setValidityClass);

        const invalid = form.querySelector(':invalid');
        if (invalid) {
          invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          invalid.focus({ preventScroll: true });
        }
        return;
      }

      // Form válido: bloquear doble submit y feedback
      if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;

        const isInput = submitBtn.tagName === 'INPUT';
        submitBtn.dataset.original = isInput ? submitBtn.value : submitBtn.innerHTML;

        if (isInput) {
          submitBtn.value = 'Signing in...';
        } else {
          const spinner = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>';
          submitBtn.innerHTML = spinner + 'Signing in...';
        }
      }
    }, false);

    // Live validation
    form.querySelectorAll('input, select, textarea').forEach(el => {
      el.addEventListener('input', () => setValidityClass(el));
      el.addEventListener('blur', () => setValidityClass(el));
      el.addEventListener('change', () => setValidityClass(el));
    });
  }

  // Auto-wire
  document.addEventListener('DOMContentLoaded', () => {
    const uniq = arr => Array.from(new Set(arr));
    const forms = uniq([
      ...document.querySelectorAll('form.needs-validation'),
      ...document.querySelectorAll('#login-form')
    ]);
    forms.forEach(wireForm);
  });
 
    const form = document.getElementById('reset-form');
          if (!form) return;

          const p1 = document.getElementById('Inputfield_password');
          const p2 = document.getElementById('Inputfield_password_confirm');

          // localizar invalid-feedback robusto (por markup de PW)
          const getFb = (input) => {
            let fb = input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')
              ? input.nextElementSibling : null;
            if (fb) return fb;
            const group = input.closest('.input-group');
            if (group && group.nextElementSibling && group.nextElementSibling.classList.contains('invalid-feedback')) {
              return group.nextElementSibling;
            }
            const wrap = input.closest('[id^="wrap_Inputfield_"]');
            if (wrap) {
              fb = wrap.querySelector('.invalid-feedback');
              if (fb) return fb;
            }
            return null;
          };
          const setInvalid = (input, msg) => {
            input.classList.add('is-invalid');
            input.setCustomValidity(msg || 'invalid');
            const fb = getFb(input);
            if (fb && !fb.classList.contains('d-block')) fb.style.display = 'block';
          };
          const clearInvalid = (input) => {
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
            const fb = getFb(input);
            if (fb && !fb.classList.contains('d-block')) fb.style.display = '';
          };

          // envolver en .input-group + botón ver/ocultar
          const enhance = (input) => {
            if (!input || input.closest('.input-group')) return;
            const group = document.createElement('div');
            group.className = 'input-group';
            input.parentElement.insertBefore(group, input);
            group.appendChild(input);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-secondary';
            btn.textContent = 'Ver';
            btn.addEventListener('click', () => {
              const isPwd = input.type === 'password';
              input.type = isPwd ? 'text' : 'password';
              btn.textContent = isPwd ? 'Ocultar' : 'Ver';
            });
            group.appendChild(btn);
          };
          enhance(p1); enhance(p2);

          const MIN = parseInt(p1?.getAttribute('minlength') || '8', 10);
          const validateMin = () => {
            if (!p1) return true;
            if (!p1.value) { clearInvalid(p1); return true; }
            if (p1.value.length < MIN) { setInvalid(p1, `Debe tener al menos ${MIN} caracteres.`); return false; }
            clearInvalid(p1); return true;
          };
          const validateMatch = () => {
            if (!p1 || !p2) return true;
            if (!p1.value || !p2.value) { clearInvalid(p2); return true; }
            if (p1.value !== p2.value) { setInvalid(p2, 'Las contraseñas no coinciden'); return false; }
            clearInvalid(p2); return true;
          };

          p1?.addEventListener('input', () => { validateMin(); validateMatch(); });
          p2?.addEventListener('input', validateMatch);

          form.addEventListener('submit', function(e){
            const ok = validateMin() & validateMatch();
            if (!ok || !form.checkValidity()) {
              e.preventDefault();
              e.stopPropagation();
            }
            form.classList.add('was-validated');
          });
        })();


         (function () {
  const form = document.getElementById('register-form');
  if (!form) return;

  const user = document.getElementById('Inputfield_username');
  const p1   = document.getElementById('Inputfield_password');
  const p2   = document.getElementById('Inputfield_password_confirm');

  // ===== utilidades feedback =====
  const getFeedback = (input) => {
    let fb = input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')
      ? input.nextElementSibling : null;
    if (fb) return fb;
    const group = input.closest('.input-group');
    if (group && group.nextElementSibling && group.nextElementSibling.classList.contains('invalid-feedback')) {
      return group.nextElementSibling;
    }
    const wrap = input.closest('[id^="wrap_Inputfield_"]');
    if (wrap) {
      fb = wrap.querySelector('.invalid-feedback');
      if (fb) return fb; 
    }
    return null;
  };
  const setInvalid = (input, msg) => {
    input.classList.add('is-invalid');
    input.setCustomValidity(msg || 'invalid');
    const fb = getFeedback(input);
    if (fb && !fb.classList.contains('d-block')) fb.style.display = 'block';
  };
  const clearInvalid = (input) => {
    input.classList.remove('is-invalid');
    input.setCustomValidity('');
    const fb = getFeedback(input);
    if (fb && !fb.classList.contains('d-block')) fb.style.display = '';
  };

  // ===== username: solo [A-Za-z0-9], sin espacios/guiones/símbolos =====
  const USER_MIN = parseInt(user?.getAttribute('minlength') || '3', 10);
  const sanitizeUsername = () => {
    if (!user) return;
    // elimina todo lo que no sea letra o número
    const cleaned = user.value.replace(/[^A-Za-z0-9]/g, '');
    if (cleaned !== user.value) user.value = cleaned;
  };
  const validateUsername = () => {
    if (!user) return true;
    sanitizeUsername();
    const val = user.value;
    if (!val) { clearInvalid(user); return true; } // que actúe 'required' del navegador
    if (!/^[A-Za-z0-9]+$/.test(val)) {
      setInvalid(user, 'Solo letras y números, sin espacios ni símbolos.');
      return false;
    }
    if (val.length < USER_MIN) {
      setInvalid(user, `Debe tener al menos ${USER_MIN} caracteres.`);
      return false;
    }
    clearInvalid(user);
    return true;
  };
  if (user) {
    user.addEventListener('input', validateUsername);
    user.addEventListener('blur', validateUsername);
    // evita pegar caracteres inválidos
    user.addEventListener('paste', () => setTimeout(validateUsername, 0));
  }

  // ===== password: toggle + match =====
  const enhancePassword = (input) => {
    if (!input || input.closest('.input-group')) return;
    const group = document.createElement('div');
    group.className = 'input-group';
    input.parentElement.insertBefore(group, input);
    group.appendChild(input);
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-secondary';
    btn.textContent = 'Ver';
    btn.addEventListener('click', () => {
      const isPwd = input.type === 'password';
      input.type = isPwd ? 'text' : 'password';
      btn.textContent = isPwd ? 'Ocultar' : 'Ver';
    });
    group.appendChild(btn);
  };
  enhancePassword(p1);
  enhancePassword(p2);

  const validateMatch = () => {
    if (!p1 || !p2) return true;
    if (!p1.value || !p2.value) { clearInvalid(p2); return true; }
    if (p1.value !== p2.value) { setInvalid(p2, 'Las contraseñas no coinciden'); return false; }
    clearInvalid(p2); return true;
  };
  if (p1) p1.addEventListener('input', validateMatch);
  if (p2) p2.addEventListener('input', validateMatch);
  if (p2) p2.addEventListener('blur', validateMatch);

  // ===== submit =====
  form.addEventListener('submit', function (e) {
    const okUser  = validateUsername();
    const okPass  = validateMatch();
    if (!okUser || !okPass || !form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);
})();