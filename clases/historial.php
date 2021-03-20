<?php
include 'conexion.php';
class Historial extends Conexion
{
    public function verHistorial()
    {
        $query = $this->conn->query("SELECT * FROM historial ORDER BY id ASC");
        return $query;
    }

    public function vaciarHistorial()
    {
        $this->conn->query("TRUNCATE TABLE historial"); //vaciar tabla
        echo '<div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Historial eliminado correctamente.</div>';						
    }

}
?>