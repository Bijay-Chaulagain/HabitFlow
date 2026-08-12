/**
 * Theme Manager (Dark / Light Mode)
 * Uses localStorage to persist theme preference.
 */

document.addEventListener('DOMContentLoaded', () => {
  const currentTheme = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', currentTheme);
  updateThemeIcons(currentTheme);

  const themeToggleBtns = document.querySelectorAll('.js-theme-toggle');
  themeToggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const activeTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
      
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      updateThemeIcons(newTheme);
    });
  });
});

function updateThemeIcons(theme) {
  const sunIcon = '<svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>';
  const moonIcon = '<svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

  const themeToggleBtns = document.querySelectorAll('.js-theme-toggle');
  themeToggleBtns.forEach(btn => {
    if (theme === 'dark') {
      btn.innerHTML = sunIcon; // Sun icon for switching to light mode
      btn.setAttribute('title', 'Switch to Light Mode');
    } else {
      btn.innerHTML = moonIcon; // Moon icon for switching to dark mode
      btn.setAttribute('title', 'Switch to Dark Mode');
    }
  });
}
