<?php
$n = trim($_POST['control']);
$dir = realpath("C:/xampp/htdocs/generarword-Git/Alumnos/$n");

if ($dir && is_dir($dir)) {
    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_file($fullPath)) {
            // Usamos escapeshellarg para evitar caracteres conflictivos
            $command = 'start "" ' . escapeshellarg($fullPath);
            shell_exec($command);
            echo "Abriendo: $file";
            exit;
        }
    }
    echo "No se encontraron archivos en la carpeta.";
} else {
    echo "Carpeta no encontrada.";
}
?>