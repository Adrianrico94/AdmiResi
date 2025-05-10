<?php
// Configuración de la conexión a la base de datos
$host = "localhost";
$dbname = "residencias_db";
$username = "root";
$password = "";

// Obtener los datos del formulario
$correo_institucional = $_POST['institutionalEmail'];
$contrasena = password_hash($_POST['createPassword'], PASSWORD_BCRYPT);
$nombre_empresa = $_POST['nombreEmpresa'];
$proyecto_asignado = $_POST['proyectoAsignado'];
$matricula = $_POST['matricula'];
$nombre = $_POST['nombre'];
$apellido_paterno = $_POST['apellidoPaterno'];
$apellido_materno = $_POST['apellidoMaterno'];
$carrera = $_POST['carrera'];
$telefono = $_POST['telefono'];
$observaciones = $_POST['observaciones'];

try {
    // Conectar a la base de datos
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si el correo ya existe
    $verificarCorreo = $conn->prepare("SELECT COUNT(*) FROM Usuarios WHERE correo_electronico = :correo");
    $verificarCorreo->execute([':correo' => $correo_institucional]);
    $existe = $verificarCorreo->fetchColumn();

    if ($existe > 0) {
        echo "<script>
                alert('El correo ya está registrado. Intente con otro.');
                window.location.href = 'Index.html';
              </script>";
        exit;
    }

    // Insertar en la tabla Usuarios
    $sqlUsuarios = "INSERT INTO Usuarios (nombre, apellido_paterno, apellido_materno, correo_electronico, contrasena, tipo_usuario) 
                    VALUES (:nombre, :apellido_paterno, :apellido_materno, :correo_institucional, :contrasena, 'Alumno')";
    $stmtUsuarios = $conn->prepare($sqlUsuarios);
    $stmtUsuarios->execute([
        ':nombre' => $nombre,
        ':apellido_paterno' => $apellido_paterno,
        ':apellido_materno' => $apellido_materno,
        ':correo_institucional' => $correo_institucional,
        ':contrasena' => $contrasena
    ]);
    $id_usuario = $conn->lastInsertId();

    // Insertar en la tabla Alumnos
    $sqlAlumnos = "INSERT INTO Alumnos (id_alumno, matricula, empresa, proyecto_asignado, carrera, telefono_alumno, observaciones) 
                   VALUES (:id_alumno, :matricula, :empresa, :proyecto_asignado, :carrera, :telefono, :observaciones)";
    $stmtAlumnos = $conn->prepare($sqlAlumnos);
    $stmtAlumnos->execute([
        ':id_alumno' => $id_usuario,
        ':matricula' => $matricula,
        ':empresa' => $nombre_empresa,
        ':proyecto_asignado' => $proyecto_asignado,
        ':carrera' => $carrera,
        ':telefono' => $telefono,
        ':observaciones' => $observaciones
    ]);

    // Crear directorio del alumno si no existe
    $directorio = 'C:/xampp/htdocs/generarword-Git/Alumnos/' . $matricula;
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    echo "<script>
            alert('Su registro fue realizado correctamente');
            window.location.href = 'Index.html';
          </script>";
} catch (PDOException $e) {
    die("Error en la operación: " . $e->getMessage());
}

$conn = null;
?>
