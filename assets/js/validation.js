/**
 * Client-Side Form Validation
 * Habit Tracker Web Application
 */

document.addEventListener('DOMContentLoaded', () => {
  // Register Form Validation
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', (e) => {
      let isValid = true;
      clearFormErrors(registerForm);

      const nameInput = document.getElementById('name');
      const usernameInput = document.getElementById('username');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirm_password');

      // Name required
      if (!nameInput.value.trim()) {
        showError(nameInput, 'Full name is required.');
        isValid = false;
      }

      // Username validation
      const usernameVal = usernameInput.value.trim();
      if (!usernameVal) {
        showError(usernameInput, 'Username is required.');
        isValid = false;
      } else if (!/^[a-zA-Z0-9_-]{3,50}$/.test(usernameVal)) {
        showError(usernameInput, 'Username must be 3-50 letters, numbers, or underscores.');
        isValid = false;
      }

      // Email validation
      const emailVal = emailInput.value.trim();
      if (!emailVal) {
        showError(emailInput, 'Email address is required.');
        isValid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
        showError(emailInput, 'Please enter a valid email address.');
        isValid = false;
      }

      // Password validation
      if (!passwordInput.value) {
        showError(passwordInput, 'Password is required.');
        isValid = false;
      } else if (passwordInput.value.length < 6) {
        showError(passwordInput, 'Password must be at least 6 characters.');
        isValid = false;
      }

      // Confirm Password match validation
      if (!confirmPasswordInput.value) {
        showError(confirmPasswordInput, 'Please confirm your password.');
        isValid = false;
      } else if (passwordInput.value !== confirmPasswordInput.value) {
        showError(confirmPasswordInput, 'Passwords do not match.');
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  }

  // Login Form Validation
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      let isValid = true;
      clearFormErrors(loginForm);

      const identifierInput = document.getElementById('username_email');
      const passwordInput = document.getElementById('password');

      if (!identifierInput.value.trim()) {
        showError(identifierInput, 'Please enter your username or email.');
        isValid = false;
      }

      if (!passwordInput.value) {
        showError(passwordInput, 'Please enter your password.');
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  }
});

function showError(inputElement, message) {
  const formGroup = inputElement.closest('.form-group');
  if (formGroup) {
    formGroup.classList.add('has-error');
    let errorElem = formGroup.querySelector('.form-error');
    if (!errorElem) {
      errorElem = document.createElement('div');
      errorElem.className = 'form-error';
      formGroup.appendChild(errorElem);
    }
    errorElem.textContent = message;
    errorElem.style.display = 'block';
  }
}

function clearFormErrors(formElement) {
  const formGroups = formElement.querySelectorAll('.form-group');
  formGroups.forEach(group => {
    group.classList.remove('has-error');
    const errorElem = group.querySelector('.form-error');
    if (errorElem) {
      errorElem.style.display = 'none';
      errorElem.textContent = '';
    }
  });
}
