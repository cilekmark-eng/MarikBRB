<?php
if (auth()) redirect(SITE_URL . '/');
$page_title = 'Вход — ' . SITE_NAME;
include __DIR__ . '/templates/header.php';
?>
<div style="max-width:440px;margin:0 auto">
  <div class="form-box">
    <h2>Вход в аккаунт</h2>

    <!-- Google OAuth button -->
    <a href="<?= SITE_URL ?>/auth/google_login.php" style="
      display:flex;align-items:center;justify-content:center;gap:12px;
      padding:12px 20px;background:#fff;border:1px solid #ddd;border-radius:4px;
      color:#333;font-weight:600;font-size:14px;margin-bottom:20px;
      transition:box-shadow .2s;text-decoration:none;
    " onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.15)'"
       onmouseout="this.style.boxShadow='none'">
      <svg width="20" height="20" viewBox="0 0 48 48">
        <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.2l6.8-6.8C35.8 2.4 30.2 0 24 0 14.6 0 6.6 5.4 2.6 13.3l7.9 6.1C12.3 13.4 17.7 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.6 5.9c4.4-4.1 7-10.1 7-17.1z"/>
        <path fill="#FBBC05" d="M10.5 28.6A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.1.7-4.6l-7.9-6.1A23.9 23.9 0 0 0 0 24c0 3.9.9 7.5 2.6 10.7l7.9-6.1z"/>
        <path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.6-5.9c-2 1.4-4.6 2.2-7.6 2.2-6.3 0-11.7-4-13.5-9.5l-7.9 6.1C6.6 42.6 14.6 48 24 48z"/>
      </svg>
      Войти через Google
    </a>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
      <div style="flex:1;height:1px;background:var(--border)"></div>
      <span style="color:var(--text2);font-size:13px">или</span>
      <div style="flex:1;height:1px;background:var(--border)"></div>
    </div>

    <form method="post" action="<?= SITE_URL ?>/?page=login<?= isset($_GET['redirect']) ? '&redirect='.urlencode($_GET['redirect']) : '' ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required autofocus>
      </div>
      <div class="form-group">
        <label>Пароль</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Войти</button>
    </form>
    <p style="margin-top:16px;text-align:center;color:var(--text2)">
      Нет аккаунта? <a href="<?= SITE_URL ?>/?page=register">Зарегистрироваться</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
