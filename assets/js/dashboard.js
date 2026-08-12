/**
 * Dashboard Interactive Script
 * Habit Tracker Web Application
 */

document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss flash messages after 5 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.3s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });
});
