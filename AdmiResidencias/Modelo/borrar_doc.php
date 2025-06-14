<?php
define('BASE_UPLOADS', realpath(__DIR__ . '/uploads/'));

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ruta'])) {
    $archivo = $_POST['ruta'];
    $realArchivo = realpath($archivo);

    if ($realArchivo !== false && strpos($realArchivo, BASE_UPLOADS) === 0 && file_exists($realArchivo)) {
        if (unlink($realArchivo)) {
            echo "Archivo eliminado correctamente.";
        } else {
            echo "No se pudo eliminar el archivo.";
        }
    } else {
        echo "Ruta inválida.";
    }
}

?>
