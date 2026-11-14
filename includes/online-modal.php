<?php
// Popup listing who is online (admin only). Include this just before </body> in dashboard.php.
if (!is_admin()) { return; }
if (!isset($online)) { $online = online_users(); }
?>
<div class="modal" id="onlineModal" hidden>
  <div class="modal-box">
    <button type="button" class="modal-close" data-close aria-label="Close">&times;</button>
    <h2>Users Online</h2>
    <?php if ($online): ?>
      <ul class="online-list">
        <?php foreach ($online as $u): ?>
          <li>
            <span class="dot"></span>
            <span class="who"><?= e($u['full_name']) ?> <small>@<?= e($u['username']) ?></small></span>
            <span class="role"><?= e(ucfirst($u['role'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="online-empty">Nobody is online right now.</p>
    <?php endif; ?>
  </div>
</div>
<script>
  (function () {
    var card = document.getElementById('onlineCard');
    var modal = document.getElementById('onlineModal');
    if (!card || !modal) return;
    card.addEventListener('click', function () { modal.hidden = false; });
    modal.addEventListener('click', function (e) {
      if (e.target === modal || e.target.hasAttribute('data-close')) modal.hidden = true;
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') modal.hidden = true; });
  })();
</script>