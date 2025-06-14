<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "residencias_db";

// Crear conexión con MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir los valores del formulario
$id_alumno = $_POST['id_alumno'] ?? null;
$nuevo_documento = $_POST['documento'] ?? null;

// Validar entrada
if (!$id_alumno || !$nuevo_documento) {
    echo "Faltan datos obligatorios.";
    exit;
}

// Iniciar transacción manual
$conn->begin_transaction();

try {
    // Primer UPDATE: aumentar el campo 'avance'
    $sql1 = "UPDATE alumnos SET avance = avance + 1 WHERE matricula = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $id_alumno);
    $stmt1->execute();

    // Segundo UPDATE: agregar el nuevo documento al array JSON
    $sql2 = "UPDATE alumnos SET documento = JSON_ARRAY_APPEND(documento, '$', ?) WHERE matricula = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("si", $nuevo_documento, $id_alumno);
    $stmt2->execute();

    // Confirmar cambios
    $conn->commit();
    echo "Actualización exitosa. Control: $id_alumno, número: $nuevo_documento";

} catch (Exception $e) {
    $conn->rollback();
    echo "Error al actualizar: " . $e->getMessage();
}

// Cerrar conexión
$conn->close();
?>
