<?php
session_start();
include 'datos_empresa.php'; // Aquí cargamos los datos en $empresas

// Tiempo de inactividad máximo (en segundos)
$inactive_time = 6000; // 10 minutos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive_time) {
    session_unset();
    session_destroy();
    header("Location: ../index.html");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_type'])) {
    header("Location: ../index.html");
    exit();
}

// Configuración de conexión a la base de datos 
$host = "localhost";
$user = "root";
$password = "";
$database = "residencias_db";

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);


// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Validar que se hayan recibido los datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_alumno = $_POST['id_alumno'];
    $id_docente = $_POST['id_docente'];

    // Validar que no estén vacíos
    if (empty($id_alumno) || empty($id_docente)) {
        echo "<script>alert('Por favor, completa todos los campos.'); window.history.back();</script>";
        exit();
    }

    // Verificar si el alumno ya tiene asignado un docente
    $sql_check = "SELECT * FROM asignaciones WHERE id_alumno = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_alumno);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // El alumno ya tiene un docente asignado, por lo que actualizamos la asignación
        $sql_update = "UPDATE asignaciones SET id_docente = ? WHERE id_alumno = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $id_docente, $id_alumno);

        if ($stmt_update->execute()) {
            echo "<script>alert('Asignación actualizada exitosamente.'); window.location.href = '../index.html';</script>";
        } else {
            echo "<script>alert('Error al actualizar la asignación: {$stmt_update->error}'); window.history.back();</script>";
        }
    } else {
        // El alumno no tiene docente asignado, se realiza una inserción
        $sql_insert = "INSERT INTO asignaciones (id_alumno, id_docente) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $id_alumno, $id_docente);

        if ($stmt_insert->execute()) {
            echo "<script>alert('Asignación realizada exitosamente.'); window.location.href = 'AppSuperUsuarios.php';</script>";
        } else {
            echo "<script>alert('Error al realizar la asignación: {$stmt_insert->error}'); window.history.back();</script>";
        }
    }
}



$sql = "SELECT a.*, u.nombre, u.apellido_paterno, u.apellido_materno, u.correo_electronico
        FROM Alumnos a
        JOIN Usuarios u ON a.id_alumno = u.id_usuario"; // Asegúrate de que este sea el campo correcto
$result = $conn->query($sql);




?>





<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administradores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="Styles/styleAppSuperUsuarios.css">

    <!-- icono bostrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- fin iconos  -->

    <script>
        // Función para cerrar sesión si no hay actividad del ratón
        let timeout;

        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                window.location.href = "logout.php"; // Redirige al cerrar sesión
            }, 600000); // 1 minuto = 60000 ms
        }

        // Eventos para detectar movimiento o clics
        window.onload = resetTimer;
        window.onmousemove = resetTimer;
        window.onmousedown = resetTimer;
        window.ontouchstart = resetTimer;
        window.onscroll = resetTimer;
    </script>
</head>

<body>


    <!-- Barra de navegación fija -->
    <nav class="border border-2 rounded-top-2 navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="Inicio.html">
                <img src="../Recursos/img/logo/rino.png" alt="Logo" width="70" height="40" />
            </a>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-bolder" href="#">ADMINISTRADOR DE RESIDENCIAS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder" href="#">ADMIN</a>
                </li>
            </ul>

            <form class="d-flex">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input class="form-control search-bar me-2" type="search" placeholder="Buscar"
                        aria-label="Buscar" id="buscar" />
                </div>
                <button class="btn btn-search" type="submit">Buscar</button>
            </form>

            <script>
                //Busqueda de datos
                document.getElementById("buscar").addEventListener("input", onInputChangue);

                function onInputChangue() {
                    let inputText = document.getElementById("buscar").value.toString().toLowerCase();
                    //console.log(inputText);
                    let tablaAlu = document.getElementById("alumnos");
                    let filasAlu = tablaAlu.getElementsByTagName("tr");
                    for (let i = 0; i < filasAlu.length; i++) {
                        let aluNombre = filasAlu[i].cells[1].textContent.toString().toLowerCase();
                        let aluNumero = filasAlu[i].cells[2].textContent.toString().toLowerCase();
                        let aluAP = filasAlu[i].cells[3].textContent.toString().toLowerCase();
                        let aluAM = filasAlu[i].cells[4].textContent.toString().toLowerCase();
                        if (aluNombre.indexOf(inputText) === -1 && aluNumero.indexOf(inputText) === -1 && aluAP.indexOf(inputText) === -1 && aluAM.indexOf(inputText) === -1) {
                            filasAlu[i].style.visibility = "collapse";
                        } else {
                            filasAlu[i].style.visibility = "";
                        }
                    }
                    let tablaProf = document.getElementById("profesores");
                    let filasProf = tablaProf.getElementsByTagName("tr");
                    for (let i = 0; i < filasProf.length; i++) {
                        let proNombre = filasProf[i].cells[7].textContent.toString().toLowerCase();
                        let proAP = filasProf[i].cells[8].textContent.toString().toLowerCase();
                        let proAM = filasProf[i].cells[9].textContent.toString().toLowerCase();
                        if (proNombre.indexOf(inputText) === -1 && proAP.indexOf(inputText) === -1 && proAM.indexOf(inputText) === -1) {
                            filasProf[i].style.visibility = "collapse";
                        } else {
                            filasProf[i].style.visibility = "";
                        }
                    }
                }
            </script>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item">
                    <a class="nav-link" style="color: black; font-size: 18px" href="#"><i
                            class="bi bi-bell-fill"></i></a>
                </li>
                <!-- Filtro (menú desplegable) -->

                </li>
                <li class="nav-item">
                    <a class="nav-link profile-name" href="#"><i class="bi bi-person-circle me-1"
                            style="font-size: 25px"></i>
                        Olivares López Oscar</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        Confirmar Cierre de Sesión
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas cerrar sesión?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" title="Haz clic para cancelar el cierre de sesión.">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="cerrarSesion()" title="Haz clic para cierrar de sesión y volver a la página de inicio.">Cerrar sesión</button>

                </div>
            </div>
        </div>
    </div>

    <script>
        function cerrarSesion() {

            // Redirige a logout.php para destruir la sesión
            window.location.href = 'logout.php';
        }
    </script>

    <!-- Contenedor principal con el menú lateral y el contenido -->
    <div class="d-flex">
        <!-- Sidebar estilo Teams -->
        <div class="sidebar d-flex flex-column align-items-center pt-0">
            <a href="#" class="text-center active">
                <i class="bi bi-house-door-fill"></i>
                <span>Inicio</span>
            </a>
            <!-- Enlace en el menú lateral -->
            <a href="#" class="text-center" onclick="showSection('SecAlumno', 'SecDocente')" title="Visualiza los datos de los alumnos.">
                <i class="bi bi-mortarboard-fill"></i>
                <span>Alumno</span>
            </a>
            <a href="#" class="text-center" onclick="showSection('SecDocente', 'SecAlumno')" title="Visualiza todos los dados de los profesores.">
                <i class="bi bi-person-fill"></i>
                <span>Docente</span>
            </a>
            
            <a href="#" class="text-center" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Haz clic para cerrar sesión.">
                <i class="bi bi-box-arrow-right"></i>
                <span>Salir</span>
            </a>
        </div>



        <!-- Contenido principal -->
        <div class="content pt-0 mt-2 col-10">
            <div class="text-center col-12 mt-2  border border-2 rounded p-3  text-white" style="background-color: #8a2036;">
                <h1>SECCIÓN DE ALUMNOS</h1>
            </div>

            <!-- Seccion de botones -->
            <div class="d-flex flex-wrap gap-2 pt-2 justify-content-center">

                <!-- Agregar usuario -->
                <button type="button" class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#insertarModal">
                    <i class="bi bi-person-fill-add fs-5 me-1"></i> Agregar usuario
                </button>

                <!-- Asignar Alumno -->
                <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modalAsignarAlumno">
                    <i class="bi bi-people-fill fs-5 me-1"></i> Asignar Alumno
                </button>

                <!-- Eliminar Usuarios -->
                <button type="button" class="btn btn-danger px-3" data-bs-toggle="modal" data-bs-target="#modalEliminarUsuarios">
                    <i class="bi bi-person-x-fill fs-5 me-1"></i> Eliminar Usuarios
                </button>

                <!-- Asignar Empresa -->
                <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modalAsignarEmpresa">
                    <i class="bi bi-buildings-fill fs-5 me-1"></i> Asignar Empresa
                </button>

                <!-- Agregar Empresa -->
                <button type="button" class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#AgregarEmpresa">
                    <i class="bi bi-building-fill-add fs-5 me-1"></i> Agregar Empresa
                </button>

                <!-- Eliminar Empresa -->
                <button type="button" class="btn btn-danger px-3" data-bs-toggle="modal" data-bs-target="#EliminarEmpresa">
                    <i class="bi bi-building-fill-x fs-5 me-1"></i> Eliminar Empresa
                </button>

            </div>


            <div class="container mt-3">

                <!-- Modal para insertar usuario -->
                <div class="modal fade" id="insertarModal" tabindex="-1" aria-labelledby="insertarModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="insertarModalLabel">Registrar Usuario</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Formulario de registro de usuario -->
                                <form id="formularioInsertar" action="../Controlador/insertar_usuario.php" method="POST">
                                    <!-- Campo para seleccionar tipo de usuario -->
                                    <div class="mb-3">
                                        <label for="tipo_usuario" class="form-label">Tipo de Usuario</label>
                                        <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                            <option value="">Seleccionar</option>
                                            <option value="Docente">Docente</option>
                                            <option value="Superusuario">Superusuario</option>
                                        </select>
                                    </div>

                                    <!-- Campos comunes para usuarios (nombre, correo, etc.) -->
                                    <div id="camposUsuarios">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                                            <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                            <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contrasena" class="form-label">Contraseña</label>
                                            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                        </div>
                                    </div>

                                    <!-- Campos específicos para docentes (solo se mostrarán si el tipo de usuario es Docente) -->
                                    <div id="camposDocente" style="display: none;">
                                        <div class="mb-3">
                                            <label for="clave_profesor" class="form-label">Clave del Profesor</label>
                                            <input type="text" class="form-control" id="clave_profesor" name="clave_profesor">
                                        </div>
                                        <div class="mb-3">
                                            <label for="carrera" class="form-label">Carrera</label>
                                            <input type="text" class="form-control" id="carrera" name="carrera">
                                        </div>
                                        <div class="mb-3">
                                            <label for="telefono_docente" class="form-label">Teléfono del Docente</label>
                                            <input type="text" class="form-control" id="telefono_docente" name="telefono_docente">
                                        </div>
                                        <div class="mb-3">
                                            <label for="correo_institucional" class="form-label">Correo Institucional</label>
                                            <input type="email" class="form-control" id="correo_institucional" name="correo_institucional">
                                        </div>
                                        <div class="mb-3">
                                            <label for="observaciones" class="form-label">Observaciones</label>
                                            <textarea class="form-control" id="observaciones" name="observaciones"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="horario_asignado" class="form-label">Horario Asignado</label>
                                            <input type="text" class="form-control" id="horario_asignado" name="horario_asignado">
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <button type="submit" class="btn btn-primary">Registrar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Script para cambiar campos según tipo de usuario -->
                <script>
                    document.getElementById('tipo_usuario').addEventListener('change', function() {
                        let tipoUsuario = this.value;

                        // Mostrar u ocultar campos según el tipo de usuario
                        if (tipoUsuario === 'Docente') {
                            document.getElementById('camposUsuarios').style.display = 'block'; // Muestra los campos comunes
                            document.getElementById('camposDocente').style.display = 'block'; // Muestra los campos específicos de docentes
                        } else if (tipoUsuario === 'Superusuario') {
                            document.getElementById('camposUsuarios').style.display = 'block'; // Muestra los campos comunes
                            document.getElementById('camposDocente').style.display = 'none'; // Oculta los campos de docente
                        } else {
                            document.getElementById('camposUsuarios').style.display = 'none'; // Oculta los campos comunes
                            document.getElementById('camposDocente').style.display = 'none'; // Oculta los campos de docente
                        }
                    });
                </script>

                <!-- Bootstrap JS -->
                <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>




            </div>






            <!-- Modal: Asignar Alumno -->
            <div class="modal fade" id="modalAsignarAlumno" tabindex="-1" aria-labelledby="modalAsignarAlumnoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAsignarAlumnoLabel">Asignar Profesor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="id_alumno" class="form-label">ID del Alumno</label>
                                    <input type="text" class="form-control" id="id_alumno" name="id_alumno" placeholder="Ingresa el ID del alumno" required>
                                </div>
                                <div class="mb-3">
                                    <label for="id_docente" class="form-label">ID del Profesor</label>
                                    <input type="text" class="form-control" id="id_docente" name="id_docente" placeholder="Ingresa el ID del profesor" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Asignar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>




            <!-- Modal: Eliminar Usuarios -->
            <div class="modal fade" id="modalEliminarUsuarios" tabindex="-1" aria-labelledby="modalEliminarUsuariosLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEliminarUsuariosLabel">Eliminar Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../Controlador/eliminar_usuario.php" method="POST">
                                <div class="mb-3">
                                    <label for="idUsuario" class="form-label">ID del Usuario</label>
                                    <input type="text" class="form-control" id="idUsuario" name="idUsuario" placeholder="Ingresa el ID del usuario" required>
                                </div>
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal: Asignar empresa Alumno -->


            <div class="modal fade" id="modalAsignarEmpresa" tabindex="-1" aria-labelledby="modalAsignarEmpresaLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAsignarEmpresaLabel">Asignar Empresa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="procesar_asignar_empresa.php" method="POST">
                                <div class="mb-3">
                                    <label for="id_alumno" class="form-label">ID del Alumno</label>
                                    <input type="text" class="form-control" id="id_alumno" name="id_alumno" placeholder="Ingresa el ID del alumno" required>
                                </div>
                                <div class="mb-3">
                                    <label for="id_empresa" class="form-label">ID de la Empresa</label>
                                    <input type="text" class="form-control" id="id_empresa" name="id_empresa" placeholder="Ingresa el ID de la empresa" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Asignar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>




            <!-- Agregar nueva empresa -->
            <div class="modal fade" id="AgregarEmpresa" tabindex="-1" aria-labelledby="modalAgregarEmpresaLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAgregarEmpresaLabel">Agregar Empresa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body">
                            <form id="formAgregarEmpresa" action="procesar_nueva_empresa.php" method="POST" onsubmit="return mostrarAlertaEmpresa()">

                                <div class="mb-3">
                                    <label for="nombre_empresa" class="form-label">Ingrese el nombre de la empresa</label>
                                    <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" placeholder="nombre de la empresa" required>
                                </div>

                                <div class="mb-3">
                                    <label for="correo_empresa" class="form-label"> Ingrese el correo de la empresa</label>
                                    <input type="email" class="form-control" id="correo_empresa" name="correo_empresa" placeholder="empresa@gmail.com">
                                </div>

                                <div class="mb-3">
                                    <label for="contacto_empresa" class="form-label">Ingrese el teléfono de contacto</label>
                                    <input type="tel" class="form-control" id="contacto_empresa" name="contacto_empresa" placeholder="55-15-25-35-45 ">
                                </div>

                                <div class="mb-3">
                                    <label for="tutor_asignado" class="form-label">Nombre del tutor asignado de la empresa</label>
                                    <input type="text" class="form-control" id="tutor_asignado" name="tutor_asignado" placeholder="Armando Chavez Martinez">
                                </div>

                                <div class="mb-3">
                                    <label for="horario_asistencia" class="form-label">Horario de asistencia</label>
                                    <input type="text" class="form-control" id="horario_asistencia" name="horario_asistencia" placeholder="12:00 am a 5:00 pm">
                                </div>

                                <div class="mb-3">
                                    <label for="dias_asistencia" class="form-label">Días de asistencia</label>
                                    <input type="text" class="form-control" id="dias_asistencia" name="dias_asistencia" placeholder="Lunes - viernes">
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Registrar Empresa</button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>


            <!-- Eliminar empresa -->
            <div class="modal fade" id="EliminarEmpresa" tabindex="-1" aria-labelledby="modalEliminarEmpresaLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEliminarEmpresaLabel">Eliminar Empresa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body">
                            <form id="formEliminarEmpresa" action="eliminar_Empresa.php" method="POST" onsubmit="return confirmarEliminacion()">

                                <div class="mb-3">
                                    <label for="id_o_nombre_empresa" class="form-label">Ingrese el ID o nombre de la empresa a eliminar</label>
                                    <input type="text" class="form-control" id="id_o_nombre_empresa" name="id_o_nombre_empresa" placeholder="ID o nombre de la empresa" required>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-danger">Eliminar empresa</button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>


            <!-- fin eliminar empresa -->




            <script>
                // Función para mostrar/ocultar la sección

                function showSection(showId, hideId) {
                    // Mostrar la sección seleccionada
                    const showSection = document.getElementById(showId);
                    showSection.style.display = 'block';

                    // Ocultar la sección no seleccionada
                    const hideSection = document.getElementById(hideId);
                    hideSection.style.display = 'none';
                }
            </script>
            <div class="col-12 mt-2" id="SecAlumno" >
                <div class="card rounded-3">
                    <div class="text-center my-2 mb-0 flex-grow-1 fs-6 fs-md-4">
                        <h3>ALUMNOS</h3>
                        <div class="container-fluid d-flex justify-content-center">
                            <div class="shadow-lg rounded" style="width: 100%; max-width: 1350px; margin: 0 auto;">
                                <!-- Contenedor con el scroll en la tabla -->
                                <div style="width: 100%; overflow-x: auto;">
                                    <table class="table table-bordered table-hover table-striped align-middle" style="width: 100%; max-height: 400px; overflow-y: auto;">
                                        <thead class="bg-secondary text-white" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>

                                                <th class="text-center" style="width: auto;  color: white;">ID Alumno</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 30px; padding-right: 30px; color: white;">Matrícula</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 30px; padding-right: 30px; color: white;">Nombre</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 30px; padding-right: 30px; color: white;">Apellido Paterno</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 30px; padding-right: 30px; color: white;">Apellido Materno</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 30px; padding-right: 30px; color: white;">Empresa</th>

                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 80px; padding-right: 80px; color: white;">Proyecto Asignado</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 70px; padding-right: 70px; color: white;">Carrera</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 30px; padding-right: 30px; color: white;">Ingreso</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap; padding-left: 30px; padding-right: 30px; color: white;">Egreso</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Más detalles</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 30px;color: white 50px; padding-right: 50px; color: white; ">Acciones</th>







                                            </tr>
                                        </thead>
                                        <tbody id="alumnos">
                                            <?php
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";

                                                    echo "<td class='text-center'>" . $row["id_alumno"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["matricula"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["nombre"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["apellido_paterno"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["apellido_materno"] . "</td>";
                                                    echo "<td>" . $row["empresa"] . "</td>";
                                                    echo "<td>" . $row["proyecto_asignado"] . "</td>";
                                                    echo "<td>" . $row["carrera"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["ingreso"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["egreso"] . "</td>";

                                                    echo "<td class='text-center'>";
                                                    // Botón para Ver más detalle
                                                    echo "<button class='btn btn-primary btn-sm me-1' title='Editar' data-bs-toggle='modal' data-bs-target='#ver_{$row['id_alumno']}'>
                                                            Ver más detalle
                                                        </button>";
                                                    echo "</td>";
                                                    // Botones con iconos que abren modales
                                                    echo "<td class='text-center'>";

                                                    // Botón para editar
                                                    echo "<button class='btn btn-primary btn-sm me-1' title='Editar' data-bs-toggle='modal' data-bs-target='#editModal_{$row['id_alumno']}'>
                                                            <i class='bi bi-pencil'></i>
                                                        </button>";
                                                    // Botón para eliminar
                                                    echo "<button class='btn btn-danger btn-sm' title='Eliminar' data-bs-toggle='modal' data-bs-target='#deleteModal_{$row['id_alumno']}'>
                                       <i class='bi bi-trash'></i>
                                     </button>";
                                                    echo "</td>";
                                                    echo "</tr>";

                                                    echo "</td>";


                                                    echo "</tr>";



                                                    // Modal para Mostrar
                                                    echo "<div class='modal fade' id='ver_{$row['id_alumno']}' tabindex='-1' aria-labelledby='editModalLabel_{$row['id_alumno']}' aria-hidden='true'>
                                                <div class='modal-dialog'>
                                                    <div class='modal-content' >
                                                        <div class='modal-header'style='background-color: #56212f; color: white;'>
                                                            <h5 class='modal-title' id='editModalLabel_{$row['id_alumno']}'>Editar Información</h5>
                                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                        </div>
                                                        <div class='modal-body' style='text-align: left;'>
                                                            <p><strong>Teléfono alumno:</strong> {$row['telefono_alumno']}</p>
                                                            <p><strong>Teléfono empresa:</strong> {$row['telefono_profesor_asignado']}</p>
                                                            <p><strong>Horario asignado Tecnológico:</strong> {$row['horario_asignado']}</p>
                                                            <p><strong>Descripción del proyecto:</strong></p>
                                                            <textarea readonly style='width: 100%; height: 100px; overflow-y: scroll; border: 1px solid #ccc; padding: 5px; text-align: left;'>"
                                                        . htmlspecialchars($row['observaciones']) .
                                                        "</textarea>
                                                        </div>
                                                        <div class='modal-footer' style='justify-content: center;'>
                                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal' style='width: 150px;'>Cerrar</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>";

                                                    // Modal para editar información
                                                    // Modal para editar información
                                                    echo "<div class='modal fade' id='editModal_{$row['id_alumno']}' tabindex='-1' aria-labelledby='editModalLabel_{$row['id_alumno']}' aria-hidden='true'>
                                            <div class='modal-dialog'>
                                                <div class='modal-content'>
                                                    <div class='modal-header' style='background-color: #56212f; color: white;'>
                                                        <h5 class='modal-title' id='editModalLabel_{$row['id_alumno']}'>Editar Información</h5>
                                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                    </div>
                                                    <form method='POST' action='../Controlador/editar_alumno.php'>
                                                        <div class='modal-body' style='text-align: left;'>
                                                            <input type='hidden' name='id_alumno' value='{$row['id_alumno']}'>
                                                            <div class='mb-3'>
                                                                <label for='matricula' class='form-label'>Matrícula</label>
                                                                <input type='text' class='form-control' id='matricula' name='matricula' value='{$row['matricula']}'>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='empresa' class='form-label'>Empresa</label>
                                                                <input type='text' class='form-control' id='empresa' name='empresa' value='{$row['empresa']}'>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='proyecto_asignado' class='form-label'>Proyecto Asignado</label>
                                                                <input type='text' class='form-control' id='proyecto_asignado' name='proyecto_asignado' value='{$row['proyecto_asignado']}'>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='carrera' class='form-label'>Carrera</label>
                                                                <input type='text' class='form-control' id='carrera' name='carrera' value='{$row['carrera']}'>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='ingreso' class='form-label'>Ingreso</label>
<input type='date' class='form-control' id='ingreso' name='ingreso' value='{$row['ingreso']}'>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='egreso' class='form-label'>Egreso</label>

<input type='date' class='form-control' id='egreso' name='egreso' value='{$row['egreso']}'>
                                                                </div>
                                                            <div class='mb-3'>
                                                                <label for='telefono_alumno' class='form-label'>Teléfono Alumno</label>
                                                                <input type='text' class='form-control' id='telefono_alumno' name='telefono_alumno' value='{$row['telefono_alumno']}'>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='telefono_profesor_asignado' class='form-label'>Teléfono Profesor</label>
                                                                <input type='text' class='form-control' id='telefono_profesor_asignado' name='telefono_profesor_asignado' value='{$row['telefono_profesor_asignado']}'>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='observaciones' class='form-label'>Descripción del proyecto</label>
                                                                <textarea class='form-control' id='observaciones' name='observaciones'>{$row['observaciones']}</textarea>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='horario_asignado' class='form-label'>Horario Asignado</label>
                                                                <input type='text' class='form-control' id='horario_asignado' name='horario_asignado' value='{$row['horario_asignado']}'>
                                                            </div>
                                                        </div>
                                                        <div class='modal-footer'>
                                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cerrar</button>
                                                            <button type='submit' class='btn btn-primary'>Guardar Cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>";



                                                    // Modal de eliminación
                                                    echo "<div class='modal fade' id='deleteModal_{$row['id_alumno']}' tabindex='-1' aria-labelledby='deleteModalLabel_{$row['id_alumno']}' aria-hidden='true'>
                                        <div class='modal-dialog'>
                                            <div class='modal-content'>
                                                <div class='modal-header'>
                                                    <h5 class='modal-title' id='deleteModalLabel_{$row['id_alumno']}'>Confirmar Eliminación</h5>
                                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                </div>
                                                <div class='modal-body'>
                                                    <p>¿Estás seguro de que deseas eliminar el registro del alumno con matrícula <strong>{$row['matricula']}</strong>?</p>
                                                </div>
                                                <div class='modal-footer'>
                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                                                    <form action='../Controlador/eliminar_alumno.php' method='POST'>
                                                        <input type='hidden' name='id_alumno' value='{$row['id_alumno']}'>
                                                        <button type='submit' class='btn btn-danger'>Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='13' class='text-center'>No hay registros</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4"></div> <!-- Espacio adicional en la parte inferior -->
                    </div>
                </div>
            </div>






            <?php


            // Consulta para obtener los docentes
            // Consulta para obtener los datos de Docentes junto con los datos de Usuarios relacionados por correo
            $sql = "
SELECT 
    d.id_docente,
    d.clave_profesor,
    d.carrera,
    d.telefono_docente,
    d.correo_institucional,
    d.observaciones,
    d.horario_asignado,
    u.id_usuario,
    u.nombre,
    u.apellido_paterno,
    u.apellido_materno,
    u.correo_electronico,
    u.contrasena
FROM 
    Docentes AS d
LEFT JOIN 
    Usuarios AS u
ON 
    d.correo_institucional = u.correo_electronico
";

            $result = $conn->query($sql);
            ?>

            <div class="col-12 mt-4" id="SecDocente">
                <div class="card rounded-3">
                    <div class="text-center my-2 mb-0 flex-grow-1 fs-6 fs-md-4">
                        <h3>DOCENTES</h3>
                        <div class="container-fluid d-flex justify-content-center">
                            <div class="shadow-lg rounded" style="width: 100%; max-width: 1350px; margin: 0 auto;">
                                <div style="width: 100%; overflow-x: auto;">
                                    <table class="table table-bordered table-hover table-striped align-middle" style="width: 100%; max-height: 400px; overflow-y: auto;">
                                        <thead class="bg-secondary text-white" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Id docente</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Clave Profesor</th>

                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Carrera</th>

                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Teléfono</th>

                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Correo Institucional</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Observaciones</th>
                                                <!-- <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Horario asignado</th> -->
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">ID Usuario</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Nombre</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Apellido Paterno</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Apellido Materno</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Correo Electrónico</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Contraseña</th>
                                                <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Acciones</th>


                                            </tr>
                                        </thead>
                                        <tbody id="profesores">
                                            <?php
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td class='text-center'>" . $row["id_docente"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["clave_profesor"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["carrera"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["telefono_docente"] . "</td>";
                                                    echo "<td class='text-center'>" . $row["correo_institucional"] . "</td>";
                                                    // echo "<td class='text-center'>" . $row["observaciones"] . "</td>";

                                                    echo "<td class='text-center'>" . $row["horario_asignado"] . "</td>";
                                                    echo "<td class='text-center'>" . ($row["id_usuario"] ?? "N/A") . "</td>";
                                                    echo "<td class='text-center'>" . ($row["nombre"] ?? "N/A") . "</td>";
                                                    echo "<td class='text-center'>" . ($row["apellido_paterno"] ?? "N/A") . "</td>";
                                                    echo "<td class='text-center'>" . ($row["apellido_materno"] ?? "N/A") . "</td>";
                                                    echo "<td class='text-center'>" . ($row["correo_electronico"] ?? "N/A") . "</td>";
                                                    echo "<td class='text-center'>" . ($row["contrasena"] ?? "N/A") . "</td>";
                                                    echo "<td class='text-center'>";

                                                    // Botón para editar
                                                    echo "<button class='btn btn-primary btn-sm me-1' title='Editar' data-bs-toggle='modal' data-bs-target='#editdocente_{$row['id_docente']}'>
                                                        <i class='bi bi-pencil'></i>
                                                    </button>";

                                                    // Botón para eliminar
                                                    //echo "<button class='btn btn-danger btn-sm' title='Eliminar' data-bs-toggle='modal' data-bs-target='#deletedocente_{$row['id_docente']}'>
                                                    //      <i class='bi bi-trash'></i>
                                                    //   </button>";
                                                    echo "</td>";
                                                    echo "</tr>";

                                                    // Modal para editar información
                                                    echo "<div class='modal fade' id='editdocente_{$row['id_docente']}' tabindex='-1' aria-labelledby='editModalLabel_{$row['id_docente']}' aria-hidden='true'>
                                                    <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header' style='background-color: #56212f; color: white;'>
                                                                <h5 class='modal-title' id='editModalLabel_{$row['id_docente']}'>Editar Información del Docente</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                            </div>
                                                            <form method='POST' action='../Controlador/editar_docente.php'>
                                                                <div class='modal-body' style='text-align: left;'>
                                                                    <input type='hidden' name='id_docente' value='{$row['id_docente']}'>

                                                                    <div class='mb-3'>
                                                                        <label for='clave_profesor' class='form-label'>Clave Profesor</label>
                                                                        <input type='text' class='form-control' id='clave_profesor' name='clave_profesor' value='{$row['clave_profesor']}' required>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='carrera' class='form-label'>Carrera</label>
                                                                        <input type='text' class='form-control' id='carrera' name='carrera' value='{$row['carrera']}' required>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='telefono_docente' class='form-label'>Teléfono</label>
                                                                        <input type='text' class='form-control' id='telefono_docente' name='telefono_docente' value='{$row['telefono_docente']}' required>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='correo_institucional' class='form-label'>Correo Institucional</label>
                                                                        <input type='email' class='form-control' id='correo_institucional' name='correo_institucional' value='{$row['correo_institucional']}' required>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='observaciones' class='form-label'>Observaciones</label>
                                                                        <textarea class='form-control' id='observaciones' name='observaciones' rows='3'>{$row['observaciones']}</textarea>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='horario_asignado' class='form-label'>Horario Asignado</label>
                                                                        <input type='text' class='form-control' id='horario_asignado' name='horario_asignado' value='{$row['horario_asignado']}'>
                                                                    </div>

                                                                    <hr>

                                                                    <div class='mb-3'>
                                                                        <label for='id_usuario' class='form-label'>ID Usuario</label>
                                                                        <input type='text' class='form-control' id='id_usuario' name='id_usuario' value='{$row['id_usuario']}'>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='nombre' class='form-label'>Nombre</label>
                                                                        <input type='text' class='form-control' id='nombre' name='nombre' value='{$row['nombre']}'>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='apellido_paterno' class='form-label'>Apellido Paterno</label>
                                                                        <input type='text' class='form-control' id='apellido_paterno' name='apellido_paterno' value='{$row['apellido_paterno']}'>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='apellido_materno' class='form-label'>Apellido Materno</label>
                                                                        <input type='text' class='form-control' id='apellido_materno' name='apellido_materno' value='{$row['apellido_materno']}'>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='correo_electronico' class='form-label'>Correo Electrónico</label>
                                                                        <input type='email' class='form-control' id='correo_electronico' name='correo_electronico' value='{$row['correo_electronico']}'>
                                                                    </div>
                                                                    <div class='mb-3'>
                                                                        <label for='contrasena' class='form-label'>Contraseña</label>
                                                                        <input type='password' class='form-control' id='contrasena' name='contrasena' value='{$row['contrasena']}'>
                                                                    </div>
                                                                </div>
                                                                <div class='modal-footer'>
                                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cerrar</button>
                                                                    <button type='submit' class='btn btn-primary'>Guardar Cambios</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    </div>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='6' class='text-center'>No hay docentes registrados.</td></tr>";
                                            }
                                            ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4"></div> <!-- Espacio adicional en la parte inferior -->
                    </div>
                </div>


                <!-- tabla empresas -->
                <div class="col-12 col-md-12 mt-4 " id="SecDocente">
                    <div class="card rounded-3">
                        <div class="text-center my-2 mb-0 flex-grow-1 fs-6 fs-md-4">
                            <h2>Empresas registradas</h2>
                            <div class="container-fluid d-flex justify-content-center">
                                <div class="shadow-lg rounded" style="width: 100%; max-width: 1350px; margin: 0 auto;">
                                    <div style="width: 100%; overflow-x: auto;">
                                                  <table class="table table-bordered table-hover table-striped align-middle" style="width: 100%; max-height: 400px; overflow-y: auto;">
                                        <thead class="bg-secondary text-white" style="position: sticky; top: 0; z-index: 1;">
                                                <tr>
                                                    <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 6px;color: white 50px; padding-right: 6px; color: white; ">ID Empresa</th>
                                                    <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Nombre</th>
                                                    <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Correo</th>
                                                    <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Teléfono</th>
                                                    <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 20px;color: white 50px; padding-right: 20px; color: white; ">Tutor Asignado</th>
                                                    <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 60px;color: white 50px; padding-right: 60px; color: white; ">Horario</th>

                                                    <th class="text-center" style="width: auto; white-space: nowrap;  padding-left: 50px;color: white 50px; padding-right: 50px; color: white; ">Días</th>





                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($empresas)): ?>
                                                    <?php foreach ($empresas as $fila): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($fila['id_empresa']) ?></td>
                                                            <td><?= htmlspecialchars($fila['nombre_empresa']) ?></td>
                                                            <td><?= htmlspecialchars($fila['correo_empresa']) ?></td>
                                                            <td><?= htmlspecialchars($fila['contacto_empresa']) ?></td>
                                                            <td><?= htmlspecialchars($fila['tutor_asignado']) ?></td>
                                                            <td><?= htmlspecialchars($fila['horario_asistencia']) ?></td>
                                                            <td><?= htmlspecialchars($fila['dias_asistencia']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No hay empresas registradas</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <!-- fin tabal empresas -->
        </div>


        <?php
        $conn->close();
        ?>






    </div>

    </div>





    <div class="container container-crm mt-4">


    </div>


    <!-- Footer -->
    <footer class="text-center py-0 m-0">
        <div class="container">
            <div class="row">
                <div class="col-md-4 pt-4">
                    <p style="font-size: 12px">
                        © 2024 Valdez Rico Adrian Job.
                        Todos los derechos reservados.
                    </p>
                </div>
                <div class="col-md-4" style="font-size: 14px">
                    <h6>Contacto</h6>
                    <p>
                        Av. Nopaltepec s/n Col. La Perla C.P. 54740, Cuautitlán Izcalli,
                        Estado de México
                    </p>
                    <p>Tel: (55) 58 64 31 70 - 71</p>
                </div>
                <div class="col-md-4 footer-icons pt-4 fs-5">
                    <h6>Síguenos en:</h6>
                    <a href="https://web.whatsapp.com/" class="me-2"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.facebook.com/Comunidad.Tesci" class="me-2"><i class="fab fa-facebook"></i></a>
                    <a href="https://x.com/ComunidadTESCI?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor"
                        class="me-2"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/comunidad.tesci/p/Cn4jF-CObxF/" class="me-2"><i
                            class="fab fa-instagram"></i></a>
                    <a href="https://tesci.edomex.gob.mx/"><i class="fas fa-globe"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <!-- Funcion para menu header -->
    <script src="../Controlador/jsRedirigirAppSuperUsuarios.js"></script>
    <!-- Final funcion -->

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>