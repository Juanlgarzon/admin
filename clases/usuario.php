<?php
include 'conexion.php';
class Usuario extends Conexion
{
    public function listarUsuarios()
    {
        $query = $this->conn->query("SELECT * FROM usuarios WHERE `usuario`!='admin' ORDER BY id ASC");
        return $query;
    }

    public function consultarUsuario($id)
    {
        $nik = $this->escapar($id); //guardar valor del id recibido
        $sql1 = $this->conn->prepare("SELECT * FROM usuarios WHERE id=?"); //consultar la existencia de un departamento con el id recibido
        $sql1->bind_param('i', $nik);
        $sql1->execute(); //ejecutar sentencia preparada con los parametros indicados 
        $result = $sql1->get_result();
        $sql1->close();//liberar los datos almacenados en memoria de la consulta
        return $result;
    }


    public function agregarUsuario($datos){       
        $sql1 = $this->conn->prepare("SELECT id FROM usuarios WHERE usuario=?"); //consultar la existencia de un usuario que ya exista
        $sql1->bind_param('s', $datos['usuario']);
        $sql1->execute(); //ejecutar sentencia preparada con los parametros indicados 
        $result = $sql1->get_result();
        $sql1->close();//liberar los datos almacenados en memoria de la consulta
        if ($result->num_rows > 0) {
            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. Ya existe un usuario llamado '.$datos['usuario'].' !</div>';
        }else{
        try {
            //si se intenta cambiar el nombre a admin, emite un error para cancelar la operacion
            if ($datos['usuario'] == 'admin') {
                throw new Exception();
            }
            $fecha = date("Y-m-d");
            date_default_timezone_set("America/Bogota");
            $hora = date('h:i A');
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // para activar reportar error en secuencias simples (query)
            $this->conn->autocommit(FALSE); //iniciar transaccion a la base de datos
            $insert = $this->conn->prepare("INSERT INTO `usuarios`(`nombres`, `apellidos`, `usuario`, `contrasena`, `fecha`) VALUES (?,?,?,?,?)");
            $insert->bind_param('sssss', $datos['nombres'], $datos['apellidos'], $datos['usuario'], $datos['contrasena'], $fecha); //agregar variables a la sentencia preparada
            $insert->execute(); //ejecutar sentencia preparada con los parametros indicados                        
            $n = "Nombres: " . $datos['nombres'] . " ".$datos['apellidos']."<br>Usuario: " . $datos['usuario'];
            $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`) VALUES ('admin','Creó un usuario','$n','$fecha','$hora')");
            echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Los datos han sido guardados con éxito.</div>';
            $insert->close();//liberar los datos almacenados en memoria de la consulta
            $this->conn->commit();
        } catch (Exception $e) { //si hay error, revierte la transaccion
            $this->conn->rollback();
            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. No se pudo guardar los datos !</div>';
        }
    }
    }

    public function actualizarUsuario($nuevos, $antiguos, $nik)
    {
        try {
            //generar una excepcion si se intenta agregar una cuenta con el nombre administrador
            if ($nuevos['usuario'] == 'admin' && $antiguos['usuario'] != 'admin') {
                throw new Exception();                
            //generar una excepcion si se intenta modificar el nombre del usuario administrador
            }else if($antiguos['usuario'] == 'admin' && $nuevos['usuario'] != 'admin'){
                throw new Exception();
            }
            $update = $this->conn->prepare("UPDATE `usuarios` SET `usuario`=?, `nombres`=?, `apellidos`=?, `contrasena`=? WHERE `id`=?");
            $update->bind_param('ssssi', $nuevos['usuario'], $nuevos['nombres'], $nuevos['apellidos'], $nuevos['contrasena'], $nik); //agregar variables a la sentencia preparada
            $update->execute(); //ejecutar sentencia preparada con los parametros indicados 
            $n = "";
            $fecha = date("Y-m-d");
            date_default_timezone_set("America/Bogota");
            $hora = date('h:i A');
            $usu = $_SESSION['name'];

            //comparar valores nuevos con antiguos del usuario
            $n = cambios($antiguos['nombres'], $nuevos['nombres'], $n);
            $n = cambios($antiguos['apellidos'], $nuevos['apellidos'], $n);
            $n = cambios($antiguos['usuario'], $nuevos['usuario'], $n);
            $_SESSION['name'] = $nuevos['usuario'];

            if (cambioValor(($nuevos['contrasena']), ($antiguos['contrasena']))) {
                $n = $n . "Cambió su contraseña";
            }
            //agregar acción de editar usuario al historial de acciones de usuarios
            $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`,`tipo`) VALUES ('$usu','Modificó su perfil','$n','$fecha','$hora',1)");
            $update->close();//liberar los datos almacenados en memoria de la consulta
            $this->conn->commit();
            //recargar información para enviar mensaje de éxito
            header("Location: usu-adedit.php?nik=" . $nik . "&ops=succ");
        } catch (Exception $e) { //si hay error, revierte la transaccion
            $this->conn->rollback();
            header("Location: usu-adedit.php?nik=" . $nik . "&ops=erro");
        }
    }

    public function eliminarUsuario($id)
    {
        $result = $this->consultarUsuario($id); //consultar la existencia de un usuario con el id recibido			         
        if ($result->num_rows == 0) {
            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> No se encontraron datos.</div>';
        } else {

            try {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // para activar reportar error en secuencias simples (query)
                $this->conn->autocommit(FALSE); //preparar una transaccion
                $row = $result->fetch_assoc();
                //guardar los valores eliminados para el historial							
                $fecha = date("Y-m-d");
                date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
                $hora = date('h:i A');
                $user = $row['usuario'];
                $delete = $this->conn->prepare("DELETE FROM usuarios WHERE id=?"); //eliminar el usuario con el id recibido
                $delete->bind_param('i', $id);
                $delete->execute(); //ejecutar sentencia preparada con los parametros indicados 
                //agregar acción de eliminar capa al historial de acciones de usuarios
                $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`) VALUES ('admin','Eliminó un usuario','$user','$fecha','$hora')");
                $this->conn->commit();
                $delete->close();//liberar los datos almacenados en memoria de la consulta
                echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Datos eliminados correctamente.</div>';
            } catch (Exception $e) {
                $this->conn->rollback();
                echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Error, no se pudo eliminar los datos.</div>';
            }
        }
    }
    //Iniciar sesion en el sistema
    public function iniciarSesion($user, $pass)
    {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // activar para reportar error en secuencias simples (query)
            $sql = $this->conn->prepare("SELECT id,usuario from usuarios where usuario=? and contrasena=?"); //consultar usuario 
            $sql->bind_param('ss', $user, $pass);
            $sql->execute(); //ejecutar sentencia preparada con los parametros indicados 
            $result = $sql->get_result();
            $sql->close();//liberar los datos almacenados en memoria de la consulta

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                session_start(); //iniciar una sesion en el navegador				
                $_SESSION['id'] = $row['id']; //guardar id de usuario en la sesion iniciada
                $_SESSION['access'] = time(); //guardar tiempo de actividad

                if ($user == 'admin') {                    
                    $_SESSION['name'] = 'admin'; //nombre de usuario en la sesion iniciada de admin
                } else {
                    $_SESSION['name'] = $row['usuario']; //nombre de usuario en la sesion iniciada 
                    
                }
                $nom = $_SESSION['name'];
                $fecha = date("Y-m-d");
                date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
                $hora = date('h:i A');
                $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`,  `fecha`, `hora`) VALUES ('$nom','Inició sesión','$fecha','$hora')"); //agregar accion de iniciar sesion al historial de acciones de usuarios
                $result->close();//liberar los datos almacenados en memoria de la consulta
                $this->close_db();
                echo 0; //retornar en caso de exito
            } else {
                echo 1; //retornar en caso de error
            }
            
        } catch (Exception $e) {
            $this->conn->rollback();
            echo 1; //retornar en caso de error
        }
    }
    //Cerrar sesion en el sistema
    public function cerrarSesion()
    {
        session_start(); //reanudar la sesion existente
        $fecha = date("Y-m-d");
        date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
        $hora = date('h:i A');
        $usuario = $_SESSION['name'];        
        $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`,  `fecha`, `hora`) VALUES ('$usuario','Cerró sesión','$fecha','$hora')"); //agregar accion de cerrar sesion al historial de acciones de usuarios
        session_destroy(); //destruir sesion actual
        unset($_SESSION['id	'], $_SESSION['name']); //vaciar variables = nulas	
        $this->close_db();
        header("location:../index.php"); //redirigir al formulario de inicio
    }
    //Restaurar la contraseña de un usuario
    public function restaurarContraseña($user, $pass)
    {
        $sql = $this->conn->prepare("SELECT id,usuario from usuarios where usuario= ?"); //consultar la existencia del usuario
        $sql->bind_param('s', $user);
        $sql->execute(); //ejecutar sentencia preparada con los parametros indicados
        $result = $sql->get_result();
        $sql->close();//liberar los datos almacenados en memoria de la consulta
        if ($result->num_rows > 0) {
            try {
                $row = $result->fetch_assoc();                
                $nom = $row['usuario'];
                $_SESSION['name'] = $nom;
                $nid = $row['id'];
                $this->conn->autocommit(FALSE);
                $this->conn->query("UPDATE `usuarios` SET `contrasena`='$pass' WHERE `id`='$nid'"); //actualizar contraseña de usuario 
                $fecha = date("Y-m-d");
                date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
                $hora = date('h:i A');
                $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`,  `fecha`, `hora`) VALUES ('$nom','Restauró su contraseña','$fecha','$hora')"); //agregar accion de restaurar contraseña al historial de acciones de usuarios
                $this->conn->commit();
                $result->close();//liberar los datos almacenados en memoria de la consulta
                $this->close_db();
                echo 0; //retornar en caso de exito
            } catch (Exception $e) {
                $this->conn->rollback();
                echo 1; //retornar en caso de error
            }
        } else {
            echo 1; //retornar en caso de no encontrar usuario
        }
       
    }
}
?>