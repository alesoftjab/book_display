<!DOCTYPE html>
	<html lang='en'>
		<head>
			<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
			<meta name='description' content=''>
			<meta name='author' content=''>
			<link rel='icon' href='../../favicon.ico'>

			<meta charset='utf-8'>
			<meta http-equiv='X-UA-Compatible' content='IE=edge'>
			<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>

			<link href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css' rel='stylesheet' type='text/css' />
			<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
			<script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js'></script>

			<title>BOOK DISPLAY - Ingreso</title>

			<!-- bootstrap -->
			<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css' integrity='sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb' crossorigin='anonymous'>
			<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
			<!-- estilo css -->
			<link href='css/signin.css' rel='stylesheet'>
			<style>
				html {
					min-height: 100%; /* This is required to ensure that the gradient stretches across the whole page */
				}
			</style>
		</head>

		<body>

		  <div class='container '>

		  	<div class="card text-center  border-primary" >
				  <h3 class="card-header text-white bg-primary">Ingreso al Sistema</h3>
				  <div class="card-body">
				  	<form class='form-signin' method='post' action='index.php' name='_gg' id='_gg'>

								<!-- <div class='class="card-subtitle"'>Introduzca sus datos</div> -->
								<!-- <h3>Introduzca sus datos</h3> -->
								<div class='form-group'  align='center'>
									<img class='img-rounded' src='images/logo_empresa.jpg' alt='EMPRESA' style='width:200px'>
								</div>

								<div class='input-group form-group'>
									<span class='input-group-addon' id='basic-addon1' ><span class="fa fa-user"></span></span>
									<input type='text' name=usuario placeholder='Usuario' class='form-control' aria-describedby='basic-addon1' required autofocus>
								</div>

								<div class='input-group form-group'>
									<span class='input-group-addon' id='basic-addon2'>
										<span class="fa fa-lock"></span>
									</span>
									<input type=password name=password placeholder='Contraseña' class='form-control' aria-describedby='basic-addon2' required>
								</div>

								<div class='form-group'>
									<input type=hidden name=_c_ value="home">
									<input type='submit' value='Ingresar' name='Ingresar' id=aceptar class='btn btn-lg btn-primary btn-block'>
								</div>
							</form>
				  </div>
			  </div>

		  </div> <!-- /container -->


			<!-- jQuery first, then Popper.js, then Bootstrap JS -->
			<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>

			<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
			<script src='../../assets/js/ie10-viewport-bug-workaround.js'></script>

		</body>
	</html>
