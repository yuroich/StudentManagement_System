// Small script for pages that only need the top-bar menu (details page): profile dropdown + toast auto-hide.
(function () {
  'use strict';
  const btn = document.getElementById('profileBtn');
  const dropdown = document.getElementById('profileDropdown');
  if (btn && dropdown) {
    btn.addEventListener('click', (e) => { e.stopPropagation(); dropdown.classList.toggle('open'); });
    document.addEventListener('click', () => dropdown.classList.remove('open'));
  }
  const toast = document.getElementById('toast');
  if (toast) { setTimeout(() => toast.classList.add('hide'), 4000); }
})();
