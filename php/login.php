<?php
include 'encriptar.php';
include '../clases/usuario.php';

$usuario=new Usuario();
$user=$usuario->escapar($_POST["usuario"]);

if ($_POST['contrasena']) { //si se recibe un valor de contraseña
	
	$pass=$usuario->escapar($_POST['contrasena']);
	$usuario->iniciarSesion($user,$encriptar($pass));//recibir contraseña por POST y encriptarla
	 
} else {

	$pass=$encriptar("pers_orinoquia");//encriptar pers_orinoquia como contraseña por defecto
	$usuario->restaurarContraseña($user,$pass);
	
}
?>