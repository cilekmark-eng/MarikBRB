<?php
if (auth()) redirect(SITE_URL . '/');
$page_title = 'Регистрация — ' . SITE_NAME;
include __DIR__ . '/templates/header.php';
?>
<div style="max-width:500px;margin:0 auto">
  <div class="form-box">
    <h2>Создать аккаунт</h2>
    <form method="post" action="<?= SITE_URL ?>/?page=register">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Имя *</label>
        <input type="text" name="name" required minlength="2" value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Пароль * (мин. 8 симв.)</label>
          <input type="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
          <label>Повторите пароль *</label>
          <input type="password" name="confirm" required minlength="8">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Зарегистрироваться</button>
    </form>
    <p style="margin-top:16px;text-align:center;color:var(--text2)">
      Уже есть аккаунт? <a href="<?= SITE_URL ?>/?page=login">Войти</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
