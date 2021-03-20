<?php
session_start(); //reanudar la sesion existente
if (isset($_SESSION['id'])) { //verificar que un usuario tiene una sesión activa en la plataforma
	
	include '../php/inactividad.php';
	expirar(); //verificar que hubo actividad en los ultimos 10 minutos	
	include '../php/functions.php';//algunas funciones extra
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar registros</title>
		<link rel="icon" type="image/png" href="../../img/icons/PERS_icon.png" />

		<!-- Bootstrap -->
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<link href="../css/style_nav.css" rel="stylesheet">
		<link href="../css/style_ini.css" rel="stylesheet">

	</head>
	<?php

	?>

	<body>
		<div class="loader"></div>
		<div class="web-page">
			<div class="content">
				<?php
				/* 
				si lista es igual a 1, el formulario cargará las opciones para actualizar un departamento,
				si es igual a 2, el formulario cargará las opciones para actualizar un recurso,
				si es igual a algún otro valor, mostrará un mensaje de error
				*/
				$nik = $_GET["nik"];
				$lista = $_GET["lista"];

				if ($lista == 1) {
					include '../clases/departamento.php';
					$tabla = new Departamento();
					$result = $tabla->consultarDepartamento($nik); //consultar la existencia de un departamento con el id recibido					
					$cancelar = 'depart.php'; //cancelar para volver a la lista de departamentos
				?> <h2>Lista de departamentos &raquo; Editar departamentos</h2>
					<hr />

				<?php
				} else if ($lista == 2) {
					include '../clases/recurso.php';
					$tabla = new Recurso();
					$result = $tabla->consultarRecurso($nik); //consultar la existencia de un recurso con el id recibido
					$cancelar = 'recursos.php'; //cancelar para volver a la lista de recursos
				?>
					<h2>Lista de recursos &raquo; Editar recursos</h2>
					<hr />
				<?php
				} else {
				?>
					<script type="text/javascript">
						alert("Error, No corresponde a algun x");
						window.location.href = "inicio.php";
					</script>
				<?php
				}



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

				if (isset($_POST['save'])) {

					/* los cambios son anidados con ";;", para luego organizarlos en columnas
					diferentes con los antiguos valores a la izquierda y los nuevos a la derecha
					 */

					$nombre = $tabla->escapar($_POST["nombre"]); // Escapar caracteres especiales 


					$datos = array(
						"nombre" => $nombre,
						"old" => $row['nombre'],
					);
					//actualizar departamento o recurso según el valor de lista
					switch ($lista) {
						case 1:
							$tabla->actualizarDepartamento($nik, $datos, $lista);
							break;
						case 2:
							$tabla->actualizarRecurso($nik, $datos, $lista);
							break;
					}
				}

				if (($_GET['ops']) == 'succ') {
					//mostrar mensaje de éxito
					echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Los datos han sido guardados con éxito.</div>';
				}


				?>
				<form class="form-horizontal text-left" action="" method="post" onsubmit="return checkSubmit();">
					<div class="form-group">
						<label class="col-sm-3 control-label">Nombre</label>
						<div class="col-sm-4">
							<input type="text" name="nombre" value="<?php echo $row['nombre']; ?>" class="form-control" placeholder="Nombre" title="Debe ingresar un nombre para el departamento" required>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">&nbsp;</label>
						<div class="col-sm-6">
							<button type="submit" name="save" class="btn btn-sm btn-primary">Guardar datos</button>
							<a href=<?php echo $cancelar ?> class="btn btn-sm btn-danger">Cancelar</a>
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

						$("button").on("click", function() {
							$("button").prop("disabled", false);
						});

						//titulo del sitio para agregar departamento o recurso
						if (<?php echo $lista; ?> == 1) {
							document.title = "Editar departamento";
						} else {
							document.title = "Editar recurso"
						}

						//restringir el tamaño máximo del texto en el input de nombre del recurso o departamento
						$('[name="nombre"]').keypress(function(event) {
							if (this.value.length >= 200) {
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