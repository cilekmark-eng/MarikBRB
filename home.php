<?php
$page_title = 'BarberStore — Профессиональный барберский инвентарь';
include __DIR__ . '/templates/header.php';

$cats = get_categories();

// Новинки
$newResult = get_products(['per' => 4, 'sort' => 'newest']);
$newRows   = $newResult['rows'];

// Скидки
$saleResult = get_products(['per' => 4, 'discount_only' => true]);
$saleRows   = $saleResult['rows'];

$catIcons = ['✂','🪒','💈','🔧','🧴','🧴','🔩'];
?>

<!-- HERO -->
<div class="hero">
  <div class="hero-tag">Профессиональный инвентарь</div>
  <h1>Инструменты<br>настоящего <em>Барбера</em></h1>
  <p>Всё необходимое для вашего барбершопа: машинки, триммеры, ножницы, средства для укладки и ухода за бородой.</p>
  <div class="hero-btns">
    <a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-primary">Смотреть каталог</a>
    <a href="<?= SITE_URL ?>/?page=catalog&discount=1" class="btn btn-outline">🔥 Акции</a>
  </div>
</div>

<!-- CATEGORIES -->
<div class="section-head">
  <h2>Категории</h2>
  <a href="<?= SITE_URL ?>/?page=catalog">Все товары →</a>
</div>
<div class="cat-grid" style="margin-bottom:48px">
  <?php foreach ($cats as $i => $c): ?>
  <a href="<?= SITE_URL ?>/?page=catalog&category=<?= e($c['slug']) ?>" class="cat-card">
    <div class="cat-card-icon"><?= $catIcons[$i] ?? '🔧' ?></div>
    <h3><?= e($c['name']) ?></h3>
  </a>
  <?php endforeach; ?>
</div>

<!-- НОВИНКИ -->
<?php if ($newRows): ?>
<div class="section-head">
  <h2>Новинки</h2>
  <a href="<?= SITE_URL ?>/?page=catalog&sort=newest">Все новинки →</a>
</div>
<div class="products-grid" style="margin-bottom:48px">
  <?php foreach ($newRows as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
</div>
<?php endif; ?>

<!-- СКИДКИ -->
<?php if ($saleRows): ?>
<div class="section-head">
  <h2>🔥 Скидки</h2>
  <a href="<?= SITE_URL ?>/?page=catalog&discount=1">Все акции →</a>
</div>
<div class="products-grid" style="margin-bottom:48px">
  <?php foreach ($saleRows as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/templates/footer.php'; ?>
