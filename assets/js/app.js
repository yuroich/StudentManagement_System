// Dashboard behaviour: modals, view/update loading, live search, delete confirm, menu.
(function () {
  'use strict';
  const $ = (sel) => document.querySelector(sel);

  // ----- Profile dropdown -----
  const profileBtn = $('#profileBtn');
  const dropdown = $('#profileDropdown');
  profileBtn.addEventListener('click', (e) => { e.stopPropagation(); dropdown.classList.toggle('open'); });
  document.addEventListener('click', () => dropdown.classList.remove('open'));

  // ----- Toast auto-hide -----
  const toast = $('#toast');
  if (toast) { setTimeout(() => toast.classList.add('hide'), 4000); }

  // ----- Modal helpers -----
  const viewModal = $('#viewModal');
  const formModal = $('#formModal');
  const open = (m) => { m.hidden = false; };
  const close = (m) => { m.hidden = true; };
  document.querySelectorAll('.modal').forEach((m) => {
    m.addEventListener('click', (e) => { if (e.target === m || e.target.hasAttribute('data-close')) close(m); });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { close(viewModal); close(formModal); }
  });

  async function fetchStudent(id) {
    const res = await fetch('actions/get_student.php?id=' + encodeURIComponent(id));
    if (!res.ok) { alert('Could not load that student.'); return null; }
    return res.json();
  }

  // ----- View student -----
  document.querySelectorAll('[data-view]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const s = await fetchStudent(btn.dataset.view);
      if (!s) return;
      const rows = [
        ['Name', s.full_name], ['Course', s.course], ['Year & Block', s.year_level + s.block],
        ['Contact', s.contact], ['Gender', s.gender], ['Address', s.address],
        ['Birth Date', s.birth_date], ['Status', s.status],
      ];
      const list = $('#profileList');
      list.innerHTML = '';
      rows.forEach(([label, value]) => {          // textContent => safe against XSS
        const dt = document.createElement('dt'); dt.textContent = label + ':';
        const dd = document.createElement('dd'); dd.textContent = value;
        list.append(dt, dd);
      });
      open(viewModal);
    });
  });

  // ----- Add student -----
  const form = $('#studentForm');
  $('#addBtn').addEventListener('click', () => {
    form.reset();
    form.action = 'actions/add_student.php';
    $('#f_id').value = '';
    $('#formTitle').textContent = 'Add New Student';
    $('#formSubmit').textContent = 'ADD STUDENT';
    open(formModal);
    $('#f_name').focus();
  });

  // ----- Update student (admin only) -----
  document.querySelectorAll('[data-edit]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const s = await fetchStudent(btn.dataset.edit);
      if (!s) return;
      form.action = 'actions/update_student.php';
      $('#f_id').value = s.id;
      $('#f_name').value = s.full_name;
      $('#f_contact').value = s.contact === 'N/A' ? '' : s.contact;
      $('#f_gender').value = s.gender;
      $('#f_address').value = s.address;
      $('#f_birth').value = s.birth_date;
      $('#f_course').value = s.course;
      $('#f_year').value = s.year_level;
      $('#f_block').value = s.block;
      $('#f_status').value = s.status;
      $('#formTitle').textContent = 'Update Student Information';
      $('#formSubmit').textContent = 'UPDATE STUDENT';
      open(formModal);
    });
  });

  // ----- Delete confirmation -----
  document.querySelectorAll('form[data-confirm]').forEach((f) => {
    f.addEventListener('submit', (e) => { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
  });

  // ----- Live search suggestions -----
  const input = $('#searchInput');
  const box = $('#suggestions');
  let timer;

  function showSuggestions(items, hasQuery) {
    box.innerHTML = '';
    if (!hasQuery) { box.hidden = true; return; }
    if (!items.length) {
      const none = document.createElement('div');
      none.className = 'no-result';
      none.textContent = 'No results found';
      box.append(none);
    }
    items.forEach((it) => {
      const a = document.createElement('a');
      a.href = 'dashboard.php?q=' + encodeURIComponent(it.full_name);
      a.textContent = it.full_name;
      box.append(a);
    });
    box.hidden = false;
  }

  input.addEventListener('input', () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (!q) { showSuggestions([], false); return; }
    timer = setTimeout(async () => {
      try {
        const res = await fetch('actions/search.php?q=' + encodeURIComponent(q));
        showSuggestions(res.ok ? await res.json() : [], true);
      } catch (err) { box.hidden = true; }
    }, 200);
  });
  document.addEventListener('click', (e) => { if (!box.contains(e.target) && e.target !== input) box.hidden = true; });
})();
