<?php

session_start(); //reanudar la sesion existente
if (isset($_SESSION['id'])) { //verificar que un usuario tiene una sesión activa en la plataforma
	include '../php/inactividad.php';
	expirar(); //verificar que hubo actividad en los ultimos 10 minutos
	include '../clases/recurso.php';
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Datos de recursos</title>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="http://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
		<link href="../css/style_nav.css" rel="stylesheet">
		<link href="../css/style_ini.css" rel="stylesheet">
		<link rel="icon" type="image/png" href="../../img/icons/PERS_icon.png" />

	</head>

	<body>
		<div class="loader"></div>
		<div class="web-page">
			<div class="content">
				<h2>Lista de recursos</h2>
				<a href="dr-add.php?lista=2" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>Agregar un recurso </a>

				<hr />
				<?php
				$tabla = new Recurso();
				if (isset($_GET['del']) && ($_GET['del']) == 'y') {
					$tabla->eliminarRecurso($_GET["nik"]);
				}
				?>

				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Nombre</th>
								<th>Acciones</th>

							</tr>
						</thead>
						<?php
						$sql2 = $tabla->listarRecursos(); //consultar la tabla de recursos
						if ($sql2->num_rows == 0) {
							echo '<tr><td colspan="8">No hay datos.</td></tr>';
						} else {
							//agregar información a la tabla
							while ($row = $sql2->fetch_assoc()) {
								echo '
						<tr>
							
							<td>' . $row['nombre'] . '</td>
                          
							
							<td>

								<a href="dr-edit.php?lista=2&nik=' . $row['id'] . '" title="Editar datos" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
								<button value="' . $row['id'] . ';;' . $row['nombre'] . '" title="Eliminar" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>
							</td>
						</tr>
						';
							}
						}
						$sql2->close();
						?>
					</table>
				</div>
			</div>
		</div>
		<?php include 'nav.php'; ?>

		<script type="text/javascript">
			$(document).ready(function() {
				elementos(); // cargar funcionalidades del menu lateral

				$(".btn-danger").click(function() {
					let texto = $(this).val().split(";;");
					//confirmar el eliminar un recurso
					Swal.fire({
						title: 'Esta seguro?',
						html: "<h4>Esto eliminará el recurso '" + texto[1] + "' y no es reversible!</h4>",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						confirmButtonText: 'Si, eliminar!',
						cancelButtonText: 'No, cancelar!'

					}).then((result) => {
						if (result.isConfirmed) {
							//mensaje de éxito
							window.location = "recursos.php?del=y&nik=" + texto[0];
						}
					})
				});
				//aplicar plugin datatables a la tabla para filtrar, paginar y ordenarla
				$(".table").DataTable({
					"pagingType": "simple_numbers",
					"autoWidth": false,
					"ordering": false
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