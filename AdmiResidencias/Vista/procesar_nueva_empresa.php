<?php
// Conectar a la base de datos
$conexion = new mysqli("localhost", "root", "", "residencias_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Capturar datos del formulario
$nombre = $_POST['nombre_empresa'];
$correo = $_POST['correo_empresa'];
$contacto = $_POST['contacto_empresa'];
$tutor = $_POST['tutor_asignado'];
$horario = $_POST['horario_asistencia'];
$dias = $_POST['dias_asistencia'];

// Verificar si la empresa ya existe por su nombre
$verificar = $conexion->prepare("SELECT id_empresa FROM empresa WHERE nombre_empresa = ?");
$verificar->bind_param("s", $nombre);
$verificar->execute();
$verificar->store_result();

if ($verificar->num_rows > 0) {
    echo "<script>alert('La empresa ya existe.'); window.location.href = 'index.php';</script>";
} else {
    // Insertar en la tabla empresa
    $sql = "INSERT INTO empresa (
        nombre_empresa, correo_empresa, contacto_empresa,
        tutor_asignado, horario_asistencia, dias_asistencia
    ) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $correo, $contacto, $tutor, $horario, $dias);

    if ($stmt->execute()) {
        echo "<script>alert('Empresa registrada correctamente'); window.location.href = 'AppSuperUsuarios.php';</script>";
    } else {
        echo "Error al registrar la empresa: " . $stmt->error;
    }

    $stmt->close();
}

$verificar->close();
$conexion->close();
?>
