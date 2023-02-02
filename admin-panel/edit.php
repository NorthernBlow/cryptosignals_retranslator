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

    if (isset($_POST['LOGIN']) && isset($_POST['PASSWORD']) && !isset($_SESSION['AUTH'])) {
      if (!$db->Auth($_POST['LOGIN'], $_POST['PASSWORD'])) {
        exit('<div class="alert alert-danger m-5">Ошибка авторизации! <a href="/">Попробую ещё раз</a></div>');
      } else {
        $_SESSION['AUTH'] = true;
      }
    } else if (!isset($_SESSION['AUTH'])) {
      exit('<div class="alert alert-danger m-5">Ошибка авторизации! <a href="/">Попробую ещё раз</a></div>');
    }

    if (isset($_GET['page'])) {
      $page = $db->getPageByID($_GET['page']); ?>

      <h1 class="text-center pt-5">Изменить страницу <strong class="text-danger">источник</strong></h1>

      <form action="/dashboard.php?pages" method="post" class="pt-5 px-2">
        <div class="modal-body">
          <input class="form-control mb-2" type="text" placeholder="URL" aria-label=".form-control-lg example" name="url" value="<?=$page['url']?>">
          <input type="hidden" name="source_page_id" value="<?=$page['id']?>">
        </div>
        <div class="modal-footer">
          <a href="/dashboard.php?pages" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</a>
          <button type="sumbit" class="btn btn-success">Обновить</button>
        </div>
      </form>

      <!-- <p class="lead text-center">Не затупи блядь!</p> -->

    <?php
    }




  if (isset($_GET['channel'])) {
      $channel = $db->getChannelByID($_GET['channel']); ?>

      <h1 class="text-center pt-5">Изменить канал <strong class="text-danger">источник</strong></h1>

      <form action="/dashboard.php?channels" method="post" class="pt-5 px-2">
        <div class="modal-body">
          <input class="form-control mb-2" type="text" placeholder="Username" aria-label=".form-control-lg example" name="chan" value="<?=$channel['chan']?>">
          <input type="hidden" name="sources_channel_id" value="<?=$channel['id']?>">
        </div>
        <div class="modal-footer">
          <a href="/dashboard.php?channels" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</a>
          <button type="sumbit" class="btn btn-success">Обновить</button>
        </div>
      </form>

      <!-- <p class="lead text-center">Не затупи блядь!</p> -->

    <?php
    }




    if (isset($_GET['ticker'])) {
      $ticker = $db->getTickerByID($_GET['ticker']); ?>

      <h1 class="text-center pt-5">Изменить <strong class="text-danger">тикер</strong></h1>

      <form action="/dashboard.php?tickers" method="post" class="pt-5 px-2">
        <div class="modal-body">
          <input class="form-control mb-2" type="text" placeholder="Тикер" aria-label=".form-control-lg example" name="ticker" value="<?=$ticker['ticker']?>">
          <input class="form-control mb-2" type="text" placeholder="Ключевые слова" aria-label=".form-control-lg example" name="keywords" value="<?=$ticker['keywords']?>">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="pump" id="flexCheckDefault" value="1" <?=!$ticker['pump'] ? : 'checked'?>>
            <label class="form-check-label" for="flexCheckDefault">
              Возможен PUMP
            </label>
          </div>
          <input type="hidden" name="settings_ticker_id" value="<?=$ticker['id']?>">
        </div>
        <div class="modal-footer">
          <a href="/dashboard.php?tickers" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</a>
          <button type="sumbit" class="btn btn-success">Обновить</button>
        </div>
      </form>

      <!-- <p class="lead text-center">Не затупи блядь!</p> -->

    <?php
    }




    if (isset($_GET['stopword'])) {
      $stopword = $db->getStopWordByID($_GET['stopword']); ?>

      <h1 class="text-center pt-5">Изменить <strong class="text-danger">стоп слово</strong></h1>

      <form action="/dashboard.php?stopwords" method="post" class="pt-5 px-2">
        <div class="modal-body">
          <input class="form-control mb-2" type="text" placeholder="Стоп слово" aria-label=".form-control-lg example" name="stopword" value="<?=$stopword['stopword']?>">
          <input type="hidden" name="settings_stopword_id" value="<?=$stopword['id']?>">
        </div>
        <div class="modal-footer">
          <a href="/dashboard.php?stopwords" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</a>
          <button type="sumbit" class="btn btn-success">Обновить</button>
        </div>
      </form>

      <!-- <p class="lead text-center">Не затупи блядь!</p> -->

    <?php
    }






    if (isset($_GET['wordup'])) {
      $wordup = $db->getWordUpByID($_GET['wordup']); ?>

      <h1 class="text-center pt-5">Изменить <strong class="text-danger">ключ на повышение</strong></h1>

      <form action="/dashboard.php?wordsup" method="post" class="pt-5 px-2">
        <div class="modal-body">
          <input class="form-control mb-2" type="text" placeholder="Ключ на повышение" aria-label=".form-control-lg example" name="word_for_up" value="<?=$wordup['word_for_up']?>">
          <input type="hidden" name="settings_wordup_id" value="<?=$wordup['id']?>">
        </div>
        <div class="modal-footer">
          <a href="/dashboard.php?wordsup" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</a>
          <button type="sumbit" class="btn btn-success">Обновить</button>
        </div>
      </form>

      <!-- <p class="lead text-center">Не затупи блядь!</p> -->

    <?php
    }










    if (isset($_GET['worddown'])) {
      $worddown = $db->getWordDownByID($_GET['worddown']); ?>

      <h1 class="text-center pt-5">Изменить <strong class="text-danger">ключ на понижение</strong></h1>

      <form action="/dashboard.php?wordsdown" method="post" class="pt-5 px-2">
        <div class="modal-body">
          <input class="form-control mb-2" type="text" placeholder="Ключ на повышение" aria-label=".form-control-lg example" name="word_for_down" value="<?=$worddown['word_for_down']?>">
          <input type="hidden" name="settings_worddown_id" value="<?=$worddown['id']?>">
        </div>
        <div class="modal-footer">
          <a href="/dashboard.php?wordsdown" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</a>
          <button type="sumbit" class="btn btn-success">Обновить</button>
        </div>
      </form>

      <!-- <p class="lead text-center">Не затупи блядь!</p> -->

    <?php
    }
  ?>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>