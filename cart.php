<?php
$page_title = 'Корзина — ' . SITE_NAME;
$cartId = get_cart_id();
$items  = get_cart_items($cartId);
$total  = array_reduce($items, fn($c,$i) => $c + $i['final_price'] * $i['qty'], 0);
include __DIR__ . '/templates/header.php';
?>

<h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:24px">Корзина</h2>

<?php if ($items): ?>
  <form method="post" action="<?= SITE_URL ?>/?page=cart">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">
    <table class="cart-table">
      <thead>
        <tr>
          <th>Товар</th>
          <th>Цена</th>
          <th>Кол-во</th>
          <th>Итого</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td>
            <div class="cart-product">
              <?php if ($item['main_image']): ?>
                <img src="<?= e(product_image_url((int)$item['product_id'], $item['main_image'])) ?>" alt="">
              <?php endif; ?>
              <div>
                <strong><a href="<?= SITE_URL ?>/?page=product&slug=<?= e($item['slug']) ?>" style="color:var(--white)"><?= e($item['name']) ?></a></strong>
                <?php if ($item['discount'] > 0): ?>
                  <div style="font-size:12px;color:var(--red)">Скидка <?= (int)$item['discount'] ?>%</div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= fmt_price((float)$item['final_price']) ?></td>
          <td>
            <div class="qty-wrap" style="display:inline-flex">
              <button type="button" class="qty-btn" data-dir="-">−</button>
              <input type="number" name="qty_<?= (int)$item['id'] ?>" value="<?= (int)$item['qty'] ?>" min="0" max="<?= (int)$item['stock'] ?>" style="width:50px">
              <button type="button" class="qty-btn" data-dir="+">+</button>
            </div>
          </td>
          <td style="font-weight:700;color:var(--gold)"><?= fmt_price((float)$item['final_price'] * $item['qty']) ?></td>
          <td>
            <form method="post" action="<?= SITE_URL ?>/?page=cart" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">✕</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button type="submit" class="btn btn-outline btn-sm" onclick="
      document.querySelectorAll('input[name^=qty_]').forEach(input => {
        const id = input.name.replace('qty_','');
        document.querySelectorAll('input[name=item_id]').forEach(h => {
          if(h.closest('form') && h.closest('form').querySelector('[name=action]')?.value==='update') {
            document.querySelector('[name=item_id]').value = id;
          }
        });
      })
    ">Обновить корзину</button>
  </form>

  <div class="cart-summary">
    <h3>Итого</h3>
    <?php foreach ($items as $item): ?>
      <div class="summary-row">
        <span><?= e($item['name']) ?> × <?= (int)$item['qty'] ?></span>
        <span><?= fmt_price((float)$item['final_price'] * $item['qty']) ?></span>
      </div>
    <?php endforeach; ?>
    <div class="summary-total"><?= fmt_price($total) ?></div>
    <a href="<?= SITE_URL ?>/?page=checkout" class="btn btn-primary" style="width:100%;text-align:center">Оформить заказ →</a>
    <a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-outline" style="width:100%;text-align:center;margin-top:8px">Продолжить покупки</a>
  </div>

<?php else: ?>
  <div class="empty-state">
    <div class="empty-icon">🛒</div>
    <h3>Корзина пуста</h3>
    <p>Добавьте товары из нашего каталога</p>
    <a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-primary" style="margin-top:16px">Перейти в каталог</a>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/templates/footer.php'; ?>
