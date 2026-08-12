/**
 * Admin JavaScript Confirmation Handlers
 * Habit Tracker Web Application
 */

document.addEventListener('DOMContentLoaded', () => {
  // Confirm user deactivation / activation
  const statusToggleLinks = document.querySelectorAll('.js-confirm-user-status');
  statusToggleLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const action = link.getAttribute('data-action') || 'change status of';
      if (!confirm(`Are you sure you want to ${action} this user account?`)) {
        e.preventDefault();
      }
    });
  });

  // Confirm category deletion
  const categoryDeleteLinks = document.querySelectorAll('.js-confirm-category-delete');
  categoryDeleteLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      if (!confirm('Are you sure you want to delete this category? If habits are assigned to it, deletion will be blocked.')) {
        e.preventDefault();
      }
    });
  });
});
