<?php

session_start(); //reanudar la sesion existente
if (isset($_SESSION['id'])) { //verificar que un usuario tiene una sesión activa en la plataforma
	include '../php/inactividad.php';
	expirar(); //verificar que hubo actividad en los ultimos 10 minutos
	include '../clases/usuario.php';
	include '../php/encriptar.php';//funciones para encriptar y desencriptar
	include '../php/functions.php'; //algunas funciones extra
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Agregar usuario</title>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<link href="../css/style_nav.css" rel="stylesheet">
		<link href="../css/style_ini.css" rel="stylesheet">
		<link rel="icon" type="image/png" href="../../img/icons/PERS_icon.png" />

	</head>

	<body>
		<div class="loader"></div>
		<div class="web-page">
			<div class="content">


				<?php
				/* 
				si se recibe un valor de id el formulario cargará opciones para agregar un usuario,
				de lo contrario, el formulario cargará opciones para agregar un recurso,
				
				*/
				$tabla = new Usuario();
				if (($_GET["nik"])) {
					$cuenta = $_SESSION['id'];
					if ($cuenta != $_GET["nik"]) {
				?>
						<script type="text/javascript">
							//mostrar error si se ingresa un id no correspondiente a la sesión iniciada
							alert("Error, No corresponde a su cuenta de usuario");
						</script>
					<?php
						header("Location: usu-adedit.php?nik=" . $cuenta);
					}

					?>
					<script type="text/javascript">
						//cambiar titulo de la pagina
						document.title = "Editar perfil";
					</script>
					<?php

					$nik = $tabla->escapar($_GET["nik"]); //guardar valor del id recibido				
					$result = $tabla->consultarUsuario($nik); //consultar la existencia de un usuario con el id recibido

					?>
					<h2>Editar Perfil</h2>
					<hr />
					<?php


					if ($result->num_rows == 0) {
					?>
						<script type="text/javascript">
							alert("Error, No corresponde a algun valor");
							window.location.href = "inicio.php";
						</script>
					<?php

					} else {
						$row = $result->fetch_assoc();
					}
				} else {

					?> <h2>Usuarios &raquo; Agregar usuario</h2>
					<hr />
				<?php

				}

				//si se recibe el formulario para actualizar la información de un usuario
				if (($_POST['save'])) {
					$usuario = $tabla->escapar($_POST["usuario"]); //Escapar caracteres especiales 
					$nombres = $tabla->escapar($_POST["nombres"]); //Escapar caracteres especiales 
					$apellidos = $tabla->escapar($_POST["apellidos"]); //Escapar caracteres especiales 
					$contrasena = $encriptar($tabla->escapar($_POST["contrasena"])); //Escapar caracteres especiales 
					$nuevos = array(
						"nombres" => $nombres,
						"apellidos" => $apellidos,
						"usuario" => $usuario,
						"contrasena" => $contrasena
					);
					$antiguos = array(
						"nombres" => $row['nombres'],
						"apellidos" => $row['apellidos'],
						"usuario" => $row['usuario'],
						"contrasena" => $row['contrasena']
					);

					$tabla->actualizarUsuario($nuevos, $antiguos,$row['id']);
				}

				//si se recibe el formulario para agregar un usuario
				if (($_POST['add'])) {
					$usuario = $tabla->escapar($_POST["usuario"]); //Escapar caracteres especiales 
					$nombres = $tabla->escapar($_POST["nombres"]); //Escapar caracteres especiales 
					$apellidos = $tabla->escapar($_POST["apellidos"]); //Escapar caracteres especiales 
					$contrasena = $encriptar($tabla->escapar($_POST["contrasena"])); //Escapar caracteres especiales 
					$datos = array(
						"nombres" => $nombres,
						"apellidos" => $apellidos,
						"usuario" => $usuario,
						"contrasena" => $contrasena
					);
					$tabla->agregarUsuario($datos);
				}


				if (($_GET['ops'])) {
					switch ($_GET['ops']) {
						case 'succ':
							echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Los datos han sido guardados con éxito.</div>';
							break;
						case 'erro':
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error, no se pudo guardar los datos.</div>';
							break;
					}
				}

				?>
				<form class="form-horizontal text-left" action="" method="post" onsubmit="return checkSubmit();">
					<?php
					//si se recibe un valor de id, el formulario cargará la información de un usuario para editarla
					if (($_GET["nik"])) {
					?>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nombres</label>
							<div class="col-sm-3">
								<input type="text" name="nombres" value="<?php echo $row['nombres']; ?>" class="form-control" placeholder="Nombres" required pattern=".{5,50}" title="El campo de nombres debe tener mínimo 5 y máximo 50 caracteres">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Apellidos</label>
							<div class="col-sm-3">
								<input type="text" name="apellidos" value="<?php echo $row['apellidos']; ?>" class="form-control" placeholder="Apellidos">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Usuario</label>
							<div class="col-sm-3">
								<input type="text" name="usuario" value="<?php echo $row['usuario']; ?>" class="form-control" placeholder="Usuario" required pattern=".{5,20}" title="El campo de usuario debe tener entre 5 y 20 caracteres">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Contraseña</label>

							<div class="col-sm-3">
								<div class="input-group">
									<input type="password" name="contrasena" class="form-control pwd" value="<?php echo $desencriptar($row['contrasena']); ?>" placeholder="Contraseña" required pattern=".{5,15}" title="La contraseña debe tener entre 5 y 15 caracteres" style="width: 252px;">
									<button class="btn btn-default reveal" type="button">
										<span><i class="glyphicon glyphicon-eye-open"></i></span>
									</button>
								</div>

							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">&nbsp;</label>
							<div class="col-sm-6">
								<input type="submit" name="save" class="btn btn-sm btn-primary" value="Guardar datos">
								<a href="usuarios.php" class="btn btn-sm btn-danger">Cancelar</a>
							</div>
						</div>

					<?php
						$result->close();
					}


					//de lo contrario, el formulario será para agregar un usuario
					else { ?>

						<div class="form-group">
							<label class="col-sm-3 control-label">Nombres</label>
							<div class="col-sm-4">
								<input type="text" name="nombres" class="form-control" placeholder="Nombres" required pattern=".{5,50}" title="El campo de nombres debe tener mínimo 5 y máximo 50 caracteres">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Apellidos</label>
							<div class="col-sm-4">
								<input type="text" name="apellidos" class="form-control" placeholder="Apellidos">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Usuario</label>
							<div class="col-sm-4">
								<input type="text" name="usuario" class="form-control" placeholder="Usuario" autocomplete="new-text" required pattern=".{5,20}" title="El campo de usuario debe tener entre 5 y 20 caracteres">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Contraseña</label>

							<div class="col-sm-3">
								<div class="input-group">
									<input type="password" name="contrasena" class="form-control pwd" placeholder="Contraseña" required pattern=".{5,15}" title="La contraseña debe tener entre 5 y 15 caracteres" style="width: 252px;">
									<button class="btn btn-default reveal" type="button">
										<span><i class="glyphicon glyphicon-eye-open"></i></span>
									</button>
								</div>

							</div>
						</div>


						<div class="form-group">
							<label class="col-sm-3 control-label">&nbsp;</label>
							<div class="col-sm-6">
								<input type="submit" name="add" class="btn btn-sm btn-primary" value="Guardar datos">
								<a href="usuarios.php" class="btn btn-sm btn-danger">Cancelar</a>
							</div>
						</div>
					<?php
					} ?>

				</form>
			</div>
		</div>


		<?php

		include 'nav.php'; ?>
		<script type="text/javascript">
			//evitar el envio doble de los formularios
			var statSend = false;

			function checkSubmit() {
				if (!statSend) {
					statSend = true;
					return true;
				} else {
					return false;
				}
			}
			$(document).ready(function() {
				elementos(); // cargar funcionalidades del menu lateral

				//mostrar u ocultar contraseña
				$(".reveal").on('click', function() {
					var $pwd = $(".pwd");
					if ($pwd.attr('type') === 'password') {
						$pwd.attr('type', 'text');
					} else {
						$pwd.attr('type', 'password');
					}
				});
				//restringir el tamaño máximo del texto en el input de nombres del usuario
				$('[name="nombres"]').keypress(function(event) {
					if (this.value.length >= 50) {
						return false;
					}
				});
				//restringir el tamaño máximo del texto en el input de apellidos del usuario				
				$('[name="apellidos"]').keypress(function(event) {
					if (this.value.length >= 50) {
						return false;
					}
				});
				//restringir el tamaño máximo del texto en el input de usuario del usuario				
				$('[name="usuario"]').keypress(function(event) {
					if (this.value.length >= 20) {
						return false;
					}
				});
				//restringir el tamaño máximo del texto en el input de contraseña del usuario				
				$('[name="contrasena"]').keypress(function(event) {
					if (this.value.length >= 15) {
						return false;
					}
				});

			});
		</script>
	</body>
<?php
} else {
	//si no hay una sesión activa en la plataforma,redirige al formulario de login
	header("location:../index.php");
}
?>

	</html>