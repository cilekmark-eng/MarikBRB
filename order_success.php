<?php
// order_success.php
$page_title = 'Заказ оформлен — ' . SITE_NAME;
$orderId = (int)($_GET['id'] ?? 0);
include __DIR__ . '/templates/header.php';
?>
<div class="empty-state" style="padding:80px 20px">
  <div class="empty-icon">✅</div>
  <h3 style="font-size:32px">Спасибо за заказ!</h3>
  <p style="font-size:16px;margin-top:8px">Заказ #<?= $orderId ?> принят. Мы свяжемся с вами в ближайшее время.</p>
  <div style="display:flex;gap:12px;justify-content:center;margin-top:24px">
    <a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-primary">Продолжить покупки</a>
    <?php if (auth()): ?>
      <a href="<?= SITE_URL ?>/?page=account&tab=orders" class="btn btn-outline">Мои заказы</a>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
