/**
 * Habit Management & Actions JavaScript
 * Habit Tracker Web Application
 */

document.addEventListener('DOMContentLoaded', () => {
  // Archive confirmation handler
  const archiveLinks = document.querySelectorAll('.js-confirm-archive');
  archiveLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      if (!confirm('Are you sure you want to archive this habit? It can be unarchived later from your habits list.')) {
        e.preventDefault();
      }
    });
  });

  // Delete confirmation handler
  const deleteLinks = document.querySelectorAll('.js-confirm-delete');
  deleteLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      if (!confirm('PERMANENT ACTION: Are you sure you want to delete this habit and all its completion records? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });
});
