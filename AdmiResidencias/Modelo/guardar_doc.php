<?php
    try {//Guardar documento
        $file = $_FILES['evidencia'];
        $mat = $_POST['Matricula'];
        $dir='C:/xampp/htdocs/generarword/Alumnos/'.$mat;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        move_uploaded_file($file['tmp_name'], $dir.'/'.$file['name']);
        echo '<h1>¡Documento cargado con exito!</h1><br/><br/>';
    } catch (\Throwable $th) {//En caso de falla
        echo '<h1>Error al guardar documento.</h1>';
    }
    echo '<a href="AppAlumnos.php">Regresar</a>';
?>

