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
	  <li class="nav-item" role="presentation">
	    <button class="nav-link" id="sandbox-tab" data-bs-toggle="tab" data-bs-target="#sandbox-tab-pane" type="button" role="tab" aria-controls="sandbox-tab-pane" aria-selected="false">Песочница</button>
	  </li>
	</ul>



	<!-- Страница -->
	<div class="tab-content" id="myTabContent">

		<!-- Пользователи -->
	  <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
	  	<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
			  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
				  <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
				  <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
				</svg>
			  Добавить пользователя
			</button>
	  	<table class="table table-striped table-hover">
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
	  	<ul class="nav nav-pills p-3 nav-fill" id="myTab2" role="tablist">
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages-tab-pane" type="button" role="tab" aria-controls="pages-tab-pane" aria-selected="false">
			    	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark" viewBox="0 0 16 16">
						  <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"/>
						</svg>
						Страницы
					</button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="channels-tab" data-bs-toggle="tab" data-bs-target="#channels-tab-pane" type="button" role="tab" aria-controls="channels-tab-pane" aria-selected="false">
			    	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telegram" viewBox="0 0 16 16">
						  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09z"/>
						</svg>
			    	Каналы
			    </button>
			  </li>
			</ul>

			<!-- Страницы -->
			<div class="tab-content" id="myTab2Content">

				<!-- Страницы -->
				<div class="tab-pane fade" id="pages-tab-pane" role="tabpanel" aria-labelledby="pages-tab" tabindex="0">
			  	<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addPageModal">
			  		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
						  <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
						</svg>
					  Добавить страницу
					</button>
			  	<table class="table table-striped table-hover">
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
			  	<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addChannelModal">
			  		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
						  <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
						</svg>
					  Добавить канал
					</button>
			  	<table class="table table-striped table-hover">
				    <thead>
				      <tr>
				        <th scope="col">#</th>
				        <th scope="col">Username</th>
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
	  	<ul class="nav nav-pills nav-fill p-3" id="myTab3" role="tablist">
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#tickers-tab-pane" type="button" role="tab" aria-controls="pages-tab-pane" aria-selected="false">
			    	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="dark" class="bi bi-currency-bitcoin" viewBox="0 0 16 16">
						  <path d="M5.5 13v1.25c0 .138.112.25.25.25h1a.25.25 0 0 0 .25-.25V13h.5v1.25c0 .138.112.25.25.25h1a.25.25 0 0 0 .25-.25V13h.084c1.992 0 3.416-1.033 3.416-2.82 0-1.502-1.007-2.323-2.186-2.44v-.088c.97-.242 1.683-.974 1.683-2.19C11.997 3.93 10.847 3 9.092 3H9V1.75a.25.25 0 0 0-.25-.25h-1a.25.25 0 0 0-.25.25V3h-.573V1.75a.25.25 0 0 0-.25-.25H5.75a.25.25 0 0 0-.25.25V3l-1.998.011a.25.25 0 0 0-.25.25v.989c0 .137.11.25.248.25l.755-.005a.75.75 0 0 1 .745.75v5.505a.75.75 0 0 1-.75.75l-.748.011a.25.25 0 0 0-.25.25v1c0 .138.112.25.25.25L5.5 13zm1.427-8.513h1.719c.906 0 1.438.498 1.438 1.312 0 .871-.575 1.362-1.877 1.362h-1.28V4.487zm0 4.051h1.84c1.137 0 1.756.58 1.756 1.524 0 .953-.626 1.45-2.158 1.45H6.927V8.539z"/>
						</svg>
			    	Тикеры
			    </button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="stopword-tab" data-bs-toggle="tab" data-bs-target="#stopword-tab-pane" type="button" role="tab" aria-controls="stopword-tab-pane" aria-selected="false">
			    	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="darkred" class="bi bi-sign-stop" viewBox="0 0 16 16">
						  <path d="M3.16 10.08c-.931 0-1.447-.493-1.494-1.132h.653c.065.346.396.583.891.583.524 0 .83-.246.83-.62 0-.303-.203-.467-.637-.572l-.656-.164c-.61-.147-.978-.51-.978-1.078 0-.706.597-1.184 1.444-1.184.853 0 1.386.475 1.436 1.087h-.645c-.064-.32-.352-.542-.797-.542-.472 0-.77.246-.77.6 0 .261.196.437.553.522l.654.161c.673.164 1.06.487 1.06 1.11 0 .736-.574 1.228-1.544 1.228Zm3.427-3.51V10h-.665V6.57H4.753V6h3.006v.568H6.587Z"/>
						  <path fill-rule="evenodd" d="M11.045 7.73v.544c0 1.131-.636 1.805-1.661 1.805-1.026 0-1.664-.674-1.664-1.805V7.73c0-1.136.638-1.807 1.664-1.807 1.025 0 1.66.674 1.66 1.807Zm-.674.547v-.553c0-.827-.422-1.234-.987-1.234-.572 0-.99.407-.99 1.234v.553c0 .83.418 1.237.99 1.237.565 0 .987-.408.987-1.237Zm1.15-2.276h1.535c.82 0 1.316.55 1.316 1.292 0 .747-.501 1.289-1.321 1.289h-.865V10h-.665V6.001Zm1.436 2.036c.463 0 .735-.272.735-.744s-.272-.741-.735-.741h-.774v1.485h.774Z"/>
						  <path fill-rule="evenodd" d="M4.893 0a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146A.5.5 0 0 0 11.107 0H4.893ZM1 5.1 5.1 1h5.8L15 5.1v5.8L10.9 15H5.1L1 10.9V5.1Z"/>
						</svg>
			    	Стоп слова
			    </button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="wordsup-tab" data-bs-toggle="tab" data-bs-target="#wordsup-tab-pane" type="button" role="tab" aria-controls="wordsup-tab-pane" aria-selected="false">
			    	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-arrow-bar-up" viewBox="0 0 16 16">
						  <path fill-rule="evenodd" d="M8 10a.5.5 0 0 0 .5-.5V3.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 3.707V9.5a.5.5 0 0 0 .5.5zm-7 2.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5z"/>
						</svg>
			    	На повышение
			    </button>
			  </li>
			  <li class="nav-item" role="presentation">
			    <button class="nav-link" id="wordsdown-tab" data-bs-toggle="tab" data-bs-target="#wordsdown-tab-pane" type="button" role="tab" aria-controls="wordsdown-tab-pane" aria-selected="false">
			    	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="orange" class="bi bi-arrow-bar-down" viewBox="0 0 16 16">
						  <path fill-rule="evenodd" d="M1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5zM8 6a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 .708-.708L7.5 12.293V6.5A.5.5 0 0 1 8 6z"/>
						</svg>
			    	На понижение
			    </button>
			  </li>
			</ul>

			<!-- Страница -->
			<div class="tab-content" id="myTab3Content">

				<!-- Тикеры -->
				<div class="tab-pane fade" id="tickers-tab-pane" role="tabpanel" aria-labelledby="tickers-tab" tabindex="0">
			  	<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addTickerModal">
			  		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
						  <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
						</svg>
					  Добавить тикер
					</button>
			  	<table class="table table-striped table-hover">
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
			  	<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addStopWordModal">
			  		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
						  <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
						</svg>
					  Добавить стоп слово
					</button>
			  	<table class="table table-striped table-hover">
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
			  	<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addWordForUpModal">
			  		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
						  <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
						</svg>
					  Добавить слово
					</button>
			  	<table class="table table-striped table-hover">
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
			  	<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addWordForDownModal">
			  		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
						  <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
						</svg>
					  Добавить слово
					</button>
			  	<table class="table table-striped table-hover">
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

		<!-- Песочница -->
	  <div class="tab-pane fade show" id="sandbox-tab-pane" role="tabpanel" aria-labelledby="sandbox-tab" tabindex="0">
	  	<table class="table table-striped table-hover">
		    <thead>
		      <tr>
		        <th scope="col">#</th>
		        <th scope="col">Дата</th>
		        <th scope="col">Источник</th>
		        <th scope="col">Пост</th>
		        <th scope="col">Причина</th>
		      </tr>
		    </thead>
		    <tbody>
		    	<?php
		    		$sandbox = $db->getSandbox();
		    		if ($sandbox) {
    					foreach ($sandbox as $sb) {
    						echo '<tr>';
    						echo "<th>$sb[id]</th>";
    						echo "<td>$sb[date]</td>";
    						echo "<td>$sb[src]</td>";
    						echo "<td>$sb[post]</td>";
    						echo "<td>$sb[reason]</td>";
    						echo '</tr>';
    					}
    				}
    			?>
		    </tbody>
		  </table>
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