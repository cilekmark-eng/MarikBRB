<?php
$page_title = '404 — ' . SITE_NAME;
include __DIR__ . '/templates/header.php';
?>
<div class="empty-state" style="padding:80px 20px">
  <div style="font-family:var(--font-deco);font-size:120px;color:var(--border);line-height:1">404</div>
  <h3 style="font-size:28px">Страница не найдена</h3>
  <p>Возможно, она была удалена или вы ввели неверный адрес</p>
  <a href="<?= SITE_URL ?>/" class="btn btn-primary" style="margin-top:20px">На главную</a>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
