<?php
$slug = $_GET['slug'] ?? '';
$prod = get_product_by_slug($slug);
if (!$prod) { include __DIR__ . '/404.php'; exit; }

// Increment views
db()->prepare("UPDATE products SET views=views+1 WHERE id=:id")->execute([':id' => $prod['id']]);

$images  = get_product_images((int)$prod['id']);
$tags    = get_product_tags((int)$prod['id']);
$tagIds  = array_column($tags, 'id');
$related = get_related_products((int)$prod['id'], $tagIds);
$reviews = get_reviews((int)$prod['id']);

// Rating avg
$stRat = db()->prepare("SELECT AVG(rating) as avg, COUNT(*) as cnt FROM reviews WHERE product_id=:p AND status='approved'");
$stRat->execute([':p' => $prod['id']]);
$ratingData = $stRat->fetch();

$wishIds = auth() ? wishlist_ids((int)auth()['id']) : [];
$inWish  = in_array((int)$prod['id'], $wishIds, true);

$page_title = e($prod['name']) . ' — ' . SITE_NAME;
include __DIR__ . '/templates/header.php';
?>

<div class="breadcrumb">
  <a href="<?= SITE_URL ?>/">Главная</a> /
  <a href="<?= SITE_URL ?>/?page=catalog">Каталог</a>
  <?php if ($prod['cat_name']): ?> /
    <a href="<?= SITE_URL ?>/?page=catalog&category=<?= e($prod['cat_slug']) ?>"><?= e($prod['cat_name']) ?></a>
  <?php endif; ?> /
  <span><?= e($prod['name']) ?></span>
</div>

<div class="product-single">
  <!-- Image slider -->
  <div class="product-slider">
    <div class="slider-main">
      <?php if ($images): ?>
        <?php foreach ($images as $idx => $img): ?>
          <img class="slider-img <?= $idx === 0 ? 'active' : '' ?>"
               src="<?= e(product_image_url((int)$prod['id'], $img['filename'])) ?>"
               alt="<?= e($prod['name']) ?>">
        <?php endforeach; ?>
      <?php else: ?>
        <div class="slider-no-img">✂</div>
      <?php endif; ?>
    </div>
    <?php if (count($images) > 1): ?>
      <div class="slider-thumbs">
        <?php foreach ($images as $idx => $img): ?>
          <div class="slider-thumb <?= $idx === 0 ? 'active' : '' ?>">
            <img src="<?= e(product_image_url((int)$prod['id'], $img['filename'])) ?>" alt="">
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Product info -->
  <div class="product-info">
    <?php if (!empty($prod['cat_name'])): ?>
      <a href="<?= SITE_URL ?>/?page=catalog&category=<?= e($prod['cat_slug']) ?>" style="color:var(--gold);font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px">
        <?= e($prod['cat_name']) ?>
      </a>
    <?php endif; ?>
    <h1><?= e($prod['name']) ?></h1>

    <?php if ((int)$ratingData['cnt'] > 0): ?>
      <div class="product-rating">
        <?= str_repeat('★', (int)round((float)$ratingData['avg'])) ?>
        <?= str_repeat('☆', 5 - (int)round((float)$ratingData['avg'])) ?>
        <span style="font-size:13px;color:var(--text2);margin-left:6px"><?= round((float)$ratingData['avg'], 1) ?> (<?= (int)$ratingData['cnt'] ?> отзывов)</span>
      </div>
    <?php endif; ?>

    <div class="product-price-block">
      <span class="price-final"><?= fmt_price((float)$prod['final_price']) ?></span>
      <?php if ((int)$prod['discount'] > 0): ?>
        <span class="price-old"><?= fmt_price((float)$prod['price']) ?></span>
        <span class="discount-badge">-<?= (int)$prod['discount'] ?>%</span>
      <?php endif; ?>
    </div>

    <div class="product-meta">
      <p>Артикул: <strong>#<?= (int)$prod['id'] ?></strong></p>
      <p>Наличие:
        <?php if ((int)$prod['stock'] > 5): ?>
          <strong class="stock-ok">В наличии (<?= (int)$prod['stock'] ?> шт)</strong>
        <?php elseif ((int)$prod['stock'] > 0): ?>
          <strong class="stock-low">Мало (<?= (int)$prod['stock'] ?> шт)</strong>
        <?php else: ?>
          <strong class="stock-none">Нет в наличии</strong>
        <?php endif; ?>
      </p>
    </div>

    <?php if ($tags): ?>
      <div class="product-tags">
        <?php foreach ($tags as $t): ?>
          <a href="<?= SITE_URL ?>/?page=catalog&tag=<?= e($t['slug']) ?>" class="tag"><?= e($t['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($prod['description']): ?>
      <p class="product-desc"><?= nl2br(e($prod['description'])) ?></p>
    <?php endif; ?>

    <div class="product-actions">
      <?php if ((int)$prod['stock'] > 0): ?>
        <form method="post" action="<?= SITE_URL ?>/?page=cart" style="display:flex;gap:12px;flex:1">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int)$prod['id'] ?>">
          <div class="qty-wrap">
            <button type="button" class="qty-btn" data-dir="-">−</button>
            <input type="number" name="qty" value="1" min="1" max="<?= (int)$prod['stock'] ?>">
            <button type="button" class="qty-btn" data-dir="+">+</button>
          </div>
          <button type="submit" class="btn btn-primary" style="flex:1">🛒 В корзину</button>
        </form>
      <?php else: ?>
        <button class="btn btn-outline" disabled style="flex:1">Нет в наличии</button>
      <?php endif; ?>

      <?php if (auth()): ?>
        <form method="post" action="<?= SITE_URL ?>/?page=wishlist_toggle">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int)$prod['id'] ?>">
          <button type="submit" class="btn btn-outline <?= $inWish ? 'active' : '' ?>" style="font-size:20px;padding:12px 16px">
            <?= $inWish ? '♥' : '♡' ?>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Related products -->
<?php if ($related): ?>
  <div style="margin-top:48px">
    <div class="section-head"><h2>Похожие товары</h2></div>
    <div class="products-grid">
      <?php foreach ($related as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Reviews -->
<div class="reviews-section">
  <div class="section-head"><h2>Отзывы</h2></div>

  <?php if ($reviews): ?>
    <?php foreach ($reviews as $r): ?>
      <div class="review-card">
        <div class="review-top">
          <div class="stars"><?= str_repeat('★', (int)$r['rating']) ?><?= str_repeat('☆', 5-(int)$r['rating']) ?></div>
          <strong class="review-author"><?= e($r['user_name'] ?? 'Аноним') ?></strong>
          <span class="review-date"><?= date('d.m.Y', strtotime($r['created_at'])) ?></span>
        </div>
        <?php if ($r['text']): ?><p><?= e($r['text']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="color:var(--text2)">Отзывов пока нет. Будьте первым!</p>
  <?php endif; ?>

  <?php if (auth()): ?>
    <div style="margin-top:24px;background:var(--dark2);border:1px solid var(--border);border-radius:6px;padding:24px">
      <h3 style="font-family:var(--font-head);color:var(--white);margin-bottom:16px">Оставить отзыв</h3>
      <form method="post" action="<?= SITE_URL ?>/?page=product&slug=<?= e($prod['slug']) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="review">
        <input type="hidden" name="product_id" value="<?= (int)$prod['id'] ?>">
        <input type="hidden" name="slug" value="<?= e($prod['slug']) ?>">
        <div class="form-group">
          <label>Оценка</label>
          <div class="star-rating">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
              <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-group">
          <label>Ваш отзыв</label>
          <textarea name="text" rows="4" placeholder="Поделитесь впечатлениями о товаре…"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Отправить отзыв</button>
      </form>
    </div>
  <?php else: ?>
    <p style="margin-top:16px;color:var(--text2)"><a href="<?= SITE_URL ?>/?page=login">Войдите</a>, чтобы оставить отзыв.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
