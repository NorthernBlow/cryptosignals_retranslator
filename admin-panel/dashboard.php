<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Админ панель</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container-fluid">

	<?php

	require "telegram/Autoloader.php";
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

	require_once "token.php";
	$bot = new Telegram\Bot($tg_bot_token, "CryptoBot", "with Love from Russia");
	$tg  = new Telegram\Receiver($bot);

	$members = $db->getMembers();

	function passed(
		\DateTime $date,
		$time_format = 'H:i',
		$month_format = 'd M в H:i',
		$year_format = 'M Y H:i'
	) {
        // $month_format = datefmt_create( 'ru_RU' ,IntlDateFormatter::FULL, IntlDateFormatter::FULL,
        //                                                                 'Europe/Moscow', IntlDateFormatter::GREGORIAN, 'd LLL в k:mm' );
        // $year_format = datefmt_create( 'ru_RU' ,IntlDateFormatter::FULL, IntlDateFormatter::FULL,
        //                                                                 'Europe/Moscow', IntlDateFormatter::GREGORIAN, 'd LLL yyyy в k:mm' );

		$today = new \DateTime('now', $date->getTimezone());
		$yesterday = new \DateTime('-1 day', $date->getTimezone());
		$tomorrow = new \DateTime('+1 day', $date->getTimezone());
		$minutes_ago = round(($today->format('U') - $date->format('U')) / 60);
		$minutes_in = round(($date->format('U') - $today->format('U')) / 60);

		if ($minutes_ago > 0 && $minutes_ago < 60) {
			return sprintf('%s минут назад', $minutes_ago);
		} elseif ($minutes_in > 0 && $minutes_in < 60) {
			return sprintf('Через %s минут', $minutes_in);
		} elseif ($today->format('ymd') == $date->format('ymd')) {
			return sprintf('Сегодня в %s', $date->format($time_format));
		} elseif ($yesterday->format('ymd') == $date->format('ymd')) {
			return sprintf('Вчера в %s', $date->format($time_format));
		} elseif ($tomorrow->format('ymd') == $date->format('ymd')) {
			return sprintf('Завтра в %s', $date->format($time_format));
		} elseif ($today->format('Y') == $date->format('Y')) {
			return $date->format($month_format);
        } // return datefmt_format($month_format, $date);
        else {
        	return $date->format($year_format);
        }
        // return datedmt_format($year_format);
    }



    if(isset($_GET['test'])) {
    	$db->deleteForTest();
    }



    if (isset($_POST)) {
    	if (isset($_POST['username']) && isset($_POST['password'])) {
				// ADD USER ACTION
    		$db->addUser($_POST['username'], $_POST['password']);
    	} else if (isset($_POST['token']) && isset($_POST['activation_num']) && isset($_POST['lost_date'])) {
				// ADD TOKEN ACTION
    		$lost_date = new DateTime('now');
    		$lost_date->modify(' +' . $_POST['lost_date'] . ' days');
    		$db->addToken($_POST['token'], intval($_POST['activation_num']), $lost_date->format('Y-m-d H:i:s'));
    	} else if (isset($_POST['url'])) {
				// PAGE ACTION
    		if (isset($_POST['sources_page_id'])) {
    			$db->updatePageByID($_POST['sources_page_id'], $_POST['url']);
    		} else {
    			$db->addPage($_POST['url']);
    		}

    	} else if (isset($_POST['chan'])) {
				// CHANNEL ACTION
    		if (isset($_POST['sources_channel_id'])) {
    			$db->updateChannelByID($_POST['sources_channel_id'], $_POST['chan']);
    		} else {
    			$db->addChannel($_POST['chan']);
    		}
    	} else if (isset($_POST['ticker']) && isset($_POST['keywords'])) {
				// TICKER ACTION
    		if (isset($_POST['settings_ticker_id'])) {
    			$pump = isset($_POST['pump']) ? 1 : 0;
    			$db->updateTickerByID($_POST['settings_ticker_id'], $_POST['ticker'], $_POST['keywords'], $pump);
    		} else {
    			$db->addTicker($_POST['ticker'], $_POST['keywords']);
    		}

    	} else if (isset($_POST['stopword'])) {
				// STOPWORDS ACTION
    		if (isset($_POST['settings_stopword_id'])) {
    			$db->updateStopWordByID($_POST['settings_stopword_id'], $_POST['stopword']);
    		} else {
    			$db->addStopWord($_POST['stopword']);
    		}

    	} else if (isset($_POST['word_for_up'])) {
				// WORDS FOR UP ACTION
    		if (isset($_POST['settings_wordup_id'])) {
    			$db->updateWordUpByID($_POST['settings_wordup_id'], $_POST['word_for_up']);
    		} else {
    			$db->addWordUp($_POST['word_for_up']);
    		}

    	} else if (isset($_POST['word_for_down'])) {
				// WORDS FOR DOWN ACTION
    		if (isset($_POST['settings_worddown_id'])) {
    			$db->updateWordDownByID($_POST['settings_worddown_id'], $_POST['word_for_down']);
    		} else {
    			$db->addWordDown($_POST['word_for_down']);
    		}

    	} else if (isset($_POST['pump_for_channel']) && isset($_POST['pump_for_page'])) {
				// PUMP ACTION
    		$db->updatePump($_POST['pump_for_channel'], $_POST['pump_for_page']);

    	} else if (isset($_POST['send_signal']) && isset($_POST['send_signal_ticker'])) {
				// SEND SIGNAL ACTION
    		foreach ($members as $member) {
    			$tg->send
    			->chat($member['user_id'])
    			->text("Сигнал: $_POST[send_signal_ticker] $_POST[send_signal]")
    			->send();
    		}
    		$db->delSandboxByID($_POST['sandbox_id']);
    	} else if (isset($_POST['sender_message'])) {
				// SENDER MESSAGE
    		foreach ($members as $member) {
    			$tg->send
    			->chat($member['user_id'])
    			->text($_POST['sender_message'])
    			->send();
    		}
    		$success_sender = true;
    	} else if (isset($_GET['delete_sandbox'])) {
				// DELETE FROM SANDBOX
    		$db->delSandboxByID($_GET['delete_sandbox']);
    	} else if (isset($_GET['delete_page'])) {
				// DELETE FROM PAGES
    		$db->delPageByID($_GET['delete_page']);
    	} else if (isset($_GET['delete_channel'])) {
				// DELETE FROM CHANNELS
    		$db->delChannelByID($_GET['delete_channel']);
    	} else if (isset($_GET['delete_ticker'])) {
				// DELETE FROM TICKERS
    		$db->delTickerByID($_GET['delete_ticker']);
    	} else if (isset($_GET['delete_stopword'])) {
				// DELETE FROM STOPWORDS
    		$db->delStopWordByID($_GET['delete_stopword']);
    	} else if (isset($_GET['delete_wordup'])) {
				// DELETE FROM WORDS FOR UP
    		$db->delWordUpByID($_GET['delete_wordup']);
    	} else if (isset($_GET['delete_worddown'])) {
				// DELETE FROM WORDS FOR DOWN
    		$db->delWordDownByID($_GET['delete_worddown']);
    	}
    }

    $settings = $db->getSettings();

    function isPump($pump)
    {
    	if($pump) return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="green" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
    	<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
    	</svg>';
    	else return '';
    }

    ?>

    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">Меню</button>


    <div class="offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel">
    	<div class="offcanvas-header">
    		<h5 class="offcanvas-title" id="offcanvasScrollingLabel text-white">with <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="red" class="bi bi-heart-fill" viewBox="0 0 16 16">
    			<path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/>
    		</svg> from Russia</h5>
    		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    	</div>
    	<div class="offcanvas-body text-white">
    		<!-- Главное меню (navbar) -->
    		<ul class="nav nav-pills flex-column" id="myTab" role="tablist">
    			<!-- ПОЛЬЗОВАТЕЛИ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['users']) ? 'active' : '' ?>" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-tab-pane" type="button" role="tab" aria-controls="users-tab-pane" aria-selected="true">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-people mx-2" viewBox="0 0 16 16">
    						<path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
    					</svg>
    					Пользователи
    				</button>
    			</li>
    			<!-- ПРОМО КОДЫ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['tokens']) ? 'active' : '' ?>" id="tokens-tab" data-bs-toggle="tab" data-bs-target="#tokens-tab-pane" type="button" role="tab" aria-controls="tokens-tab-pane" aria-selected="true">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-ticket mx-2" viewBox="0 0 16 16">
						  <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6V4.5ZM1.5 4a.5.5 0 0 0-.5.5v1.05a2.5 2.5 0 0 1 0 4.9v1.05a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-1.05a2.5 2.5 0 0 1 0-4.9V4.5a.5.5 0 0 0-.5-.5h-13Z"/>
						</svg>
    					Промо коды
    				</button>
    			</li>
    			<!-- СТРАНИЦЫ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['pages']) ? 'active' : '' ?>" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages-tab-pane" type="button" role="tab" aria-controls="pages-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-bookmark mx-2" viewBox="0 0 16 16">
    						<path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"/>
    					</svg>
    					Страницы
    				</button>
    			</li>
    			<!-- КАНАЛЫ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['channels']) ? 'active' : '' ?>" id="channels-tab" data-bs-toggle="tab" data-bs-target="#channels-tab-pane" type="button" role="tab" aria-controls="channels-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-telegram mx-2" viewBox="0 0 16 16">
    						<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09z"/>
    					</svg>
    					Каналы
    				</button>
    			</li>
    			<!-- ТИКЕРЫ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['tickers']) ? 'active' : '' ?>" id="pages-tab" data-bs-toggle="tab" data-bs-target="#tickers-tab-pane" type="button" role="tab" aria-controls="pages-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-pass mx-2" viewBox="0 0 16 16">
    						<path d="M5.5 5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5Zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3Z"/>
    						<path d="M8 2a2 2 0 0 0 2-2h2.5A1.5 1.5 0 0 1 14 1.5v13a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-13A1.5 1.5 0 0 1 3.5 0H6a2 2 0 0 0 2 2Zm0 1a3.001 3.001 0 0 1-2.83-2H3.5a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-13a.5.5 0 0 0-.5-.5h-1.67A3.001 3.001 0 0 1 8 3Z"/>
    					</svg>
    					Тикеры
    				</button>
    			</li>
    			<!-- СТОП СЛОВА -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['stopwords']) ? 'active' : '' ?>" id="stopword-tab" data-bs-toggle="tab" data-bs-target="#stopword-tab-pane" type="button" role="tab" aria-controls="stopword-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-sign-stop mx-2" viewBox="0 0 16 16">
    						<path d="M3.16 10.08c-.931 0-1.447-.493-1.494-1.132h.653c.065.346.396.583.891.583.524 0 .83-.246.83-.62 0-.303-.203-.467-.637-.572l-.656-.164c-.61-.147-.978-.51-.978-1.078 0-.706.597-1.184 1.444-1.184.853 0 1.386.475 1.436 1.087h-.645c-.064-.32-.352-.542-.797-.542-.472 0-.77.246-.77.6 0 .261.196.437.553.522l.654.161c.673.164 1.06.487 1.06 1.11 0 .736-.574 1.228-1.544 1.228Zm3.427-3.51V10h-.665V6.57H4.753V6h3.006v.568H6.587Z"/>
    						<path fill-rule="evenodd" d="M11.045 7.73v.544c0 1.131-.636 1.805-1.661 1.805-1.026 0-1.664-.674-1.664-1.805V7.73c0-1.136.638-1.807 1.664-1.807 1.025 0 1.66.674 1.66 1.807Zm-.674.547v-.553c0-.827-.422-1.234-.987-1.234-.572 0-.99.407-.99 1.234v.553c0 .83.418 1.237.99 1.237.565 0 .987-.408.987-1.237Zm1.15-2.276h1.535c.82 0 1.316.55 1.316 1.292 0 .747-.501 1.289-1.321 1.289h-.865V10h-.665V6.001Zm1.436 2.036c.463 0 .735-.272.735-.744s-.272-.741-.735-.741h-.774v1.485h.774Z"/>
    						<path fill-rule="evenodd" d="M4.893 0a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146A.5.5 0 0 0 11.107 0H4.893ZM1 5.1 5.1 1h5.8L15 5.1v5.8L10.9 15H5.1L1 10.9V5.1Z"/>
    					</svg>
    					Стоп слова
    				</button>
    			</li>
    			<!-- НА ПОВЫШЕНИЕ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['wordsup']) ? 'active' : '' ?>" id="wordsup-tab" data-bs-toggle="tab" data-bs-target="#wordsup-tab-pane" type="button" role="tab" aria-controls="wordsup-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-graph-up-arrow mx-2" viewBox="0 0 16 16">
    						<path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/>
    					</svg>
    					На повышение
    				</button>
    			</li>
    			<!-- НА ПОНИЖЕНИЕ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['wordsdown']) ? 'active' : '' ?>" id="wordsdown-tab" data-bs-toggle="tab" data-bs-target="#wordsdown-tab-pane" type="button" role="tab" aria-controls="wordsdown-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-graph-down-arrow mx-2" viewBox="0 0 16 16">
    						<path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 11.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 0-1 0v2.6l-3.613-4.417a.5.5 0 0 0-.74-.037L7.06 8.233 3.404 3.206a.5.5 0 0 0-.808.588l4 5.5a.5.5 0 0 0 .758.06l2.609-2.61L13.445 11H10.5a.5.5 0 0 0-.5.5Z"/>
    					</svg>
    					На понижение
    				</button>
    			</li>
    			<!-- НАСТРОЙКИ -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['settings']) ? 'active' : '' ?>" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-tab-pane" type="button" role="tab" aria-controls="settings-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-gear mx-2" viewBox="0 0 16 16">
    						<path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
    						<path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
    					</svg>
    					Настройки
    				</button>
    			</li>
    			<!-- ПЕСОЧНИЦА -->
    			<li class="nav-item" role="presentation">
    				<button class="nav-link <?=isset($_GET['sandbox']) ? 'active' : '' ?>" id="sandbox-tab" data-bs-toggle="tab" data-bs-target="#sandbox-tab-pane" type="button" role="tab" aria-controls="sandbox-tab-pane" aria-selected="false">
    					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-trash mx-2" viewBox="0 0 16 16">
    						<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
    						<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
    					</svg>Песочница</button>
    				</li>
    				<!-- ФОРМА РАССЫЛКИ -->
    				<li class="nav-item" role="presentation">
    					<button class="nav-link <?=isset($_GET['sender']) ? 'active' : '' ?>" id="sender-tab" data-bs-toggle="tab" data-bs-target="#sender-tab-pane" type="button" role="tab" aria-controls="sender-tab-pane" aria-selected="false">
    						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-send ms-2 me-1" viewBox="0 0 16 16">
    							<path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
    						</svg>
    					</svg>Рассылка</button>
    				</li>
    			</ul>

    		</div>
    	</div>


    	<!-- КОНТЕЙНЕР С КОНТЕНТОМ -->
    	<div class="tab-content" id="myTabContent">

    		<!-- Пользователи -->
    		<div class="tab-pane fade <?=isset($_GET['users']) ? 'show active' : '' ?>" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
    			<!-- <button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
				  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-plus mx-2" viewBox="0 0 16 16">
					  <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
					  <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
					</svg>
				  Добавить пользователя
				</button> -->
    			<table class="table table-striped table-hover w-100" id="users-table">
    				<thead class="bg-primary text-white">
    					<tr>
    						<th scope="col" class="d-none">#</th>
    						<th scope="col">Имя пользователя</th>
    						<th scope="col">Промо-код</th>
    						<th scope="col">Дата приглашения</th>
    						<th scope="col">Истекает (дней)</th>
    					</tr>
    				</thead>
    				<tbody>
					<?php
    					$members = $db->getMembers();
    					$tokens = $db->getTokens();
    					function getTokenName($tokens, $id)
    					{
    						foreach($tokens as $token) {
    							if ($token['id'] == $id)
    								return $token['token'];
    						}
    					}
    					function getTokenLost($tokens, $id)
    					{
    						foreach($tokens as $token) {
    							if ($token['id'] == $id)
    								return $token['lost_date'];
    						}
    					}
		    		$now = time(); // текущее время (метка времени)
		    		if ($members) {
		    			foreach ($members as $member) {
		    				$lost_date = getTokenLost($tokens, $member['token_id']);
		    				if ($lost_date == '0000-00-00 00:00:00') {
		    					$lost_days = 'неограничено';
		    				} else {
    							$your_date = strtotime($lost_date); // какая-то дата в строке (1 января 2017 года)
    							if($now < $your_date) {
									$datediff = $now - $your_date; // получим разность дат (в секундах)
									$lost_days = abs(floor($datediff / (60 * 60 * 24)));
								} else {
									$lost_days = 0;
								}
							}

							if ($lost_days == 0)
								echo '<tr class="bg-danger">';
							else if ($lost_days <= 3)
								echo '<tr class="bg-warning">';
							else
								echo '<tr>';
							echo "<th class='d-none'>$member[id]</th>";
							echo "<td title='$member[user_id]'>$member[user_name]</td>";
							echo '<td>' . getTokenName($tokens, $member['token_id']) . '</td>';
							echo "<td>$member[date]</td>";
							echo '<td>' . $lost_days . '</td>';
							echo '</tr>';
						}
					}
					?>
					</tbody>
				</table>
			</div>

			<!-- Промо коды -->
    		<div class="tab-pane fade <?=isset($_GET['tokens']) ? 'show active' : '' ?>" id="tokens-tab-pane" role="tabpanel" aria-labelledby="tokens-tab" tabindex="0">
    			<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addTokenModal">
					<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square mx-2" viewBox="0 0 16 16">
						<path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
					</svg>
				  	Добавить промо код
				</button>
    			<table class="table table-striped table-hover w-100" id="tokens-table">
    				<thead class="bg-primary text-white">
    					<tr>
    						<th scope="col" class="d-none">#</th>
    						<th scope="col">Токен</th>
    						<th scope="col">Кол-во активаций</th>
    						<th scope="col">Дата создания</th>
    						<th scope="col">Истекает (дней)</th>
    					</tr>
    				</thead>
    				<tbody>
    					<?php
    					$tokens = $db->getTokens();
			    		$now = time(); // текущее время (метка времени)
			    		if ($tokens) {
			    			foreach ($tokens as $token) {
			    				if ($token['lost_date'] == '0000-00-00 00:00:00') {
			    					$lost_days = 'неограничено';
			    				} else {
	    							$your_date = strtotime($token['lost_date']); // какая-то дата в строке (1 января 2017 года)
	    							if($now < $your_date) {
										$datediff = $now - $your_date; // получим разность дат (в секундах)
										$lost_days = abs(floor($datediff / (60 * 60 * 24)));
									} else {
										$lost_days = 0;
									}
								}

								if ($lost_days == 0 || $token['activation_num'] == 0)
									echo '<tr class="bg-danger">';
								else if ($lost_days <= 3 || $token['activation_num'] <= 3)
									echo '<tr class="bg-warning">';
								else
									echo '<tr>';

								echo "<th class='d-none'>$token[id]</th>";
								echo "<td>$token[token]</td>";
								echo '<td>' . $token['activation_num'] . '</td>';
								echo "<td>$token[created_date]</td>";
								echo '<td>' . $lost_days . '</td>';
								echo '</tr>';
							}
						}
						?>
					</tbody>
				</table>
			</div>

		<!-- Страницы -->
		<div class="tab-pane fade <?=isset($_GET['pages']) ? 'show active' : '' ?>" id="pages-tab-pane" role="tabpanel" aria-labelledby="pages-tab" tabindex="0">
			<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addPageModal">
				<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square mx-2" viewBox="0 0 16 16">
					<path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
					<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
				</svg>
				Добавить страницу
			</button>
			<table class="table table-striped table-hover w-100" id="pages-table">
				<thead class="bg-primary text-white">
					<tr>
						<th scope="col" class="d-none">#</th>
						<th scope="col"></th>
						<th scope="col">URL</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$pages = $db->getPages();
					if ($pages) {
						foreach ($pages as $page) { ?>
							<tr>
								<?php
								echo "<th class='d-none'>$page[id]</th>";
								echo '<td><a href="?sources&pages&delete_page=' . $page['id'] . '" class="btn btn-danger">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
								<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
								<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
								</svg>
								</a></td>';
								echo "<td onclick=\"window.location.href='/edit.php?page=$page[id]'; return false\">$page[url]</td>";
								echo '</tr>';
							}
						}
						?>
					</tbody>
				</table>
			</div>

			<!-- Каналы -->
			<div class="tab-pane fade <?=isset($_GET['channels']) ? 'show active' : '' ?>" id="channels-tab-pane" role="tabpanel" aria-labelledby="channels-tab" tabindex="0">
				<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addChannelModal">
					<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square mx-2" viewBox="0 0 16 16">
						<path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
						<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
					</svg>
					Добавить канал
				</button>
				<table class="table table-striped table-hover w-100" id="channels-table">
					<thead class="bg-primary text-white">
						<tr>
							<th scope="col" class="d-none">#</th>
							<th scope="col"></th>
							<th scope="col">Username</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$channels = $db->getChannels();
						if ($channels) {
							foreach ($channels as $channel) { ?>
								<tr>
									<?php
									echo "<th class='d-none'>$channel[id]</th>";
									echo '<td><a href="?sources&channels&delete_channel=' . $channel['id'] . '" class="btn btn-danger">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
									<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
									<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
									</svg>
									</a></td>';
									echo "<td onclick=\"window.location.href='/edit.php?channel=$channel[id]'; return false\">$channel[chan]</td>";
									echo '</tr>';
								}
							}
							?>
						</tbody>
					</table>
				</div>


				<!-- Тикеры -->
				<div class="tab-pane fade <?=isset($_GET['tickers']) ? 'show active' : '' ?>" id="tickers-tab-pane" role="tabpanel" aria-labelledby="tickers-tab" tabindex="0">
					<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addTickerModal">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square mx-2" viewBox="0 0 16 16">
							<path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
							<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
						</svg>
						Добавить тикер
					</button>
					<table class="table table-striped table-hover w-100" id="tickers-table">
						<thead class="bg-primary text-white">
							<tr>
								<th scope="col" class="d-none">#</th>
								<th scope="col"></th>
								<th scope="col">Тикер</th>
								<th scope="col">Ключевые слова</th>
								<th scope="col">PUMP</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$tickers = $db->getTickers();
							if ($tickers) {
								foreach ($tickers as $ticker) { ?>
									<tr>
										<?php
										echo "<th class='d-none'>$ticker[id]</th>";
										echo '<td><a href="?tickers&delete_ticker=' . $ticker['id'] . '" class="btn btn-danger">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
										<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
										<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
										</svg>
										</a></td>';
										echo "<td onclick=\"window.location.href='/edit.php?ticker=$ticker[id]'; return false\">$ticker[ticker]</td>";
										echo "<td onclick=\"window.location.href='/edit.php?ticker=$ticker[id]'; return false\">$ticker[keywords]</td>";
										echo "<td onclick=\"window.location.href='/edit.php?ticker=$ticker[id]'; return false\">" . isPump($ticker['pump']) . "</td>";
										echo '</tr>';
									}
								}
								?>
							</tbody>
						</table>
					</div>

					<!-- Стоп слова -->
					<div class="tab-pane fade <?=isset($_GET['stopwords']) ? 'show active' : '' ?>" id="stopword-tab-pane" role="tabpanel" aria-labelledby="stopword-tab" tabindex="0">
						<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addStopWordModal">
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square mx-2" viewBox="0 0 16 16">
								<path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
								<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
							</svg>
							Добавить стоп слово
						</button>
						<table class="table table-striped table-hover w-100" id="stopwords-table">
							<thead class="bg-primary text-white">
								<tr>
									<th scope="col" class="d-none">#</th>
									<th scope="col"></th>
									<th scope="col">Стоп слово</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$stopwords = $db->getStopWords();
								if ($stopwords) {
									foreach ($stopwords as $stopword) { ?>
										<tr>
											<?php
											echo "<th class='d-none'>$stopword[id]</th>";
											echo '<td><a href="?stopwords&delete_stopword=' . $stopword['id'] . '" class="btn btn-danger">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
											<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
											<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
											</svg>
											</a></td>';
											echo "<td onclick=\"window.location.href='/edit.php?stopword=$stopword[id]'; return false\">$stopword[stopword]</td>";
											echo '</tr>';
										}
									}
									?>
								</tbody>
							</table>
						</div>

						<!-- Слова на повышение -->
						<div class="tab-pane fade <?=isset($_GET['wordsup']) ? 'show active' : '' ?>" id="wordsup-tab-pane" role="tabpanel" aria-labelledby="wordsup-tab" tabindex="0">
							<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addWordForUpModal">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square mx-2" viewBox="0 0 16 16">
									<path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
									<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
								</svg>
								Добавить слово
							</button>
							<table class="table table-striped table-hover w-100" id="wordsup-table">
								<thead class="bg-primary text-white">
									<tr>
										<th scope="col" class="d-none">#</th>
										<th scope="col"></th>
										<th scope="col">Слова</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$wordsup = $db->getWordsUp();
									if ($wordsup) {
										foreach ($wordsup as $word_for_up) { ?>
											<tr>
												<?php
												echo "<th class='d-none'>$word_for_up[id]</th>";
												echo '<td><a href="?wordsup&delete_wordup=' . $word_for_up['id'] . '" class="btn btn-danger">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
												<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
												<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
												</svg>
												</a></td>';
												echo "<td onclick=\"window.location.href='/edit.php?wordup=$word_for_up[id]'; return false\">$word_for_up[word_for_up]</td>";
												echo '</tr>';
											}
										}
										?>
									</tbody>
								</table>
							</div>

							<!-- Слова на понижение -->
							<div class="tab-pane fade <?=isset($_GET['wordsdown']) ? 'show active' : '' ?>" id="wordsdown-tab-pane" role="tabpanel" aria-labelledby="wordsdown-tab" tabindex="0">
								<button type="button" class="btn btn-default m-3" data-bs-toggle="modal" data-bs-target="#addWordForDownModal">
									<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square mx-2" viewBox="0 0 16 16">
										<path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
										<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
									</svg>
									Добавить слово
								</button>
								<table class="table table-striped table-hover w-100" id="wordsdown-table">
									<thead class="bg-primary text-white">
										<tr>
											<th scope="col" class="d-none">#</th>
											<th scope="col"></th>
											<th scope="col">Слова</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$wordsdown = $db->getWordsDown();
										if ($wordsdown) {
											foreach ($wordsdown as $word_for_down) { ?>
												<tr>
													<?php
													echo "<th class='d-none'>$word_for_down[id]</th>";
													echo '<td><a href="?wordsdown&delete_worddown=' . $word_for_down['id'] . '" class="btn btn-danger">
													<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
													<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
													<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
													</svg>
													</a></td>';
													echo "<td onclick=\"window.location.href='/edit.php?worddown=$word_for_down[id]'; return false\">$word_for_down[word_for_down]</td>";
													echo '</tr>';
												}
											}
											?>
										</tbody>
									</table>
								</div>

								<!-- Настройки -->
								<div class="tab-pane fade <?=isset($_GET['settings']) ? 'show active' : '' ?>" id="settings-tab-pane" role="tabpanel" aria-labelledby="settings-tab" tabindex="0">
									<form action="/dashboard.php?settings" method="post" class="p-5">
										<label><span class="badge bg-primary">PUMP</span> для канала:</label>
										<input class="form-control mb-2" type="text" placeholder="2000" aria-label=".form-control-lg example" name="pump_for_channel" value="<?=$settings[0]['value']?>">
										<label class="pt-5"><span class="badge bg-primary">PUMP</span> для страницы:</label>
										<input class="form-control" type="text" placeholder="10000" aria-label=".form-control-lg example" name="pump_for_page" value="<?=$settings[1]['value']?>">
										<button type="sumbit" class="btn btn-success mt-3">Сохранить</button>
									</form>
								</div>

								<!-- Песочница -->
								<div class="tab-pane fade <?=isset($_GET['sandbox']) ? 'show active' : '' ?>" id="sandbox-tab-pane" role="tabpanel" aria-labelledby="sandbox-tab" tabindex="0">
									<table class="table table-striped table-hover w-100" id="sandbox-table">
										<thead class="bg-primary text-white">
											<tr>
												<th scope="col" class="d-none">#</th>
												<th scope="col" class="d-none d-md-table-cell">Дата</th>
												<th scope="col" class="d-none d-md-table-cell">Источник</th>
												<th scope="col">Пост</th>
												<th scope="col">Причина</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$sandbox = $db->getSandbox();
											if ($sandbox) {
												foreach ($sandbox as $sb) {

													$reason = "'/send_signal.php?sandbox_id=$sb[id]'";
													$timestamp = new DateTime($sb['date'], new DateTimeZone('Europe/Moscow'));

													if (!filter_var($sb['src'], FILTER_VALIDATE_URL) === false) {
														$go_to_sources = '/dashboard.php?pages';
													} else {
														$go_to_sources = '/dashboard.php?channels';
													}


													?>
													<tr>
														<th class="d-none" onclick="window.location.href=<?=$reason?>; return false" ><?=$sb['id']?></th>
														<td class="d-none d-md-table-cell text-nowrap" onclick="window.location.href=<?=$reason?>; return false"><?=passed($timestamp)?></td>
														<td class="d-none d-md-table-cell overflow-hidden"><a href="<?=$go_to_sources?>"><?=$sb['src']?></a></td>
														<td onclick="window.location.href=<?=$reason?>; return false"><?=mb_strimwidth($sb['post'], 0, 96, '')?>…</td>
														<td onclick="window.location.href=<?=$reason?>; return false"><span class='badge bg-danger'><?=$sb['reason']?></span></td>
													</tr>
													<?php
												}
											}
											?>
										</tbody>
									</table>
								</div>

								<!-- Рассылка -->
								<div class="tab-pane fade <?=isset($_GET['sender']) ? 'show active' : '' ?>" id="sender-tab-pane" role="tabpanel" aria-labelledby="sandbox-tab" tabindex="0">
									<?php if (isset($success_sender)) "<p class='alert alert-success m-2'>Рассылка отправлена!</p>";?>
									<form action="/dashboard.php?sender" method="post" class="m-xl-5 m-2">
										<textarea class="form-control" placeholder="Введите сообщение для рассылки" rows="15" name="sender_message"></textarea>
										<button type="sumbit" class="btn btn-success">Отправить</button>
									</form>
								</div>

							</div>




							<!-- Modal -->
							<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новый пользователь</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?users" method="post">
											<div class="modal-body">
												<input class="form-control mb-2" type="text" placeholder="Имя пользователя" aria-label=".form-control-lg example" name="username">
												<input class="form-control" type="text" placeholder="Пароль" aria-label=".form-control-lg example" name="password">
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
							<div class="modal fade" id="addTokenModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новый промо код</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?users" method="post">
											<div class="modal-body">
												<input class="form-control mb-2" type="text" placeholder="Промо-код" aria-label=".form-control-lg example" name="token">
												<input class="form-control mb-2" type="text" placeholder="Кол-во активаций" aria-label=".form-control-lg example" name="activation_num">
												<input class="form-control" type="text" placeholder="Срок действия (дней)" aria-label=".form-control-lg example" name="lost_date">
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
							<div class="modal fade" id="addPageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новая страница для парсинга</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?sources&pages" method="post">
											<div class="modal-body">
												<input class="form-control mb-2" type="text" placeholder="URL" aria-label=".form-control-lg example" name="url">
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
							<div class="modal fade" id="addChannelModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новый канал для парсинга</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?sources&channels" method="post">
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
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новый тикер для парсинга</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?tickers" method="post">
											<div class="modal-body">
												<input class="form-control mb-2" type="text" placeholder="Тикер" aria-label=".form-control-lg example" name="ticker">
												<input class="form-control mb-2" type="text" placeholder="Ключевые слова" aria-label=".form-control-lg example" name="keywords">
												<div class="form-check">
													<input class="form-check-input" type="checkbox" name="pump" id="flexCheckDefault">
													<label class="form-check-label" for="flexCheckDefault">
														Возможен PUMP
													</label>
												</div>
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
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новое стоп-слово</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?stopwords" method="post">
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
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новое слово (повышение)</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?wordsup" method="post">
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
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="exampleModalLabel">Новое слово (понижение)</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<form action="/dashboard.php?wordsdown" method="post">
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

							<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
							<link href="//cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.css" rel="stylesheet">
							<script src="//cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
							<script src="//cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>
							<script src="js/bootstrap.bundle.min.js"></script>
							<script type="text/javascript">
								$(document).ready(function () {
								    $('#users-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#tokens-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#pages-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#channels-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#tickers-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#stopwords-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#wordsup-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#wordsdown-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								    $('#sandbox-table').DataTable({
								        pagingType: 'full_numbers',
								        "pageLength": 50,
								        "language": {
								            "lengthMenu": "Показывать по _MENU_",
								            "zeroRecords": "Ничего не найдено",
								            "info": "Страница _PAGE_ из _PAGES_",
								            "infoEmpty": "Нет записей",
								            "infoFiltered": "(всего _MAX_ записей)",
								            "search": "Поиск",
								            "paginate": {
								            	"first": "«",
								            	"previous": "←",
								            	"next": "→",
								            	"last": "»",
								            }
								        }
								    });
								});
							</script>
						</body>
						</html>