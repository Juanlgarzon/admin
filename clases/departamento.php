    <?php
    include 'conexion.php';
    class Departamento extends Conexion
    {
    

        public function listarDepartamentos()
        {
            $query = $this->conn->query("SELECT * FROM departamentos ORDER BY id ASC");
            return $query;
        }

        public function consultarDepartamento($id){
            $nik = $this->escapar($id); //guardar valor del id recibido
            $sql1 = $this->conn->prepare( "SELECT * FROM departamentos WHERE id=?"); //consultar la existencia de un departamento con el id recibido
            $sql1->bind_param('i', $nik);
            $sql1->execute(); //ejecutar sentencia preparada con los parametros indicados
            $result = $sql1->get_result();        
            $sql1->close();//liberar los datos almacenados en memoria de la consulta
            return $result;
        }

        public function agregarDepartamento($nomb)
        {
            try {
                $fecha = date("Y-m-d");
                date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
                $hora = date("h:i A");
                $nom = $_SESSION['name'];
                $nombre = $this->escapar($nomb);//Escapar caracteres especiales
                //insertar nuevo departamento a la tabla de departamentos
                $sql =  "INSERT INTO `departamentos`(`nombre`) VALUES (?)";
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);// para activar reportar error en secuencias simples (query)
                $this->conn->autocommit(FALSE); //preparar una transaccion
                $insert = $this->conn->prepare($sql);
                $insert->bind_param('s', $nombre); //agregar variables a la sentencia preparada
                $insert->execute(); //ejecutar sentencia preparada con los parametros indicados
                //agregar acción de agregar un departamento al historial de acciones de usuarios
                $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`) VALUES ('$nom','Agregó un departamento','$nombre','$fecha','$hora')");
                     
                echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Los datos han sido guardados con éxito.</div>';            
                $this->conn->commit();
                $insert->close();//liberar los datos almacenados en memoria de la consulta
            } catch (Exception $e) { //si hay error, revierte la transaccion
                $this->conn->rollback();						
                echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. No se pudo guardar los datos !</div>';
            }
        }

        public function eliminarDepartamento($id)
        {
            $result = $this->consultarDepartamento($id); //consultar la existencia de un departamento con el id recibido			  
            if ($result->num_rows == 0) {
                echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error, no se encontraron datos.</div>';
            } else {
                try {
                    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // activar el reportar error en secuencias simples (query)
                    $this->conn->autocommit(FALSE); //preparar una transaccion
                    $row = $result->fetch_assoc();
                    $delete = $this->conn->prepare("DELETE FROM departamentos WHERE id=?"); //eliminar el departamento con el id recibido
                    $delete->bind_param('i', $id);
                    $delete->execute(); //ejecutar sentencia preparada con los parametros indicados
                    $delete->close();//liberar los datos almacenados en memoria de la consulta	
                    //guardar los valores eliminados para el historial
                    $nombre = $row['nombre'];
                    $fecha = date("Y-m-d");
                    date_default_timezone_set("America/Bogota");
                    $hora = date('h:i A');
                    $nom = $_SESSION['name'];                
                    //agregar acción de eliminar departamento al historial de acciones de usuarios
                    $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`) VALUES ('$nom','Eliminó un departamento:','$nombre','$fecha','$hora')");                                                   
                    $this->conn->commit();
                    echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Datos eliminados correctamente.</div>';
                } catch (Exception $e) {
                    $this->conn->rollback();
                    //mensaje de error
                    echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Error, no se pudo eliminar los datos.</div>';
                }
            }
        }

        
        public function actualizarDepartamento($id,$datos,$lista)
        {
            
            try {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);// para activar reportar error en secuencias simples (query)
                $this->conn->autocommit(FALSE); //preparar una transaccion
                $qry = "UPDATE departamentos SET `nombre`=? WHERE `id`=?";
                $update = $this->conn->prepare($qry);
                $update->bind_param('si', $datos['nombre'], $id); //agregar variables a la sentencia preparada
                $update->execute(); //ejecutar sentencia preparada con los parametros indicados
                $fecha = date("Y-m-d");
                date_default_timezone_set("America/Bogota"); //ajustar horario de reloj a Colombia
                $hora = date('h:i A');
                $nom = $_SESSION['name'];
                $n = "";  //variable para almacenar los cambios respecto a la información anterior
                $n = cambios($datos['old'], $datos['nombre'], $n); //comparar valores nuevos con antiguos
                //agregar acción de modificar un departamento al historial de acciones de usuarios
                $this->agregarHistorial("INSERT INTO `historial`(`usuario`, `accion`, `valor`, `fecha`, `hora`,`tipo`) VALUES ('$nom','Modificó un departamento','$n','$fecha','$hora',1)");
                $this->conn->commit();
                $update->close();//liberar los datos almacenados en memoria de la consulta                
                //recargar información para enviar mensaje de éxito
                header("Location: dr-edit.php?lista=" . $lista . "&nik=" . $id . "&ops=succ");
            } catch (Exception $e) { //si hay error, revierte la transaccion
                $this->conn->rollback();
                echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. No se pudo guardar los datos !</div>';
            }
        }
    }
    ?>