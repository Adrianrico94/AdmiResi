<?php
// Conectar a la base de datos
$conexion = new mysqli("localhost", "root", "", "residencias_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Capturar dato enviado desde el formulario (puede ser ID o nombre)
$id_o_nombre = $_POST['id_o_nombre_empresa'];

// Verificar si el dato es numérico (asumimos que es ID), sino será nombre
if (is_numeric($id_o_nombre)) {
    // Eliminación por ID
    $sql = "DELETE FROM empresa WHERE id_empresa = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_o_nombre);
} else {
    // Eliminación por nombre (puede ser parcial, aquí exacto)
    $sql = "DELETE FROM empresa WHERE nombre_empresa = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $id_o_nombre);
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Empresa eliminada correctamente'); window.location.href = 'AppSuperUsuarios.php';</script>";
    } else {
        echo "<script>alert('No se encontró ninguna empresa con ese ID o nombre'); window.location.href = 'AppSuperUsuarios.php';</script>";
    }
} else {
    echo "Error al eliminar la empresa: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>
