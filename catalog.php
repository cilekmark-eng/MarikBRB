<?php
$page_title = 'Каталог — ' . SITE_NAME;

$cats = get_categories();
$tags = get_tags();

// Build opts from GET
$opts = [
    'page'          => max(1, (int)($_GET['p'] ?? 1)),
    'per'           => in_array((int)($_GET['per'] ?? 12), [12,24,36]) ? (int)($_GET['per'] ?? 12) : 12,
    'sort'          => $_GET['sort'] ?? '',
    'search'        => trim($_GET['search'] ?? ''),
    'in_stock'      => !empty($_GET['in_stock']),
    'discount_only' => !empty($_GET['discount']),
];

// Category by slug
$catSlug = $_GET['category'] ?? '';
if ($catSlug) {
    $stCat = db()->prepare("SELECT id,name FROM categories WHERE slug=:s LIMIT 1");
    $stCat->execute([':s' => $catSlug]);
    $activeCat = $stCat->fetch();
    if ($activeCat) {
        $opts['category_id'] = $activeCat['id'];
        $page_title = e($activeCat['name']) . ' — ' . SITE_NAME;
    }
}

if (!empty($_GET['tag'])) {
    $stTag = db()->prepare("SELECT id FROM tags WHERE slug=:s LIMIT 1");
    $stTag->execute([':s' => $_GET['tag']]);
    $tagRow = $stTag->fetch();
    if ($tagRow) $opts['tag_id'] = $tagRow['id'];
}
if ($_GET['price_min'] ?? '' !== '') $opts['price_min'] = (float)$_GET['price_min'];
if ($_GET['price_max'] ?? '' !== '') $opts['price_max'] = (float)$_GET['price_max'];

$result = get_products($opts);
$rows   = $result['rows'];
$pag    = $result['pag'];

include __DIR__ . '/templates/header.php';

// Build query string helper
function buildUrl(array $extra = [], array $remove = []): string {
    $params = $_GET;
    foreach ($remove as $k) unset($params[$k]);
    foreach ($extra as $k => $v) $params[$k] = $v;
    unset($params['p']); // always reset page
    return SITE_URL . '/?' . http_build_query($params);
}
function buildPagUrl(int $pg): string {
    $params = $_GET; $params['p'] = $pg;
    return SITE_URL . '/?' . http_build_query($params);
}
?>

<div class="breadcrumb">
  <a href="<?= SITE_URL ?>/">Главная</a> / <span>Каталог</span>
  <?php if (!empty($activeCat)): ?> / <span><?= e($activeCat['name']) ?></span><?php endif; ?>
</div>

<div class="catalog-wrap">

  <!-- Filters sidebar -->
  <aside>
    <form class="filters" method="get" action="<?= SITE_URL ?>/">
      <input type="hidden" name="page" value="catalog">

      <div class="filter-group">
        <h4>Категории</h4>
        <label><input type="radio" name="category" value="" <?= empty($catSlug) ? 'checked' : '' ?>> Все</label>
        <?php foreach ($cats as $c): ?>
          <label>
            <input type="radio" name="category" value="<?= e($c['slug']) ?>" <?= $catSlug === $c['slug'] ? 'checked' : '' ?>>
            <?= e($c['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <div class="filter-group">
        <h4>Цена, ₽</h4>
        <div class="price-range">
          <input type="number" name="price_min" placeholder="от" value="<?= e($_GET['price_min'] ?? '') ?>" min="0">
          <span>—</span>
          <input type="number" name="price_max" placeholder="до" value="<?= e($_GET['price_max'] ?? '') ?>" min="0">
        </div>
      </div>

      <div class="filter-group">
        <h4>Наличие</h4>
        <label><input type="checkbox" name="in_stock" value="1" <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>> Только в наличии</label>
        <label><input type="checkbox" name="discount" value="1" <?= !empty($_GET['discount']) ? 'checked' : '' ?>> Со скидкой</label>
      </div>

      <div class="filter-group">
        <h4>Теги</h4>
        <?php foreach ($tags as $t): ?>
          <label>
            <input type="radio" name="tag" value="<?= e($t['slug']) ?>" <?= ($_GET['tag'] ?? '') === $t['slug'] ? 'checked' : '' ?>>
            <?= e($t['name']) ?>
          </label>
        <?php endforeach; ?>
        <label><input type="radio" name="tag" value=""> Все теги</label>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%">Применить</button>
      <a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-outline" style="width:100%;margin-top:8px;text-align:center">Сбросить</a>
    </form>
  </aside>

  <!-- Main -->
  <div>
    <!-- Sort bar -->
    <div class="sort-bar">
      <span>Найдено: <strong><?= $pag['total'] ?></strong> товаров</span>
      <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <select onchange="location.href='<?= buildUrl() ?>&sort='+this.value" style="padding:8px 12px;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:4px;color:#fff">
          <option value=""        <?= empty($_GET['sort']) ? 'selected' : '' ?>>Новинки</option>
          <option value="popular" <?= ($_GET['sort'] ?? '') === 'popular'    ? 'selected' : '' ?>>Популярные</option>
          <option value="price_asc"  <?= ($_GET['sort'] ?? '') === 'price_asc'  ? 'selected' : '' ?>>Цена ↑</option>
          <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Цена ↓</option>
        </select>
        <div class="per-page">
          <?php foreach ([12,24,36] as $pp): ?>
            <a href="<?= buildUrl(['per' => $pp]) ?>" class="<?= $opts['per'] === $pp ? 'active' : '' ?>"><?= $pp ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Products -->
    <?php if ($rows): ?>
      <div class="products-grid">
        <?php foreach ($rows as $p): include __DIR__ . '/partials/product_card.php'; endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Товары не найдены</h3>
        <p>Попробуйте изменить параметры фильтрации</p>
        <a href="<?= SITE_URL ?>/?page=catalog" class="btn btn-outline" style="margin-top:16px">Сбросить фильтры</a>
      </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($pag['pages'] > 1): ?>
    <div class="pagination">
      <?php if ($pag['page'] > 1): ?>
        <a href="<?= buildPagUrl($pag['page']-1) ?>">«</a>
      <?php endif; ?>
      <?php for ($i = max(1,$pag['page']-3); $i <= min($pag['pages'],$pag['page']+3); $i++): ?>
        <?php if ($i === $pag['page']): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="<?= buildPagUrl($i) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($pag['page'] < $pag['pages']): ?>
        <a href="<?= buildPagUrl($pag['page']+1) ?>">»</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
