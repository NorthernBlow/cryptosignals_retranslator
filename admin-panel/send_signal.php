<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Админ панель</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <?php
    require 'Db.php'; $db = new Database();
    $sandbox = $db->getSandboxByID($_GET['sandbox_id']);

    if (isset($_POST['LOGIN']) && isset($_POST['PASSWORD']) && !isset($_SESSION['AUTH'])) {
      if (!$db->Auth($_POST['LOGIN'], $_POST['PASSWORD'])) {
        exit('<div class="alert alert-danger m-5">Ошибка авторизации! <a href="/">Попробую ещё раз</a></div>');
      } else {
        $_SESSION['AUTH'] = true;
      }
    } else if (!isset($_SESSION['AUTH'])) {
      exit('<div class="alert alert-danger m-5">Ошибка авторизации! <a href="/">Попробую ещё раз</a></div>');
    }
  ?>

  <!-- Главное меню (navbar) -->
  <ul class="nav nav-tabs pt-3 nav-fill" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link" href="/dashboard.php?sandbox">← Назад</a>
    </li>
  </ul>

  <h1 class="text-center">Отправить или удалить сигнал</h1>


  <div class="p-1">
    <p class="lead"><?=$sandbox['post']?></p>
    <p class="badge bg-danger"><?=$sandbox['reason']?></p>
    <p class="badge bg-dark"><?=$sandbox['src']?></p>
  </div>

  <form action="/dashboard.php" method="POST" class="pt-5 px-5">
    <fieldset>
      <input name="sandbox_id" value="<?=$_GET['sandbox_id']?>" type="hidden">
      <div class="mb-3">
        <label for="disabledSelect" class="form-label">Для какого тикера сигнал:</label>
        <select class="form-select" name="send_signal_ticker">
          <?php
            $tickers = $db->getTickers();
            if ($tickers) {
              foreach ($tickers as $ticker) {
                echo "<option value='$ticker[ticker]'>$ticker[ticker]</option>";
              }
            }
          ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="disabledSelect" class="form-label">Характер сигнала:</label>
        <select class="form-select" name="send_signal">
          <option class="bg-success text-white" value="повышение">↑ Повышение</option>
          <option class="bg-danger text-white" value="понижение">↓ Понижение</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Отправить сигнал</button>
      <a href="dashboard.php?sandbox&delete_sandbox=<?=$_GET['sandbox_id']?>" class="btn btn-danger">Удалить пост</a>
    </fieldset>
  </form>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>