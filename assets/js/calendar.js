/**
 * Calendar UI Interaction Script
 * Habit Tracker Web Application
 */

document.addEventListener('DOMContentLoaded', () => {
  const calendarDays = document.querySelectorAll('.js-calendar-day');
  calendarDays.forEach(dayElem => {
    dayElem.addEventListener('click', () => {
      const detailsElem = dayElem.querySelector('.calendar-day-details');
      if (detailsElem) {
        const isVisible = detailsElem.style.display === 'block';
        // Hide all other day details
        document.querySelectorAll('.calendar-day-details').forEach(el => el.style.display = 'none');
        detailsElem.style.display = isVisible ? 'none' : 'block';
      }
    });
  });
});
