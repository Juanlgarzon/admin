<?php

session_start();//reanudar la sesion existente
if (isset($_SESSION['id'])) { //verificar que un usuario tiene una sesión activa en la plataforma
	include '../php/inactividad.php';
	expirar(); //verificar que hubo actividad en los ultimos 10 minutos
	include '../clases/capa.php';	
	include '../php/functions.php'; //algunas funciones extra
	
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar capa</title>
		<link rel="icon" type="image/png" href="../../img/icons/PERS_icon.png" />

		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<link href="../css/style_nav.css" rel="stylesheet">
		<link href="../css/style_ini.css" rel="stylesheet">

	</head>

	<body>
		<div class="loader"></div>

		<div class="web-page">
			<div class="content">
				<?php
				$tabla = new Capa();
				$nik = $tabla->escapar($_GET["nik"]); //guardar valor del id recibido
				?>

				<h2>Capas a mostrar &raquo; Editar capas</h2>
				<hr />

				<?php

				
				$result = $tabla->consultarCapa($nik);//consultar la existencia de una capa con el id recibido
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
					
					//nuevos valores de la capa recibidos
					$ubicacion = $tabla->escapar($_POST["ubicacion"]); //Escapar caracteres especiales
					$titulo = $tabla->escapar($_POST["titulo"]);
					$ttl = $tabla->escapar($_POST["ttl"]);
					$archivo = $tabla->escapar($_POST["archivo"]);
					$dep = $tabla->escapar($_POST["sdep"]);
					$rec = $tabla->escapar($_POST["srec"]);
					
					$antiguos = array(
						"ubicacion" => $row['ubicacion'],
						"titulo" => $row['titulo'],
						"ttl" => $row['ttl_lynd'],
						"archivo" => $row['url_archivo'],
						"dep" => $row['dep'],
						"rec" => $row['rec']
					);
					//antiguos valores de la capa
					$nuevos = array(
						"ubicacion" => $ubicacion,
						"titulo" => $titulo,
						"ttl" => $ttl,
						"archivo" => $archivo,
						"dep" => $dep,
						"rec" => $rec
					);
					$tabla->actualizarCapa($nuevos,$antiguos,$nik);
					
				}
				if (isset($_GET['ops']) && ($_GET['ops']) == 'succ') {
					//mostrar mensaje de éxito
					echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Los datos han sido guardados con éxito.</div>';
				}

				?>
				<form class="form-horizontal text-left" action="" method="post" onsubmit="return checkSubmit();">

					<div class="form-group">
						<label class="col-sm-3 control-label">Ubicacion</label>
						<div class="col-sm-4">
							<input type="text" name="ubicacion" value="<?php echo $row['ubicacion']; ?>" class="form-control" placeholder="Ubicacion" title="Debe ingresar la localizacion de la capa en Geoserver del espacio PERS" required>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Titulo de la capa</label>
						<div class="col-sm-4">
							<input type="text" name="titulo" value="<?php echo $row['titulo']; ?>" class="form-control" placeholder="Titulo" title="Debe ingresar un nombre de la capa a mostrar" required>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Titulo de la leyenda</label>
						<div class="col-sm-4">
							<input type="text" name="ttl" value="<?php echo $row['ttl_lynd']; ?>" class="form-control" placeholder="Titulo leyenda" title="Debe ingresar un titulo para la leyenda">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Archivo asociado</label>
						<div class="col-sm-4">
							<input type="text" name="archivo" value="<?php echo $row['url_archivo']; ?>" class="form-control" placeholder="Archivo">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Departamento</label>
						<div class="col-sm-4">
							<select class="selectpicker" name="sdep">
								<!--agregar los nombres de todos los departamentos a selector-->
								<?php $tabla->getNdeps($row['dep']); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Recurso</label>
						<div class="col-sm-4">
							<select class="selectpicker" name="srec">
								<!--agregar los nombres de todos los recursos a selector-->
								<?php $tabla->getNrecs($row['rec']); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">&nbsp;</label>
						<div class="col-sm-6">
							<input type="submit" name="save" class="btn btn-sm btn-primary" value="Guardar datos">
							<a href="capas.php" class="btn btn-sm btn-danger">Cancelar</a>
						</div>
					</div>




				</form>
			</div>
		</div>
		<?php
		include 'nav.php';
		?>

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

				//restringir el tamaño máximo del texto en el input de ubicación
				$('[name="ubicacion"]').keypress(function(event) {
					if (this.value.length >= 200) {
						return false;
					}
				});
				//restringir el tamaño máximo del texto en el input de titulo
				$('[name="titulo"]').keypress(function(event) {
					if (this.value.length >= 200) {
						return false;
					}
				});

				//restringir el tamaño máximo del texto en el input del titulo de la leyenda
				$('[name="titulo"]').keypress(function(event) {
					if (this.value.length >= 80) {
						return false;
					}
				});
				//restringir el tamaño máximo del texto en el input de la url del archivo asociado a la capa
				$('[name="archivo"]').keypress(function(event) {
					if (this.value.length >= 200) {
						return false;
					}
				});
			});
		</script>

	</body>
<?php
} else {
	//si no hay una sesión activa en la plataforma,redirige el formulario de login
	header("location:../index.php");
}
?>

	</html>