    <?php

    class Conexion
    {

        protected $conn;
        /*Datos de conexion a la base de datos*/
        private $db_host = "localhost";
        private $db_user = "mapas_pers";
        private $db_pass = "pers_orinoquia";
        private $db_name = "pers_re";

        function __construct()
        {
            $this->conectar();
        }

        //METODO PARA REALIZAR LA CONEXION A LA BASE DE DATOS
        public function conectar()
        {
            try {
                $this->conn = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
                $this->conn->set_charset("utf8");
            } catch (Exception $e) {
                echo "<h2>No me pude conectar con la Base de Datos...<br></h2>" . $e->getMessage();
            }
        }

        public function getCon()
        {
            return $this->conn;
        }

        //cerrar conexion
        public function close_db()
        {
            $this->conn->close();
        }

        //Obtener la cantidad de recursos de una tabla
        public function nX($tabla)
        {
            $sql = $this->conn->query("SELECT COUNT(*) as n FROM $tabla");
            $id = $sql->fetch_assoc();
            $sql->close();//liberar los datos almacenados en memoria de la consulta
            return $id['n'];
        }

        //Consultar nombre de un recurso
        public function getNrec($nik)
        {
            try {
                $sql = $this->conn->prepare("SELECT `nombre` FROM recursos WHERE id=?");
                $sql->bind_param('i', $nik);
                $sql->execute(); //ejecutar sentencia preparada con los parametros indicados
                $result = $sql->get_result();
                $row = $result->fetch_assoc();
                $sql->close();//liberar los datos almacenados en memoria de la consulta
                return $row['nombre'];
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        }

        //Consultar nombre de un departamento
        public function getNdep($nik)
        {
            try {
                $selectl = $this->conn->prepare("SELECT `nombre` FROM departamentos WHERE id=?");
                $selectl->bind_param('i', $nik);
                $selectl->execute(); //ejecutar sentencia preparada con los parametros indicados
                $result = $selectl->get_result();
                $row = $result->fetch_assoc();
                $selectl->close();//liberar los datos almacenados en memoria de la consulta
                return $row['nombre'];
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        }

        //agregar los nombres de todos los recursos a selector
        public function getNrecs($nik)
        {
            $sql = $this->conn->query("SELECT id,nombre FROM recursos");
            while ($row = $sql->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '"';
                if ($row['id'] == $nik) {
                    echo ' selected';
                }
                echo '>' . $row['nombre'] . '</option>';
            }
            $sql->close();//liberar los datos almacenados en memoria de la consulta
        }
        //agregar los nombres de todos los departamentos a selector
        public function getNdeps($nik)
        {
            $sql = $this->conn->query("SELECT id,nombre FROM departamentos");
            while ($row = $sql->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '"';
                if ($row['id'] == $nik) {
                    echo ' selected';
                }
                echo '>' . $row['nombre'] . '</option>';
            }
            $sql->close();//liberar los datos almacenados en memoria de la consulta
        }

        //consultar tabla de recursos
        function addRec()
        {
            $sql = $this->conn->query("SELECT id,nombre FROM recursos");
            while ($row = $sql->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '">' . $row['nombre'] . '</option>';
            }
            $sql->close();//liberar los datos almacenados en memoria de la consulta
        }
        //consultar tabla de departamentos
        function addDep()
        {
            $sql = $this->conn->query("SELECT id,nombre FROM departamentos");
            while ($row = $sql->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '">' . $row['nombre'] . '</option>';
            }
            $sql->close();//liberar los datos almacenados en memoria de la consulta
        }

        //Consultar informacion de un usuario
        public function informacionUsuario()
        {
            $user = $_SESSION['id'];
            $sql1 = $this->conn->prepare("SELECT `id`,`nombres`,`apellidos`,`usuario`,`fecha` FROM usuarios WHERE `id`=?"); //consultar información del usuario que inició sesión
            $sql1->bind_param('i', $user); //agregar variables a la sentencia preparada
            $sql1->execute(); //ejecutar sentencia preparada con los parametros indicados
            $result = $sql1->get_result();
            $datos = $result->fetch_assoc();
            $sql1->close();//liberar los datos almacenados en memoria de la consulta
            return $datos;
        }

        //agregar una acción al historial
        public function agregarHistorial($sql)
        {
            $this->conn->query($sql);
        }

        //escapar caracteres especiales
        public function escapar($txt)
        {
            return $this->conn->real_escape_string($txt);
        }
    }
    ?>
