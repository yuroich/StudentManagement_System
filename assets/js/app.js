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
  const open = (m) => { closeAllSelects(); m.hidden = false; };
  const close = (m) => { m.hidden = true; };
  document.querySelectorAll('.modal').forEach((m) => {
    m.addEventListener('click', (e) => { if (e.target === m || e.target.hasAttribute('data-close')) close(m); });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { close(viewModal); close(formModal); }
  });

  // ----- Animated dropdowns -----
  // The pop-up list of a normal <select> is drawn by the browser, so CSS cannot animate it.
  // Every <select data-cs> keeps working as the real form field (hidden), while this builds
  // a styled button + list on top of it that opens with an animation.
  const customSelects = [];
  const closeAllSelects = (except) => customSelects.forEach((c) => { if (c.wrap !== except) c.close(); });
  const syncSelects = () => customSelects.forEach((c) => c.sync());   // call after changing a select by code

  function enhanceSelect(select) {
    const wrap = document.createElement('div');  wrap.className = 'cs';
    const btn = document.createElement('button'); btn.type = 'button'; btn.className = 'cs-btn';
    btn.setAttribute('aria-haspopup', 'listbox'); btn.setAttribute('aria-expanded', 'false');
    const text = document.createElement('span');  text.className = 'cs-text';
    const arrow = document.createElement('span'); arrow.className = 'cs-arrow'; arrow.setAttribute('aria-hidden', 'true');
    btn.append(text, arrow);
    const list = document.createElement('ul');    list.className = 'cs-list'; list.setAttribute('role', 'listbox');

    select.before(wrap);
    wrap.append(select, btn, list);
    select.classList.add('cs-native'); select.tabIndex = -1; select.setAttribute('aria-hidden', 'true');

    // Accessible name: the field's <label>, or the select's own aria-label
    const label = select.id ? document.querySelector('label[for="' + select.id + '"]') : null;
    if (label) {
      label.id = label.id || select.id + '_label';
      btn.setAttribute('aria-labelledby', label.id);
      label.addEventListener('click', () => btn.focus());
    } else if (select.getAttribute('aria-label')) {
      btn.setAttribute('aria-label', select.getAttribute('aria-label'));
    }

    let items = [];
    let active = -1;
    let swallowKeyup = false;

    function sync() {
      const idx = select.selectedIndex;
      text.textContent = idx >= 0 ? select.options[idx].textContent : '';
      items.forEach((li, i) => li.setAttribute('aria-selected', i === idx ? 'true' : 'false'));
    }
    function setActive(i) {
      if (!items.length) return;
      items.forEach((li) => li.classList.remove('active'));
      active = Math.max(0, Math.min(items.length - 1, i));
      items[active].classList.add('active');
      items[active].scrollIntoView({ block: 'nearest' });
    }
    function openList() {
      closeAllSelects(wrap);
      wrap.classList.remove('up');
      const rect = btn.getBoundingClientRect();
      const need = Math.min(list.scrollHeight, 220) + 12;
      // open upwards when there is no room below (e.g. the last fields of the form)
      if (window.innerHeight - rect.bottom < need && rect.top > need) wrap.classList.add('up');
      wrap.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      setActive(select.selectedIndex);
    }
    function closeList() {
      wrap.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }
    function choose(i) {
      const changed = select.selectedIndex !== i;
      select.selectedIndex = i;
      sync(); closeList(); btn.focus();
      if (changed) select.dispatchEvent(new Event('change', { bubbles: true }));   // runs the select's own onchange
    }

    Array.from(select.options).forEach((opt, i) => {
      const li = document.createElement('li');
      li.className = 'cs-opt'; li.setAttribute('role', 'option');
      li.textContent = opt.textContent;
      li.style.setProperty('--i', i);                 // used for the staggered fade-in
      li.addEventListener('click', () => choose(i));
      li.addEventListener('mouseenter', () => setActive(i));
      list.append(li); items.push(li);
    });

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      wrap.classList.contains('open') ? closeList() : openList();
    });
    btn.addEventListener('keydown', (e) => {
      const isOpen = wrap.classList.contains('open');
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        if (!isOpen) openList(); else setActive(active + (e.key === 'ArrowDown' ? 1 : -1));
      } else if ((e.key === 'Enter' || e.key === ' ') && isOpen) {
        e.preventDefault(); swallowKeyup = e.key === ' ';
        choose(active);
      } else if (e.key === 'Escape' && isOpen) {
        e.stopPropagation(); closeList();            // close the list only, not the whole pop-up form
      } else if (e.key === 'Tab') {
        closeList();
      }
    });
    btn.addEventListener('keyup', (e) => { if (swallowKeyup && e.key === ' ') { e.preventDefault(); swallowKeyup = false; } });

    customSelects.push({ wrap, sync, close: closeList });
    sync();
  }
  document.querySelectorAll('select[data-cs]').forEach(enhanceSelect);
  document.addEventListener('click', () => closeAllSelects());

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
    syncSelects();
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
      syncSelects();
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
