<?php
include 'conexion.php';


class Capa extends conexion
{
    public function listarCapas()
    {
        $query = $this->conn->query("SELECT * FROM capas ORDER BY id ASC");
        return $query;
    }

    public function consultarCapa($id)
    {
        $nik = $this->escapar($id); //guardar valor del id recibido
        $sql1 = $this->conn->prepare("SELECT * FROM capas WHERE id=?"); //consultar la existencia de un departamento con el id recibido
        $sql1->bind_param('i', $nik);
        $sql1->execute(); //ejecutar sentencia preparada con los parametros indicados //ejecutar sentencia preparada con los parametros indicados
        $result = $sql1->get_result();
        $sql1->close(); //liberar los datos almacenados en memoria de la consulta
        return $result;
    }

    public function agregarCapa($datos)
    {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // para activar reportar error en secuencias simples (query)
            $this->conn->autocommit(FALSE); //iniciar transaccion a la base de datos
            $insert = $this->conn->prepare("INSERT INTO `capas`(`ubicacion`, `titulo`, `ttl_lynd`, `url_archivo`, `dep`, `rec`) VALUES (?,?,?,?,?,?)"); //preparar consultar
            $insert->bind_param('ssssii', $datos['ubicacion'], $datos['titulo'], $datos['ttl'], $datos['archivo'], $datos['dep'], $datos['rec']); //agregar variables a la sentencia preparada
            $insert->execute(); //ejecutar sentencia preparada con los parametros indicados //ejecutar sentencia preparada con los parametros indicados
            $fecha = date("Y-m-d");
            date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
            $hora = date("h:i A");
            $nom = $_SESSION['name']; //obtener nombre de usuario de la sesión activa
            $recu = $this->getNrec($datos['rec']); //obtener nombre de recurso
            $depa = $this->getNdep($datos['dep']); //obtener nombre de departamento 
            $ubicacion=$datos['ubicacion'];
            $titulo=$datos['titulo'];
            $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`) VALUES ('$nom','Agregó una capa al recurso $recu del departamento $depa','- $ubicacion<br>- $titulo','$fecha','$hora')");					
            echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Los datos han sido guardados con éxito.</div>';
            $insert->close(); //liberar los datos almacenados en memoria de la consulta
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. No se pudo guardar los datos !</div>';
        }
    }

    public function actualizarCapa($nuevos, $antiguos, $id)
    {
        try {
            //actualizar capa según su id
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // para activar reportar error en secuencias simples (query)
            $this->conn->autocommit(FALSE); //iniciar transaccion a la base de datos
            $update = $this->conn->prepare("UPDATE `capas` SET `ubicacion`=?,`titulo`=?,`ttl_lynd`=?,`url_archivo`=?,`dep`=?,`rec`=?  WHERE `id`=?");
            $update->bind_param('ssssiii', $nuevos['ubicacion'], $nuevos['titulo'], $nuevos['ttl'], $nuevos['archivo'], $nuevos['dep'], $nuevos['rec'], $id); //agregar variables a la sentencia preparada
            $update->execute(); //ejecutar sentencia preparada con los parametros indicados //ejecutar sentencia preparada con los parametros indicados
            $fecha = date("Y-m-d");
            date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
            $hora = date('h:i A');
            $nom = $_SESSION['name'];
            $n = ""; //variable para almacenar los cambios respecto a la información anterior

            /* los cambios son anidados con ";;", para luego organizarlos en columnas
            diferentes con los antiguos valores a la izquierda y los nuevos a la derecha
             */

            //comparar valores nuevos con antiguos
            $n = cambios($antiguos['ubicacion'], $nuevos['ubicacion'], $n);
            $n = cambios($antiguos['titulo'], $nuevos['titulo'], $n);
            $n = cambios($antiguos['ttl'], $nuevos['ttl'], $n);
            $n = cambios($antiguos['archivo'], $antiguos['archivo'], $n);

            /** comparar los cambios de los valores */
            $recu = $this->getNrec($antiguos['rec']); //recurso nuevo
            $depa = $this->getNdep($antiguos['dep']); //departamento nuevo
            $n = cambios($depa, $this->getNdep($nuevos['rec']), $n); //comparar recurso nuevo
            $n = cambios($recu, $this->getNrec($nuevos['dep']), $n); //comparar departamento nuevo

            //agregar acción de actualizar capa al historial de acciones de usuarios
            $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`,`tipo`) VALUES ('$nom','Modificó una capa del recurso $recu del departamento $depa','$n','$fecha','$hora',1)");
            $update->close(); //liberar los datos almacenados en memoria de la consulta
            $this->conn->commit();
            //recargar información para enviar mensaje de éxito
            header("Location: capas-edit.php?nik=" . $id . "&ops=succ");
        } catch (Exception $e) { //si hay error, revierte la transaccion
            $this->conn->rollback();
            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error, no se pudo guardar los datos.</div>';
        }
    }
    public function eliminarCapa($id)
    {
        $result = $this->consultarCapa($id); //consultar la existencia de una capa con el id recibido			         
        if ($result->num_rows == 0) {
            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> No se encontraron datos.</div>';
        } else {
            try {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);// para activar reportar error en secuencias simples (query)
                $this->conn->autocommit(FALSE); //preparar una transaccion
                $delete = $this->conn->prepare("DELETE FROM capas WHERE id=?"); //eliminar la capa con el id recibido
                $delete->bind_param('i', $id);
                $delete->execute(); //ejecutar sentencia preparada con los parametros indicados //ejecutar sentencia preparada con los parametros indicados
                //guardar los valores eliminados para el historial
                $row = $result->fetch_assoc();
                $ubicacion = $row['ubicacion'];
                $titulo = $row['titulo'];
                $fecha = date("Y-m-d");
                date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
                $hora = date('h:i A');
                $nom = $_SESSION['name'];
                $recu = $this->getNrec($row['rec']);
                $depa = $this->getNdep($row['dep']);
                //agregar acción de eliminar capa al historial de acciones de usuarios
                $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`) VALUES ('$nom','Eliminó una capa del recurso $recu del departamento $depa','- $ubicacion<br>- $titulo','$fecha','$hora')");
                $this->conn->commit();
                $delete->close(); //liberar los datos almacenados en memoria de la consulta
                //mensaje de éxito								
                echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Datos eliminados correctamente.</div>';
            } catch (Exception $e) {
                $this->conn->rollback();
                //mensaje de error
                echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Error, no se pudo eliminar los datos.</div>';

            }



        }
    }
}
?>