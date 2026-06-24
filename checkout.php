<?php
$page_title = 'Оформление заказа — ' . SITE_NAME;
$cartId = get_cart_id();
$items  = get_cart_items($cartId);
if (empty($items)) { redirect(SITE_URL . '/?page=cart'); }
$total = array_reduce($items, fn($c,$i) => $c + $i['final_price'] * $i['qty'], 0);

$user = auth();
include __DIR__ . '/templates/header.php';
?>

<h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:24px">Оформление заказа</h2>

<div style="display:grid;grid-template-columns:1fr 360px;gap:28px">
  <form method="post" action="<?= SITE_URL ?>/?page=checkout" class="form-box">
    <?= csrf_field() ?>
    <h2>Данные доставки</h2>

    <div class="form-row">
      <div class="form-group">
        <label>Ваше имя *</label>
        <input type="text" name="name" required value="<?= e($user['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required value="<?= e($user['email'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Телефон *</label>
      <input type="tel" name="phone" required placeholder="+7 (___) ___-__-__" value="<?= e($user['phone'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Адрес доставки *</label>
      <textarea name="address" required rows="3"><?= e($user['address'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Комментарий к заказу</label>
      <textarea name="comment" rows="3" placeholder="Уточнения по доставке, время и т.д."></textarea>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%">✓ Подтвердить заказ на <?= fmt_price($total) ?></button>
  </form>

  <!-- Order summary -->
  <div class="cart-summary" style="height:fit-content">
    <h3>Ваш заказ</h3>
    <?php foreach ($items as $item): ?>
      <div class="summary-row">
        <span style="font-size:13px"><?= e($item['name']) ?> <span style="color:var(--text2)">×<?= (int)$item['qty'] ?></span></span>
        <span><?= fmt_price((float)$item['final_price'] * $item['qty']) ?></span>
      </div>
    <?php endforeach; ?>
    <div class="summary-row" style="font-size:18px;font-weight:700;color:var(--gold);border:none;margin-top:8px">
      <span>Итого</span>
      <span><?= fmt_price($total) ?></span>
    </div>
    <a href="<?= SITE_URL ?>/?page=cart" style="font-size:13px;color:var(--text2)">← Изменить корзину</a>
  </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
