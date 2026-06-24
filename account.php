<?php
require_auth();
$page_title = 'Личный кабинет — ' . SITE_NAME;
$tab = $_GET['tab'] ?? 'profile';

// Load full user
$userRow = db()->prepare("SELECT * FROM users WHERE id=:id");
$userRow->execute([':id' => auth()['id']]);
$userRow = $userRow->fetch();

// Orders
if ($tab === 'orders') {
    $st = db()->prepare(
        "SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS item_count
         FROM orders o WHERE o.user_id=:uid ORDER BY o.created_at DESC"
    );
    $st->execute([':uid' => auth()['id']]);
    $orders = $st->fetchAll();
}

// Wishlist
if ($tab === 'wishlist') {
    $st = db()->prepare(
        "SELECT p.*, ROUND(p.price*(1-p.discount/100),2) AS final_price,
                (SELECT filename FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS main_image
         FROM wishlist w JOIN products p ON p.id=w.product_id
         WHERE w.user_id=:uid AND p.is_active=1 ORDER BY w.added_at DESC"
    );
    $st->execute([':uid' => auth()['id']]);
    $wishItems = $st->fetchAll();
}

// Order detail
if ($tab === 'order' && isset($_GET['id'])) {
    $st = db()->prepare("SELECT * FROM orders WHERE id=:id AND user_id=:uid LIMIT 1");
    $st->execute([':id' => (int)$_GET['id'], ':uid' => auth()['id']]);
    $orderDetail = $st->fetch();
    if ($orderDetail) {
        $st2 = db()->prepare("SELECT * FROM order_items WHERE order_id=:id");
        $st2->execute([':id' => $orderDetail['id']]);
        $orderItems = $st2->fetchAll();
    }
}

include __DIR__ . '/templates/header.php';
?>

<div class="account-wrap">
  <aside class="account-nav">
    <strong style="display:block;padding:10px 14px;color:var(--text2);font-size:12px;text-transform:uppercase;letter-spacing:1px">Кабинет</strong>
    <a href="<?= SITE_URL ?>/?page=account&tab=profile" class="<?= $tab==='profile'?'active':'' ?>">👤 Профиль</a>
    <a href="<?= SITE_URL ?>/?page=account&tab=orders" class="<?= $tab==='orders'?'active':'' ?>">📦 Мои заказы</a>
    <a href="<?= SITE_URL ?>/?page=account&tab=wishlist" class="<?= $tab==='wishlist'?'active':'' ?>">♥ Избранное</a>
    <a href="<?= SITE_URL ?>/?page=logout" style="color:var(--red);margin-top:16px">← Выйти</a>
  </aside>

  <div>
    <?php if ($tab === 'profile'): ?>
      <div class="form-box">
        <h2>Профиль</h2>
        <form method="post" action="<?= SITE_URL ?>/?page=account">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_profile">
          <div class="form-row">
            <div class="form-group">
              <label>Имя</label>
              <input type="text" name="name" value="<?= e($userRow['name']) ?>" required minlength="2">
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" value="<?= e($userRow['email']) ?>" disabled style="opacity:.5">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Телефон</label>
              <input type="tel" name="phone" value="<?= e($userRow['phone'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Адрес доставки</label>
            <textarea name="address" rows="3"><?= e($userRow['address'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
      </div>

    <?php elseif ($tab === 'orders'): ?>
      <h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:20px">Мои заказы</h2>
      <?php if (!empty($orders)): ?>
        <div style="background:var(--dark2);border:1px solid var(--border);border-radius:6px;overflow:hidden">
          <table class="data-table">
            <thead><tr><th>ID</th><th>Дата</th><th>Статус</th><th>Сумма</th><th>Позиций</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
              <tr>
                <td>#<?= (int)$o['id'] ?></td>
                <td><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
                <td style="color:var(--gold);font-weight:700"><?= fmt_price((float)$o['total']) ?></td>
                <td><?= (int)$o['item_count'] ?></td>
                <td><a href="<?= SITE_URL ?>/?page=account&tab=order&id=<?= (int)$o['id'] ?>" class="btn btn-outline btn-sm">Детали</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state"><div class="empty-icon">📦</div><h3>Заказов пока нет</h3><p>Сделайте первый заказ</p><a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-primary" style="margin-top:12px">В каталог</a></div>
      <?php endif; ?>

    <?php elseif ($tab === 'order' && !empty($orderDetail)): ?>
      <div style="margin-bottom:16px"><a href="<?= SITE_URL ?>/?page=account&tab=orders" style="color:var(--text2)">← Назад к заказам</a></div>
      <div class="order-detail">
        <h3>Заказ #<?= (int)$orderDetail['id'] ?></h3>
        <p style="color:var(--text2);margin-bottom:16px"><?= date('d.m.Y H:i', strtotime($orderDetail['created_at'])) ?></p>
        <div style="margin-bottom:16px">
          <span class="status-badge status-<?= e($orderDetail['status']) ?>"><?= e($orderDetail['status']) ?></span>
        </div>
        <table class="data-table" style="margin-bottom:16px">
          <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Сумма</th></tr></thead>
          <tbody>
            <?php foreach ($orderItems as $oi): ?>
            <tr>
              <td><?= e($oi['name']) ?></td>
              <td><?= fmt_price((float)$oi['price']) ?></td>
              <td><?= (int)$oi['qty'] ?></td>
              <td><?= fmt_price((float)$oi['price'] * $oi['qty']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div style="text-align:right;font-size:20px;font-weight:700;color:var(--gold)">Итого: <?= fmt_price((float)$orderDetail['total']) ?></div>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);color:var(--text2)">
          <p>Имя: <?= e($orderDetail['name']) ?></p>
          <p>Телефон: <?= e($orderDetail['phone']) ?></p>
          <p>Адрес: <?= e($orderDetail['address']) ?></p>
          <?php if ($orderDetail['comment']): ?><p>Комментарий: <?= e($orderDetail['comment']) ?></p><?php endif; ?>
        </div>
      </div>

    <?php elseif ($tab === 'wishlist'): ?>
      <h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:20px">Избранное</h2>
      <?php if (!empty($wishItems)): ?>
        <div class="products-grid">
          <?php foreach ($wishItems as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state"><div class="empty-icon">♥</div><h3>Список пуст</h3><p>Добавляйте товары в избранное</p></div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
