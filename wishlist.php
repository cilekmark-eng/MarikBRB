<?php
declare(strict_types=1);
require_auth();
$userId = (int)auth()['id'];

$st = db()->prepare(
    "SELECT p.*, c.name AS cat_name,
            ROUND(p.price*(1-p.discount/100),2) AS final_price,
            (SELECT filename FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS main_image,
            w.added_at
     FROM wishlist w
     JOIN products p ON p.id=w.product_id
     LEFT JOIN categories c ON c.id=p.category_id
     WHERE w.user_id=:uid AND p.is_active=1
     ORDER BY w.added_at DESC"
);
$st->execute([':uid'=>$userId]);
$items = $st->fetchAll();

include __DIR__ . '/templates/header.php';
?>
<div class="container site-main">
  <h1 style="font-family:var(--font-head);color:var(--white);margin-bottom:24px">Избранное</h1>

  <?php if (!$items): ?>
    <div class="empty-state">
      <div class="empty-icon">♡</div>
      <h3>Список избранного пуст</h3>
      <p>Добавляйте товары в избранное, чтобы не потерять их.</p>
      <a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-primary" style="margin-top:20px">В каталог</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($items as $p): ?>
        <?php include __DIR__ . '/partials/product_card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
