<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Админ панель</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

	<?php require 'Db.php'; $db = new Database(); ?>
	<?php

		if (isset($_POST)) {
			if (isset($_POST['username']) && $_POST['token']) {
				$db->addUser($_POST['username'], $_POST['token']);
			} else if (isset($_POST['url'])) {
				$db->addPage($_POST['url']);
			} else if (isset($_POST['chan'])) {
				$db->addChannel($_POST['chan']);
			} else if (isset($_POST['ticker'])) {
				$db->addTicker($_POST['ticker']);
			} else if (isset($_POST['stopword'])) {
				$db->addStopWord($_POST['stopword']);
			} else if (isset($_POST['word_for_up'])) {
				$db->addWordUp($_POST['word_for_up']);
			} else if (isset($_POST['word_for_down'])) {
				$db->addWordDown($_POST['word_for_down']);
			}
		}
	?>

	<!-- Главное меню (navbar) -->
  <ul class="nav nav-tabs pt-3 justify-content-center" id="myTab" role="tablist">
	  <li class="nav-item" role="presentation">
	    <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-tab-pane" type="button" role="tab" aria-controls="users-tab-pane" aria-selected="true">Пользователи</button>
	  </li>
	  <li class="nav-item" role="presentation">
	    <button class="nav-link" id="sources-tab" data-bs-toggle="tab" data-bs-target="#sources-tab-pane" type="button" role="tab" aria-controls="sources-tab-pane" aria-selected="false">Источники</button>
	  </li>
	  <li class="nav-item" role="presentation">
	    <button class="nav-link" id="words-tab" data-bs-toggle="tab" data-bs-target="#words-tab-pane" type="button" role="tab" aria-controls="words-tab-pane" aria-selected="false">Настройки</button>
	  </li>
	</ul>



	<!-- Страница -->
	<div class="tab-content" id="myTabContent">

		<!-- Пользователи -->
	  <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
	  	<button type="button" class="btn btn-primary m-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
			  Добавить пользователя
			</button>
	  	<table class="table">
		    <thead>
		      <tr>
		        <th scope="col">#</th>
		        <th scope="col">Имя</th>
		        <th scope="col">Промо-код</th>
		        <th scope="col">Дата приглашения</th>
		      </tr>
		    </thead>
		    <tbody>
		    	<?php
		    		$users = $db->getUsers();
		    		if ($users) {
    					foreach ($users as $user) {
    						echo '<tr>';
    						echo "<th>$user[id]</th>";
    						echo "<td>$user[username]</td>";
    						echo "<td>$user[token]</td>";
    						echo "<td>$user[date]</td>";
    						echo '</tr>';
    					}
    				}
    			?>
		    </tbody>
		  </table>
	  </div>

	  <!-- Источники -->
	  <div class="tab-pane fade" id="sources-tab-pane" role="tabpanel" aria-labelledby="sources-tab" tabindex="0">

	  	<!-- Вложенное меню -->
	  	<ul class="nav nav-tabs pt-3 justify-content-center" id="myTab2" role="tablist">
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages-tab-pane" type="button" role="tab" aria-controls="pages-tab-pane" aria-selected="false">Страницы</button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="channels-tab" data-bs-toggle="tab" data-bs-target="#channels-tab-pane" type="button" role="tab" aria-controls="channels-tab-pane" aria-selected="false">Каналы</button>
			  </li>
			</ul>

			<!-- Страницы -->
			<div class="tab-content" id="myTab2Content">

				<!-- Страницы -->
				<div class="tab-pane fade" id="pages-tab-pane" role="tabpanel" aria-labelledby="pages-tab" tabindex="0">
			  	<button type="button" class="btn btn-warning m-3" data-bs-toggle="modal" data-bs-target="#addPageModal">
					  Добавить страницу
					</button>
			  	<table class="table">
				    <thead>
				      <tr>
				        <th scope="col">#</th>
				        <th scope="col">URL</th>
				      </tr>
				    </thead>
				    <tbody>
				    	<?php
				    		$pages = $db->getPages();
				    		if ($pages) {
		    					foreach ($pages as $page) {
		    						echo '<tr>';
		    						echo "<th>$page[id]</th>";
		    						echo "<td>$page[url]</td>";
		    						echo '</tr>';
		    					}
		    				}
		    			?>
				    </tbody>
				  </table>
			  </div>

			  <!-- Каналы -->
			  <div class="tab-pane fade" id="channels-tab-pane" role="tabpanel" aria-labelledby="channels-tab" tabindex="0">
			  	<button type="button" class="btn btn-info m-3" data-bs-toggle="modal" data-bs-target="#addChannelModal">
					  Добавить канал
					</button>
			  	<table class="table">
				    <thead>
				      <tr>
				        <th scope="col">#</th>
				        <th scope="col">Link</th>
				      </tr>
				    </thead>
				    <tbody>
				    	<?php
				    		$channels = $db->getChannels();
				    		if ($channels) {
		    					foreach ($channels as $channel) {
		    						echo '<tr>';
		    						echo "<th>$channel[id]</th>";
		    						echo "<td>$channel[chan]</td>";
		    						echo '</tr>';
		    					}
		    				}
		    			?>
				    </tbody>
				  </table>
			  </div>
			</div>

		</div>

		<!-- Настройки -->
		<div class="tab-pane fade" id="words-tab-pane" role="tabpanel" aria-labelledby="words-tab" tabindex="0">

			<!-- Вложенное меню -->
	  	<ul class="nav nav-tabs pt-3 justify-content-center" id="myTab3" role="tablist">
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#tickers-tab-pane" type="button" role="tab" aria-controls="pages-tab-pane" aria-selected="false">Тикеры</button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="stopword-tab" data-bs-toggle="tab" data-bs-target="#stopword-tab-pane" type="button" role="tab" aria-controls="stopword-tab-pane" aria-selected="false">Стоп слова</button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="wordsup-tab" data-bs-toggle="tab" data-bs-target="#wordsup-tab-pane" type="button" role="tab" aria-controls="wordsup-tab-pane" aria-selected="false">На повышение</button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="wordsdown-tab" data-bs-toggle="tab" data-bs-target="#wordsdown-tab-pane" type="button" role="tab" aria-controls="wordsdown-tab-pane" aria-selected="false">На понижение</button>
			  </li>
			</ul>

			<!-- Страница -->
			<div class="tab-content" id="myTab3Content">

				<!-- Тикеры -->
				<div class="tab-pane fade" id="tickers-tab-pane" role="tabpanel" aria-labelledby="tickers-tab" tabindex="0">
			  	<button type="button" class="btn btn-success m-3" data-bs-toggle="modal" data-bs-target="#addTickerModal">
					  Добавить тикер
					</button>
			  	<table class="table">
				    <thead>
				      <tr>
				        <th scope="col">#</th>
				        <th scope="col">Тикер</th>
				        <th scope="col">Ключевые слова</th>
				      </tr>
				    </thead>
				    <tbody>
				    	<?php
				    		$tickers = $db->getTickers();
				    		if ($tickers) {
		    					foreach ($tickers as $ticker) {
		    						echo '<tr>';
		    						echo "<th>$ticker[id]</th>";
		    						echo "<td>$ticker[ticker]</td>";
		    						echo "<td>$ticker[keywords]</td>";
		    						echo '</tr>';
		    					}
		    				}
		    			?>
				    </tbody>
				  </table>
			  </div>

			  <!-- Стоп слова -->
			  <div class="tab-pane fade" id="stopword-tab-pane" role="tabpanel" aria-labelledby="stopword-tab" tabindex="0">
			  	<button type="button" class="btn btn-danger m-3" data-bs-toggle="modal" data-bs-target="#addStopWordModal">
					  Добавить стоп слово
					</button>
			  	<table class="table">
				    <thead>
				      <tr>
				        <th scope="col">#</th>
				        <th scope="col">Стоп слово</th>
				      </tr>
				    </thead>
				    <tbody>
				    	<?php
				    		$stopwords = $db->getStopWords();
				    		if ($stopwords) {
		    					foreach ($stopwords as $stopword) {
		    						echo '<tr>';
		    						echo "<th>$stopword[id]</th>";
		    						echo "<td>$stopword[stopword]</td>";
		    						echo '</tr>';
		    					}
		    				}
		    			?>
				    </tbody>
				  </table>
			  </div>

			  <!-- Слова на повышение -->
			  <div class="tab-pane fade" id="wordsup-tab-pane" role="tabpanel" aria-labelledby="wordsup-tab" tabindex="0">
			  	<button type="button" class="btn btn-danger m-3" data-bs-toggle="modal" data-bs-target="#addWordForUpModal">
					  Добавить слово
					</button>
			  	<table class="table">
				    <thead>
				      <tr>
				        <th scope="col">#</th>
				        <th scope="col">Слова</th>
				      </tr>
				    </thead>
				    <tbody>
				    	<?php
				    		$wordsup = $db->getWordsUp();
				    		if ($wordsup) {
		    					foreach ($wordsup as $word_for_up) {
		    						echo '<tr>';
		    						echo "<th>$word_for_up[id]</th>";
		    						echo "<td>$word_for_up[word_for_up]</td>";
		    						echo '</tr>';
		    					}
		    				}
		    			?>
				    </tbody>
				  </table>
			  </div>

			  <!-- Слова на понижение -->
			  <div class="tab-pane fade" id="wordsdown-tab-pane" role="tabpanel" aria-labelledby="wordsdown-tab" tabindex="0">
			  	<button type="button" class="btn btn-danger m-3" data-bs-toggle="modal" data-bs-target="#addWordForDownModal">
					  Добавить слово
					</button>
			  	<table class="table">
				    <thead>
				      <tr>
				        <th scope="col">#</th>
				        <th scope="col">Слова</th>
				      </tr>
				    </thead>
				    <tbody>
				    	<?php
				    		$wordsdown = $db->getWordsDown();
				    		if ($wordsdown) {
		    					foreach ($wordsdown as $word_for_down) {
		    						echo '<tr>';
		    						echo "<th>$word_for_down[id]</th>";
		    						echo "<td>$word_for_down[word_for_down]</td>";
		    						echo '</tr>';
		    					}
		    				}
		    			?>
				    </tbody>
				  </table>
			  </div>
			</div>
		</div>

	</div>




	<!-- Modal -->
	<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header bg-primary text-white">
	        <h1 class="modal-title fs-5" id="exampleModalLabel">Новый пользователь</h1>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form action="/dashboard.php" method="post">
	      	<div class="modal-body">
	        	<input class="form-control mb-2" type="text" placeholder="Имя пользователя" aria-label=".form-control-lg example" name="username">
	        	<input class="form-control" type="text" placeholder="Промо-код" aria-label=".form-control-lg example" name="token">
	      	</div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
		        <button type="sumbit" class="btn btn-primary">Добавить</button>
		      </div>
	      </form>
	    </div>
	  </div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="addPageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header bg-warning">
	        <h1 class="modal-title fs-5" id="exampleModalLabel">Новая страница для парсинга</h1>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form action="/dashboard.php" method="post">
		      <div class="modal-body">
	        	<input class="form-control mb-2" type="text" placeholder="URL" aria-label=".form-control-lg example" name="url">
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
		        <button type="sumbit" class="btn btn-warning">Добавить</button>
		      </div>
	      </form>
	    </div>
	  </div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="addChannelModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header bg-info text-white">
	        <h1 class="modal-title fs-5" id="exampleModalLabel">Новый канал для парсинга</h1>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form action="/dashboard.php" method="post">
	      	<div class="modal-body">
	        	<input class="form-control mb-2" type="text" placeholder="Chan" aria-label=".form-control-lg example" name="chan">
	      	</div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
		        <button type="sumbit" class="btn btn-success">Добавить</button>
		      </div>
	      </form>
	    </div>
	  </div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="addTickerModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header bg-success text-white">
	        <h1 class="modal-title fs-5" id="exampleModalLabel">Новый тикер для парсинга</h1>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form action="/dashboard.php" method="post">
	      	<div class="modal-body">
	        	<input class="form-control mb-2" type="text" placeholder="Тикер" aria-label=".form-control-lg example" name="ticker">
	      	</div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
		        <button type="sumbit" class="btn btn-success">Добавить</button>
		      </div>
	      </form>
	    </div>
	  </div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="addStopWordModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header bg-danger text-white">
	        <h1 class="modal-title fs-5" id="exampleModalLabel">Новое стоп-слово</h1>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form action="/dashboard.php" method="post">
	      	<div class="modal-body">
	        	<input class="form-control mb-2" type="text" placeholder="Стоп слово" aria-label=".form-control-lg example" name="stopword">
	      	</div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
		        <button type="sumbit" class="btn btn-success">Добавить</button>
		      </div>
	      </form>
	    </div>
	  </div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="addWordForUpModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header bg-danger text-white">
	        <h1 class="modal-title fs-5" id="exampleModalLabel">Новое слово (повышение)</h1>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form action="/dashboard.php" method="post">
	      	<div class="modal-body">
	        	<input class="form-control mb-2" type="text" placeholder="Слово или фраза" aria-label=".form-control-lg example" name="word_for_up">
	      	</div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
		        <button type="sumbit" class="btn btn-success">Добавить</button>
		      </div>
	      </form>
	    </div>
	  </div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="addWordForDownModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header bg-danger text-white">
	        <h1 class="modal-title fs-5" id="exampleModalLabel">Новое слово (понижение)</h1>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form action="/dashboard.php" method="post">
	      	<div class="modal-body">
	        	<input class="form-control mb-2" type="text" placeholder="Слово или фраза" aria-label=".form-control-lg example" name="word_for_down">
	      	</div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
		        <button type="sumbit" class="btn btn-success">Добавить</button>
		      </div>
	      </form>
	    </div>
	  </div>
	</div>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>