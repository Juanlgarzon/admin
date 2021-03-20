<?php
session_start();//reanudar la sesion existente
if (isset($_SESSION['id'])) { //verificar que un usuario tiene una sesión activa en la plataforma
	include '../php/inactividad.php';
	expirar(); //verificar que hubo actividad en los ultimos 10 minutos
	include '../clases/capa.php';
	
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Agregar capa</title>

		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<link href="../css/style_nav.css" rel="stylesheet">
		<link href="../css/style_ini.css" rel="stylesheet">
		<link rel="icon" type="image/png" href="../../img/icons/PERS_icon.png" />
	</head>

	<body>
		<div class="loader"></div>
		<div class="web-page">
			<div class="content">
				<h2>Lista de capas &raquo; Agregar capa</h2>
				<hr />
				<?php
				$tabla=new Capa();

				if (isset($_POST['add'])) {
					$ubicacion = $tabla->escapar($_POST["ubicacion"]); //Escapar caracteres especiales
					$titulo = $tabla->escapar($_POST["titulo"]); //Escapar caracteres especiales
					$ttl = $tabla->escapar($_POST["ttl"]); //Escapar caracteres especiales
					$archivo = $tabla->escapar($_POST["archivo"]); //Escapar caracteres especiales
					$dep = $tabla->escapar($_POST["dep"]); //Escapar caracteres especiales
					$rec = $tabla->escapar($_POST["rec"]); //Escapar caracteres especiales
					$datos = array(
						"ubicacion" => $ubicacion,
						"titulo" => $titulo,
						"ttl" => $ttl,
						"archivo" => $archivo,
						"dep" => $dep,
						"rec" => $rec
					);
					$tabla->agregarCapa($datos);					
				}

				?>
				<form class="form-horizontal" action="" method="post" onsubmit="return checkSubmit();">
					<div class="form-group">
						<label class="col-sm-3 control-label">Ubicacion</label>
						<div class="col-sm-4">
							<input type="text" name="ubicacion" class="form-control" placeholder="Ubicacion" title="Debe ingresar la localizacion de la capa en Geoserver del espacio PERS" required>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Titulo de la capa</label>
						<div class="col-sm-4">
							<input type="text" name="titulo" class="form-control" placeholder="Titulo" title="Debe ingresar un nombre de la capa a mostrar" required>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Titulo de la leyenda</label>
						<div class="col-sm-4">
							<input type="text" name="ttl" class="form-control" placeholder="Titulo de leyenda">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Archivo asociado</label>
						<div class="col-sm-4">
							<input type="text" name="archivo" class="form-control" placeholder="Archivo">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Departamento</label>
						<div class="col-sm-4">
							<select name="dep" class="selectpicker">
								<!--agregar los nombres de todos los departamentos a selector-->
								<?php $tabla->addDep(); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Recurso</label>
						<div class="col-sm-4">
							<select name="rec" class="selectpicker">
								<!--agregar los nombres de todos los recursos a selector-->
								<?php $tabla->addRec(); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">&nbsp;</label>
						<div class="col-sm-6">
							<input type="submit" name="add" class="btn btn-sm btn-primary" value="Guardar datos">
							<a href="capas.php" class="btn btn-sm btn-danger">Cancelar</a>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php include 'nav.php';
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
				$('[name="ttl"]').keypress(function(event) {
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
	//si no hay una sesión activa en la plataforma,redirige al formulario de login
	header("location:../index.php");
}
?>

	</html>