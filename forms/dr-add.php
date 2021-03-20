<?php

session_start(); //reanudar la sesion existente
if (isset($_SESSION['id'])) { //verificar que un usuario tiene una sesión activa en la plataforma
	include '../php/inactividad.php';
	expirar(); //verificar que hubo actividad en los ultimos 10 minutos	
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Agregar departamento</title>
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
				si lista es igual a 1, el formulario cargará las opciones para agregar un departamento,
				si es igual a 2, el formulario cargará las opciones para agregar un recurso,
				si es igual a algún otro valor, mostrará un mensaje de error
				*/


				$lista = $_GET['lista'];

				if ($lista == 1) {
					include '../clases/departamento.php';
					$tabla = new Departamento();					
					$cancelar='depart.php';//cancelar para volver a la lista de departamentos
				?> <h2>Lista de departamentos &raquo; Agregar departamento</h2>
					<hr />
				<?php
				} else if ($lista == 2) {
					include '../clases/recurso.php';
					$tabla = new Recurso();
					$cancelar='recursos.php';//cancelar para volver a la lista de recursos
				?> <h2>Lista de recursos &raquo; Agregar recurso</h2>
					<hr />
				<?php	} else {
				?>
					<script type="text/javascript">
						alert("Error, No corresponde a algun valor");
						window.location.href = "inicio.php";
					</script>
				<?php
				}

				if (isset($_POST['add'])) {
					switch ($lista) {
						case 1:
							$tabla->agregarDepartamento($_POST["nombre"]);
							break;
						case 2:
							$tabla->agregarRecurso($_POST["nombre"]);
							break;
					}
				}

				?>

				<form class="form-horizontal" action="#" method="post" onsubmit="return checkSubmit();">
					<div class="form-group">
						<label class="col-sm-3 control-label">Nombre</label>
						<div class="col-sm-4">
							<input type="text" name="nombre" class="form-control" placeholder="Nombre" title="Debe ingresar un nombre para el departamento" required>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">&nbsp;</label>
						<div class="col-sm-6">
							<button type="submit" name="add" class="btn btn-sm btn-primary">Guardar datos</button>
							<a href=<?php echo $cancelar?> class="btn btn-sm btn-danger">Cancelar</a>
							
						</div>
					</div>
				</form>

			</div>
		</div>
		<?php include 'nav.php'; ?>
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

				//titulo del sitio para agregar departamento o recurso
				if (<?php echo $lista; ?> == 1) {
					document.title = "Agregar departamento";
				} else {
					document.title = "Agregar recurso"
				}

				//restringir el tamaño máximo del texto en el input de nombre del recurso o departamento
				$('[name="nombre"]').keypress(function(event) {
					if (this.value.length >= 50) {
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