<?php
// "Online Now" stat card. Include this inside your .stats row in dashboard.php.
// Admin: clickable button that opens the list. Other users: plain count card.
if (!isset($online)) { $online = online_users(); }
?>
<?php if (is_admin()): ?>
<button type="button" class="stat stat-click" id="onlineCard" title="See who is online">
  <span>Online Now</span>
  <strong><?= count($online) ?></strong>
</button>
<?php else: ?>
<div class="stat">
  <span>Online Now</span>
  <strong><?= count($online) ?></strong>
</div>
<?php endif; ?>