<?php
//procesa los datos y los divide en antiguos y nuevos en columnas
function valorHistorial($tipo, $texto)
{
    if ($tipo == 1) {
        $spl = explode(";;", $texto);
        $viejo = "Antiguos:";
        $nuevo = "Nuevos:";
        for ($i = 0; $i < count($spl); $i = $i + 2) {
            $viejo = $viejo . "<br>" . $spl[$i];
            $nuevo = $nuevo . "<br>" . $spl[$i + 1];
        }
        echo '<td>' . $viejo . '</td><td>' . $nuevo . '</td>';
    } else {
        echo '<td>' . $texto . '</td><td></td>';
    }
}
//verificar si un nuevo dato es igual o diferente al antiguo
function cambios($old, $new, $n)
{
    if (!($old == $new)) {
        $n = $n . $old . ";;" . $new . ";;";
    }
    return $n;
}
function cambioValor($old, $new)
{
    if (!($old == $new)) {
        return true;
    }
}
?>