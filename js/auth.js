function setError(inputEl, message) {
  const field = inputEl.closest(".auth-field");
  if (!field) return;

  const err = field.querySelector(".auth-error");
  if (!err) return;

  err.textContent = message || "";
  field.classList.toggle("has-error", Boolean(message));

  if (message) {
    inputEl.setAttribute("aria-invalid", "true");
  } else {
    inputEl.removeAttribute("aria-invalid");
  }
}

function validateUsername(inputEl) {
  const v = (inputEl.value || "").trim();

  if (v.length === 0) return "Wpisz nazwę użytkownika.";
  if (v.length < 3) return "Nazwa użytkownika musi mieć min. 3 znaki.";
  if (/\s/.test(v)) return "Nazwa użytkownika nie może zawierać spacji.";
  if (!/^[a-zA-Z0-9._-]+$/.test(v)) return "Dozwolone: litery, cyfry oraz . _ -";
  return "";
}

function validatePassword(inputEl) {
  const v = inputEl.value || "";
  if (v.length === 0) return "Wpisz hasło.";
  if (v.length < 8) return "Hasło musi mieć min. 8 znaków.";
  return "";
}

function bindAuthForm(form) {
  const user = form.querySelector('input[name="username"]');
  const pass = form.querySelector('input[name="password"]');
  const pass2 = form.querySelector('input[name="password2"]');

  function validateAll() {
    let ok = true;

    if (user) {
      const msg = validateUsername(user);
      setError(user, msg);
      ok = ok && !msg;
    }

    if (pass) {
      const msg = validatePassword(pass);
      setError(pass, msg);
      ok = ok && !msg;
    }

    if (pass2) {
      const v2 = pass2.value || "";
      let msg = "";
      if (v2.length === 0) msg = "Powtórz hasło.";
      else if ((pass?.value || "") !== v2) msg = "Hasła nie są takie same.";
      setError(pass2, msg);
      ok = ok && !msg;
    }

    return ok;
  }

  // walidacja na żywo
  [user, pass, pass2].filter(Boolean).forEach((el) => {
    el.addEventListener("input", () => {
      if (el === user) setError(user, validateUsername(user));
      if (el === pass) {
        setError(pass, validatePassword(pass));
        if (pass2) {
          const msg = pass2.value ? (((pass.value || "") === (pass2.value || "")) ? "" : "Hasła nie są takie same.") : "";
          setError(pass2, msg);
        }
      }
      if (el === pass2) {
        const msg = pass2.value ? (((pass?.value || "") === (pass2.value || "")) ? "" : "Hasła nie są takie same.") : "";
        setError(pass2, msg);
      }
    });

    el.addEventListener("blur", () => {
      if (el === user) setError(user, validateUsername(user));
      if (el === pass) setError(pass, validatePassword(pass));
      if (el === pass2) {
        const v2 = pass2.value || "";
        let msg = "";
        if (v2.length === 0) msg = "Powtórz hasło.";
        else if ((pass?.value || "") !== v2) msg = "Hasła nie są takie same.";
        setError(pass2, msg);
      }
    });
  });

  form.addEventListener("submit", (e) => {
    const ok = validateAll();
    if (!ok) {
      e.preventDefault();

      const firstError = form.querySelector(".auth-field.has-error input");
      if (firstError) firstError.focus();
    }
  });
}

document.querySelectorAll('form[data-auth-form="true"]').forEach(bindAuthForm);