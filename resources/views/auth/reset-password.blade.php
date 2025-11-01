<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css" />
  <style>
    .field-error { color: var(--del-color); font-size: .9rem; margin-top: .25rem; }
    .is-invalid { border-color: var(--del-color) !important; }
    .spinner { width: 1em; height: 1em; border-radius: 50%; border: 2px solid currentColor; border-right-color: transparent; display: inline-block; animation: spin .6s linear infinite; vertical-align: -2px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .toast { position: fixed; inset-inline: 0; bottom: 1rem; display: grid; place-items: center; padding: 0 1rem; z-index: 9999; }
    .toast > div { max-width: 640px; width: 100%; }
  </style>
</head>
<body>
  <main class="container">
    <article>
      <h1>Reset Password</h1>
      <p>Please enter your email, new password, and confirm the password.</p>
      <form id="resetForm" method="POST" action="{{ url('/api/v1/auth/reset-password') }}" novalidate>
        <input type="hidden" name="token" id="token" value="{{ $token }}" />

        <label for="email">Email
          <input type="email" name="email" id="email" value="{{ $email }}" required />
        </label>
        <div class="field-error" id="emailError" aria-live="polite"></div>

        <label for="password">New Password
          <input type="password" name="password" id="password" required minlength="8" />
        </label>
        <small>At least 8 characters, mix letters, numbers, and symbols for stronger security.</small>
        <div class="field-error" id="passwordError" aria-live="polite"></div>

        <label for="password_confirmation">Confirm Password
          <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" />
        </label>
        <div class="field-error" id="passwordConfirmationError" aria-live="polite"></div>

        <div class="grid">
          <button id="submitBtn" type="submit">
            <span id="btnText">Reset Password</span>
            <span id="btnSpinner" class="spinner" style="display:none" aria-hidden="true"></span>
          </button>
        </div>

        <div class="field-error" id="formError" aria-live="polite"></div>
      </form>
    </article>
  </main>

  <dialog id="successDialog" aria-labelledby="successTitle">
    <article>
      <header>
        <h2 id="successTitle">Password reset successfully 🎉</h2>
      </header>
      <p>Your password has been updated successfully. You can now close this window or return to your app.</p>
      <footer class="grid">
        <button id="closeDialogBtn" class="secondary">Close</button>
      </footer>
    </article>
  </dialog>

  <div class="toast" id="toast" style="display:none;" aria-live="polite"></div>

  <script>
    (function () {
      const form = document.getElementById('resetForm');
      const submitBtn = document.getElementById('submitBtn');
      const btnText = document.getElementById('btnText');
      const btnSpinner = document.getElementById('btnSpinner');
      const successDialog = document.getElementById('successDialog');
      const closeDialogBtn = document.getElementById('closeDialogBtn');

      const email = document.getElementById('email');
      const password = document.getElementById('password');
      const passwordConfirmation = document.getElementById('password_confirmation');
      const token = document.getElementById('token');

      const emailError = document.getElementById('emailError');
      const passwordError = document.getElementById('passwordError');
      const passwordConfirmationError = document.getElementById('passwordConfirmationError');
      const formError = document.getElementById('formError');
      const toast = document.getElementById('toast');

      const apiUrl = form.getAttribute('action');

      function showToast(message, variant = 'primary') {
        const article = document.createElement('article');
        if (variant === 'success') article.setAttribute('data-theme', 'green');
        if (variant === 'warning') article.setAttribute('data-theme', 'amber');
        if (variant === 'error') article.setAttribute('data-theme', 'red');
        article.innerHTML = `<div>${message}</div>`;
        toast.innerHTML = '';
        toast.appendChild(article);
        toast.style.display = 'grid';
        setTimeout(() => { toast.style.display = 'none'; }, 4000);
      }

      function setFieldError(inputEl, errorEl, msg) {
        if (msg) {
          inputEl.classList.add('is-invalid');
          errorEl.textContent = msg;
        } else {
          inputEl.classList.remove('is-invalid');
          errorEl.textContent = '';
        }
      }

      function validate() {
        let ok = true;
        setFieldError(email, emailError, '');
        setFieldError(password, passwordError, '');
        setFieldError(passwordConfirmation, passwordConfirmationError, '');
        formError.textContent = '';

        if (!token.value || String(token.value).trim().length === 0) {
          ok = false;
          formError.textContent = 'Reset token is missing or invalid.';
        }

        if (!email.value) {
          ok = false; setFieldError(email, emailError, 'Email is required.');
        } else {
          const r = /[^@\s]+@[^@\s]+\.[^@\s]+/;
          if (!r.test(email.value)) { ok = false; setFieldError(email, emailError, 'Invalid email format.'); }
        }

        if (!password.value) {
          ok = false; setFieldError(password, passwordError, 'Password is required.');
        } else if (password.value.length < 8) {
          ok = false; setFieldError(password, passwordError, 'Minimum 8 characters required.');
        } else {
          const complexity = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{8,}$/;
          if (!complexity.test(password.value)) {
            setFieldError(password, passwordError, 'Use letters, numbers, and symbols for a stronger password.');
            ok = false;
          }
        }

        if (!passwordConfirmation.value) {
          ok = false; setFieldError(passwordConfirmation, passwordConfirmationError, 'Please confirm your password.');
        } else if (passwordConfirmation.value !== password.value) {
          ok = false; setFieldError(passwordConfirmation, passwordConfirmationError, 'Passwords do not match.');
        }
        return ok;
      }

      function setLoading(loading) {
        submitBtn.disabled = loading;
        btnText.style.display = loading ? 'none' : '';
        btnSpinner.style.display = loading ? 'inline-block' : 'none';
      }

      async function submitForm(ev) {
        ev.preventDefault();
        if (!validate()) return;

        setLoading(true);
        try {
          const payload = {
            token: token.value,
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value
          };

          const res = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
          });

          const data = await res.json().catch(() => ({}));

          if (!res.ok) {
            if (data && data.errors) {
              if (data.errors.email) setFieldError(email, emailError, data.errors.email[0]);
              if (data.errors.password) setFieldError(password, passwordError, data.errors.password[0]);
              if (data.errors.password_confirmation) setFieldError(passwordConfirmation, passwordConfirmationError, data.errors.password_confirmation[0]);
            }
            const message = (data && (data.message || data.error)) || 'Password reset failed. Please try again.';
            formError.textContent = message;
            showToast(message, 'error');
            return;
          }

          const successMsg = (data && data.message) || 'Password reset successfully.';
          showToast(successMsg, 'success');
          if (typeof successDialog?.showModal === 'function') successDialog.showModal();

        } catch (err) {
          formError.textContent = 'Network error. Please check your connection.';
          showToast('Network error. Please check your connection.', 'error');
        } finally {
          setLoading(false);
        }
      }

      form.addEventListener('submit', submitForm);
      passwordConfirmation.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') form.requestSubmit();
      });

      closeDialogBtn.addEventListener('click', () => {
        successDialog.close();
      });
    })();
  </script>
</body>
</html>
