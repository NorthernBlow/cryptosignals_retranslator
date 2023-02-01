<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Удачи 🌹">
	<title>Авторизация</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/signin.css" rel="stylesheet">
</head>
<body class="text-center">
	<main class="form-signin w-100 m-auto">
		<form action="/dashboard.php" method="post">
			<div class="form-floating">
				<input type="text" class="form-control" id="inputLogin" name="LOGIN" placeholder="username">
				<label for="inputLogin">пользователь</label>
			</div>
			<div class="form-floating">
				<input type="password" class="form-control" id="inputPassword" name="PASSWORD" placeholder="password">
				<label for="inputPassword">пароль</label>
			</div>

			<button class="w-100 btn btn-lg btn-primary bg-gradient mt-3" type="submit">Вход</button>

			<p class="mt-5 mb-3 badge text-dark">
				with
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-heart-fill" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/>
				</svg>
				from Russia
			</p>
		</form>
	</main>
	<script src="js/bootstrap.bundle.min.js" defer></script>
</body>
</html>