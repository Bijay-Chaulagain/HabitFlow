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
  const themeToggleBtns = document.querySelectorAll('.js-theme-toggle');
  themeToggleBtns.forEach(btn => {
    if (theme === 'dark') {
      btn.innerHTML = '☀️'; // Sun icon for switching to light mode
      btn.setAttribute('title', 'Switch to Light Mode');
    } else {
      btn.innerHTML = '🌙'; // Moon icon for switching to dark mode
      btn.setAttribute('title', 'Switch to Dark Mode');
    }
  });
}
