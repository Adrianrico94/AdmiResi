<?php
// Configuración de conexión a la base de datos
$servidor = "localhost";
$usuario = "root";
$password = "";
$basedatos = "residencias_db";

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $password, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $id_alumno = $_POST['id_alumno'];
    $id_empresa = $_POST['id_empresa']; // Asumiendo que en tu formulario el campo se llama id_docente
    
    // Actualizar la tabla de alumnos para asignar la empresa
    $sql = "UPDATE alumnos SET id_empresa = ? WHERE id_alumno = ?";
    
    // Preparar la consulta
    $stmt = $conexion->prepare($sql);
    
    // Vincular parámetros
    $stmt->bind_param("ii", $id_empresa, $id_alumno);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "<script>alert('Registro actualizado correctamente.'); window.location.href = 'AppSuperUsuarios.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el registro: " . $stmt->error . "'); window.history.back();</script>";
    }
    
    // Cerrar la consulta
    $stmt->close();
}

// Cerrar conexión
$conexion->close();
?>