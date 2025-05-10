<?php
session_start();

// Tiempo de inactividad máximo (en segundos)
$inactive_time = 600; // 1 minuto

// Verificar si la sesión ha expirado
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive_time) {
  session_unset(); // Elimina todas las variables de sesión
  session_destroy(); // Destruye la sesión
  header("Location: Index.html"); // Redirige a la página de inicio de sesión
  exit();
}

// Actualizar la hora de la última actividad
$_SESSION['last_activity'] = time();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_type'])) {
  header("Location: Index.html"); // Redirige si no está logueado
  exit();
}

// Iniciar la sesión para acceder a los datos guardados en la sesión
if (isset($_SESSION['user_email'])) {
  $user_email = $_SESSION['user_email'];

  // Configuración de la conexión a la base de datos
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "residencias_db";

  // Crear conexión
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Verificar si hay errores de conexión
  if ($conn->connect_error) {
    echo "<script>alert('No se pudo conectar a la base de datos'); window.location.href='Index.html';</script>";
    exit();
  }

  // Consulta para obtener los datos del alumno
  $sql_alumno = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.correo_electronico, 
                          a.telefono_alumno, a.matricula, a.proyecto_asignado, a.id_empresa,.a.notificacion, a.avance
                   FROM Usuarios u
                   JOIN Alumnos a ON u.id_usuario = a.id_alumno
                   WHERE u.correo_electronico = ?";
  $stmt_alumno = $conn->prepare($sql_alumno);
  $stmt_alumno->bind_param("s", $user_email);
  $stmt_alumno->execute();
  $result_alumno = $stmt_alumno->get_result();

  // Mostrar los datos del alumno
  if ($result_alumno->num_rows > 0) {
    $row_alumno = $result_alumno->fetch_assoc();
    $id_alumno = $row_alumno['id_usuario'];  // Almacenar el id_alumno
    $nombre_alumno = $row_alumno['nombre'];
    $apellido_paterno = $row_alumno['apellido_paterno'];
    $apellido_materno = $row_alumno['apellido_materno'];
    $correo_alumno = $row_alumno['correo_electronico'];
    $telefono_alumno = $row_alumno['telefono_alumno'];
    $matricula = $row_alumno['matricula']; // Obtener la matrícula
    $proyecto_asignado = $row_alumno['proyecto_asignado']; // Obtener el proyecto asignado
    $id_empresa = $row_alumno['id_empresa'];  // Obtener el id de la empresa asociada
    $notificacion = $row_alumno['notificacion'];

    // Consulta para obtener los datos de la empresa asociada al alumno
    $sql_empresa = "SELECT e.nombre_empresa, e.correo_empresa, e.contacto_empresa, e.tutor_asignado, 
                               e.horario_asistencia, e.dias_asistencia
                        FROM empresa e
                        WHERE e.id_empresa = ?";
    $stmt_empresa = $conn->prepare($sql_empresa);
    $stmt_empresa->bind_param("i", $id_empresa);
    $stmt_empresa->execute();
    $result_empresa = $stmt_empresa->get_result();

    // Validar y mostrar los datos de la empresa
    if ($result_empresa->num_rows > 0) {
      $row_empresa = $result_empresa->fetch_assoc();
      $nombre_empresa = $row_empresa['nombre_empresa'] ?: "En proceso de asignación";
      $correo_empresa = $row_empresa['correo_empresa'] ?: "En proceso de asignación";
      $contacto_empresa = $row_empresa['contacto_empresa'] ?: "En proceso de asignación";
      $tutor_asignado = $row_empresa['tutor_asignado'] ?: "En proceso de asignación";
      $horario_asistencia = $row_empresa['horario_asistencia'] ?: "En proceso de asignación";
      $dias_asistencia = $row_empresa['dias_asistencia'] ?: "En proceso de asignación";

      // echo "<h3>Empresa Asignada</h3>";
      // echo "<p>Nombre de la Empresa: $nombre_empresa</p>";
      // echo "<p>Correo de la Empresa: $correo_empresa</p>";
      // echo "<p>Contacto de la Empresa: $contacto_empresa</p>";
      // echo "<p>Tutor Asignado: $tutor_asignado</p>";
      // echo "<p>Horario de Asistencia: $horario_asistencia</p>";
      // echo "<p>Días de Asistencia: $dias_asistencia</p>";
    } else {
      $mensaje_proceso = "<p>Datos de la empresa: En proceso de asignación</p>";
    }

    // Cerrar la consulta de la empresa
    $stmt_empresa->close();
  } else {
    echo "No se encontró el alumno con el correo electrónico proporcionado.";
  }
  // Consulta para obtener el docente asignado al alumno
  $sql_docente = "SELECT d.id_docente, CONCAT(u_docente.nombre, ' ', u_docente.apellido_paterno, ' ', u_docente.apellido_materno) AS nombre_completo_docente,
  d.telefono_docente, d.correo_institucional, d.clave_profesor, d.observaciones
FROM Docentes d
JOIN Asignaciones asg ON d.id_docente = asg.id_docente
JOIN Alumnos a ON asg.id_alumno = a.id_alumno
JOIN Usuarios u_alumno ON a.id_alumno = u_alumno.id_usuario
JOIN Usuarios u_docente ON d.correo_institucional = u_docente.correo_electronico
WHERE u_alumno.correo_electronico = ?";
  $stmt_docente = $conn->prepare($sql_docente);
  $stmt_docente->bind_param("s", $user_email);
  $stmt_docente->execute();
  $result_docente = $stmt_docente->get_result();

  // Verificar si se encontró el docente
  if ($result_docente->num_rows > 0) {
    $row_docente = $result_docente->fetch_assoc();
    $nombre_completo_docente = $row_docente['nombre_completo_docente'];
    $telefono_docente = $row_docente['telefono_docente'];
    $correo_institucional = $row_docente['correo_institucional'];
    $clave_profesor = $row_docente['clave_profesor'];
    $observaciones = $row_docente['observaciones'];

    // Mostrar la información del docente asignado

    // echo "<p><strong>Nombre:</strong> $nombre_completo_docente</p>";
    // echo "<p><strong>Teléfono:</strong> $telefono_docente</p>";
    // echo "<p><strong>Correo Institucional:</strong> $correo_institucional</p>";
    // echo "<p><strong>Clave Profesor:</strong> $clave_profesor</p>";
    // echo "<p><strong>Observaciones:</strong> $observaciones</p>";
  } else {
    // echo "<p>No se encontró un docente asignado a este alumno.</p>";
  }

  // Cerrar conexiones
  $stmt_docente->close();

  // Cerrar la conexión a la base de datos
  $stmt_alumno->close();
  $conn->close();
}
?>




<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alumnos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- CAMBIO DE COLOR -->
    <link rel="stylesheet" href="stylesCambioTemaAlumnos.css">
          <!-- Alondra -->
  <link rel="stylesheet" href="Styles/stylesCambioTemaProfesores.css" />
      <!-- Fin alondra -->
  <script>
    // Función para cerrar sesión si no hay actividad del ratón
    let timeout;

    function resetTimer() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        window.location.href = "logout.php"; // Redirige al cerrar sesión
      }, 1990000); // 1 minuto = 60000 ms
    }

    // Eventos para detectar movimiento o clics
    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer;
    window.ontouchstart = resetTimer;
    window.onscroll = resetTimer;

    //FUNCIÓN PARA CERRAR SESIÓN
    function cerrarSesion() {
      window.location.href = "index.html";
    }
  </script>

  <style>
    /* Barra de navegación fija */
    .navbar-custom {
      background-color: #f8f9fa;
      position: sticky;
      /* Esto hace que se quede fija en la parte superior */
      top: 0;
      z-index: 1000;
    }

    .navbar-custom .nav-link,
    .navbar-custom .navbar-brand {
      color: #000;
    }

    .search-bar {
      max-width: 200px;
    }

    .btn-search {
      background-color: #28a745;
      color: white;
    }

    .btn-register {
      background-color: #007bff;
      color: white;
    }

    .profile-name {
      color: #000;
      font-weight: bold;
    }

    /* Estilos para el menú lateral estilo Teams */
    .sidebar {
      position: fixed;
      /* Esto hace que la barra lateral se quede fija */
      height: 100vh;
      width: 80px;
      background-color: #1b1e21;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 20px;
      z-index: 999;
      /* Asegura que la barra lateral esté encima del contenido */
    }

    .sidebar a {
      color: #b0b3b8;
      font-size: 14px;
      text-decoration: none;
      margin: 20px 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: color 0.3s ease, background-color 0.3s ease;
      width: 100%;
      padding: 10px 0;
    }

    .sidebar a i {
      font-size: 24px;
      margin-bottom: 5px;
    }

    .sidebar a:hover,
    .sidebar a.active {
      color: #ffffff;
      background-color: #3a3f44;
      border-radius: 10px;
    }

    /* Espacio para el contenido principal */
    .content {
      flex-grow: 1;
      padding: 20px;
      background-color: #f8f9fa;
      margin-left: 80px;
      /* Para que el contenido no se solape con la barra lateral */
      margin-top: 70px;
      /* Ajuste para evitar que el contenido quede debajo de la barra de navegación fija */
    }

    /* Estilos personalizados para el footer */
    footer {
      background-color: #1b1e21;
      color: white;
    }

    footer a {
      color: white;
      text-decoration: none;
    }

    footer a:hover {
      color: #adb5bd;
    }

    .footer-icons a {
      color: white;
    }

    .footer-icons a:hover {
      color: #adb5bd;
    }
  </style>
</head>

<body>

  <!-- Barra de navegación fija -->
<nav class="border border-2 rounded-top-2 navbar navbar-expand-lg navbar-light navbar-custom">
  <div class="container-fluid">
    <a class="navbar-brand" href="Inicio.html">
      <img src="../Recursos\img\logo\rino.png" alt="Logo" width="70" height="40" />
    </a>
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <li class="nav-item">
        <a class="nav-link fw-bolder" style="color: #56212f;">ADMINISTRADOR DE RESIDENCIAS</a>
      </li>
      <li class="nav-item">
        <a class="nav-link fw-bolder" style="color: #BC955B;">Alumno</a>
      </li>
    </ul>

    <!-- Leyenda y Barra de progreso -->
    <div class="d-flex align-items-center">
      <!-- Leyenda de progreso -->
      <span class="me-3" style="font-size: 16px; font-weight: bold;">Progreso del Alumno:</span>

      <!-- Barra de progreso sin animación -->
      <div class="progress" style="width: 300px; height: 20px;">
      <?php 
      $per = ($row_alumno['avance'] / 31) * 100;
      echo "<div class='progress-bar text-bg-success' style='width: $per%'>{$row_alumno['avance']}/31</div>"?>
      </div>
    </div>

    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <div class="dropdown">
          <a class="btn m-2 d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <?php
            if ($notificacion === null){    
              echo "<i class='bi bi-bell'></i>";                       
            }else{
              echo "<i class='bi bi-bell-fill text-danger'></i>"; }
          ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#">NOTIFICACIÓNES</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <?php
              if ($notificacion === null){
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-file-earmark-text-fill'></i> ¡Bienvenido! recuerda enviar tu propuesta de proyecto</a></li>";                            
              }elseif ($notificacion == 1) {
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-check-circle-fill'></i> Tu proyecto ha sido <strong>aceptado</strong>. Puedes empezar a trabajar.</a></li>";              
              }elseif ($notificacion == 0) {
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-x-circle-fill'></i> Tu proyecto ha sido <strong>rechazado</strong>. Revísalo y haz las modificaciónes necesarias.</a></li>";              
              }elseif ($notificacion == 3) {
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-file-earmark-text-fill'></i> <strong></strong> ha propuesto un nuevo proyecto.</a></li>";              
              }
            ?>            
          </ul>
        </div>

      <div class="dropdown">
        <a class="btn btn-outline-dark dropdown-toggle btn m-2 d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-circle" style="font-size: 20px; margin-right: 8px;"></i>
          <span style="font-size: 18px; margin-bottom: 0;"><?php echo "$nombre_alumno "; ?></span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#"><?php echo "Alumno"; ?></a></li>
          <li><a class="dropdown-item" href="#"><?php echo "<p>Nombre:  $nombre_alumno $apellido_paterno $apellido_materno</p>"; ?></a></li>
          <li><a class="dropdown-item" href="#"><?php echo "<p>Correo electrónico:  $correo_alumno</p>"; ?></a></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item" href="#"><?php echo "<p>Teléfono: $telefono_alumno</p>"; ?></a></li>
          <li>
              <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalConfiguraciones" title="Cambia el color de la página a tu gusto.">
               <i class="bi bi-gear-fill"></i> Configuraciones
              </a>
          </li>

         
          
          
        </ul>
      </div>
    </ul>
  </div>
</nav>

  <!-- Modal Cargando -->
  <div class="modal fade" id="miModal" tabindex="-1" aria-labelledby="miModalLabel" aria-hidden="true"> 
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">

      <!-- Encabezado del modal -->
      <div class="modal-header border-0">
        <h5 class="modal-title w-100" id="miModalLabel">¡Bienvenido Alumno al Sistema TESCI!</h5>
      </div>

      <!-- Cuerpo del modal -->
      <div class="modal-body">

        <!-- Video animado tipo loader -->
        <video
          src="..\Recursos\Banners\fotos-largas\gif.mp4"
          autoplay
          muted
          loop
          playsinline
          style="width: 120px; height: auto; border-radius: 10px; margin: 0 auto 15px;"
        ></video>

        <!-- Barra de carga con porcentaje -->
        <div class="progress" style="height: 20px; position: relative;">
          <div
            id="barraProgreso"
            class="progress-bar progress-bar-striped progress-bar-animated text-white"
            role="progressbar"
            style="width: 0%; background-color: #8a2036;"
            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
          >
            <span id="porcentajeTexto" style="position: absolute; left: 3%; transform: translateX(-3%); font-size: 13px;">
              0%
            </span>
          </div>
        </div>

        <!-- Texto y animación de carga -->
        <div class="d-flex align-items-center mt-3">
          <strong role="status">Cargando... Por favor espera</strong>

          <div class="spinner-border spinner-border-sm ms-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>

          <div class="spinner-grow spinner-grow-sm ms-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Script para animar la barra y cerrar el modal -->
<script>
  window.onload = function () {
    const miModal = new bootstrap.Modal(document.getElementById('miModal'));
    miModal.show();

    let progreso = 0;
    const barra = document.getElementById('barraProgreso');
    const texto = document.getElementById('porcentajeTexto');

    const intervalo = setInterval(() => {
      if (progreso >= 100) {
        clearInterval(intervalo);
        setTimeout(() => {
          miModal.hide();
        }, 500); // opcional: espera 0.5s más al llegar al 100%
      } else {
        progreso++;
        barra.style.width = progreso + '%';
        barra.setAttribute('aria-valuenow', progreso);
        texto.innerText = progreso + '%';
      }
    }, 30); // velocidad de carga: ajusta este número si quieres más rápido o lento
  };
</script>


  <!--Fin Modal Cargando -->


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
          <button type="button" class="btn btn-danger" onclick="cerrarSesion()" title="Haz clic para cierrar de sesión y volver a la página de inicio.">
            Cerrar sesión
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenedor principal con el menú lateral y el contenido -->
  <div class="d-flex">
    <!-- Sidebar estilo Teams -->
    <div class="sidebar d-flex flex-column align-items-center pt-0">
      

    <!-- <a href="../Formato.html" class="text-center active">
  <i class="bi bi-house-door-fill"></i>
  <span>Formatos</span>
</a> -->

 <!-- Enlace para abrir el modal -->
    <a href="#" class="text-center active" data-bs-toggle="modal" data-bs-target="#formModal" title="Genera los 8 formatos requeridos para el 
    proceso de residencia profesional.">
      <i class="bi bi-file-earmark-word-fill"></i>
      <span>Formatos</span>
    </a>



    
      <a href="#alumnos" class="text-center">
        <i class="bi bi-person-fill" title="Visualiza los datos registrados."></i>
        <span>Alumno</span>
      </a>
      <a href="#residencias" class="text-center">
        <i class="bi bi-mortarboard-fill"title="Visualiza los datos registrados
        sobre la empresa."></i>
        <span>Mi Residencias</span>
      </a>
      <a href="#" class="text-center" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right" title="Haz clic para cerrar sesión."></i>
        <span>Salir</span>
      </a>
    </div>




                    <!-- Modal de Configuraciones ALONDRA -->
                    <div class="modal fade" id="modalConfiguraciones" tabindex="-1" aria-labelledby="modalConfiguracionesLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
              <div class="modal-content rounded-4 shadow">
                <div class="modal-header text-white" style="background-color:rgb(121, 26, 45);">
                  <h5 class="modal-title" id="modalConfiguracionesLabel">Configuraciones de perfil</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body bg-dark text-white">
                  <div class="nav flex-column nav-pills mb-3">
                    <button class="nav-link bg-dark text-white border mb-2 active" data-bs-toggle="pill" data-bs-target="#themeTab">Color y tema</button>
                  </div>
                  
                  <div class="tab-content">
                    <div class="tab-pane fade show active" id="themeTab">
                      <h6 class="text-white mb-3">Selecciona un tema:</h6>
                      <div class="theme-grid">
                        <!-- Tema predeterminado - Original -->
                         <div class="theme-item">
                          <div class="theme-circle active" data-theme="theme-default" style="--left-color: #8a2036; --right-color: #6d1a2a"></div>
                          <div class="theme-name text-white">Predeterminado</div>
                        </div>
                        <!-- Negro -->
                         <div class="theme-item">
                          <div class="theme-circle" data-theme="theme-black" style="--left-color: #121212; --right-color: #000000"></div>
                          <div class="theme-name text-white">Negro</div>
                        </div>
                        <!-- Sepia -->
                         <div class="theme-item">
                          <div class="theme-circle" data-theme="theme-sepia" style="--left-color: #f4ecd8; --right-color: #e0c9a6"></div>
                          <div class="theme-name text-white">Sepia</div>
                        </div>
                        <!-- Gris oscuro -->
                         <div class="theme-item">
                          <div class="theme-circle" data-theme="theme-darkgray" style="--left-color: #333333; --right-color: #222222"></div>
                          <div class="theme-name text-white">Gris oscuro</div>
                        </div>
                        <!-- Morado claro -->
                         <div class="theme-item">
                          <div class="theme-circle" data-theme="theme-lightpurple" style="--left-color: #d8ccf1; --right-color: #9f8ad7"></div>
                          <div class="theme-name text-white">Morado claro</div>
                        </div>
                        <!-- Rosa claro -->
                         <div class="theme-item">
                          <div class="theme-circle" data-theme="theme-lightpink" style="--left-color: #ffdde1; --right-color: #f8a5c2"></div>
                          <div class="theme-name text-white">Rosa claro</div>
                        </div>
                        <!-- Azul claro -->
                         <div class="theme-item">
                          <div class="theme-circle" data-theme="theme-lightblue" style="--left-color: #d4f1f9; --right-color: #90cdf4"></div>
                          <div class="theme-name text-white">Azul claro</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer border-secondary">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" title="Haz clic para cancelar los cambios y mantener el tema actual.">Cancelar</button>
                  <button type="button" class="btn btn-primary" id="saveThemeChanges"title="Haz clic para aplicar y guardar el tema seleccionado.">Guardar cambios</button>
                </div>
              </div>
            </div>
          </div>
          <!-- Fin Modal de Configuraciones -->







    <!-- Contenido principal -->


    <div class="content p-4 mt-0 bg-white rounded shadow-sm">
      <!-- Encabezado principal con botón -->
      <div class="d-flex mb-4" style="background-color: #8a2036; padding: 20px; border-radius: 8px;">
        <h1 class="text-uppercase text-white m-0" style="padding-left: 70px;">RESIDENCIAS PROFESIONALES</h1>
        <div class="text-center" style="padding-left: 350px;">
          <p class="text-white fw-semibold mb-1">Estado de Residencias</p>
          <button id="estado"  title="Este es tu estado actual 
de residencia profesionales."
            class="btn 
            <?php
            echo is_null($notificacion)
              ? 'btn-warning text-white'
              : ($notificacion ? 'btn-success text-white' : 'btn-danger text-white');
            ?> btn-sm px-4">
            <?php
            echo is_null($notificacion)
              ? 'En revisión'
              : ($notificacion ? 'Aprobada' : 'Rechazada');
            ?>
          </button>


        </div>
      </div>




      <!-- Datos principales -->
      <div class="col-md-12 pb-3" id="alumnos">
        <div class="card border-light shadow-sm h-100">
          <div class="card-body text-center">
            <h2 style="color: #BC955B;">Carrera</h2>
            <h4 class="text-muted">Ing. en Sistemas Computacionales</h4>
          </div>
        </div>
      </div>




<!-- Generar Reporte formato modal -->
<!-- Modal para llenar los datos del documento Word -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header text-white rounded-top-4" style="background-color:rgb(121, 26, 45);">
        <h5 class="modal-title" id="formModalLabel">Formulario para la generación de reportes en Word</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
      <ul class="list-group">
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalCartaPresentacion" title="Haz clic para generar tu carta de presentación.">Carta Presentación</li>
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalCartaAceptacion" title="Haz clic para generar tu carta de aceptación.">Carta Aceptación</li>
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalAnteproyecto" title="Haz clic para generar tu anteproyecto.">Anteproyecto</li>
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalAsesoria" title="Haz clic para generar tus asesorias.">Asesoría</li>
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalVisita" title="Haz clic para generar tus visitas.">Visita</li>
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalEvaluacion1" title="Haz clic para generar tus dos evaluaciones.">Evaluación</li>
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalEvaluacion2" title="Haz clic para generar tu última evaluación">Evaluación Final</li>
        <li class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#modalCartaTermino" title="Haz clic para generar tu carta de término.">Carta Término</li>
      </ul>

        
      </div>
    </div>
  </div>
</div>

<!-- Modal para llenar los datos del documento Word -->


 <!-- Modales -->
  <!-- Carta Presentación-->
  <div class="modal fade" id="modalCartaPresentacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Carta Presentación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
      <form action="../../generar.php" method="POST">
          <div class="row g-3">
          <div class="col-md-4">
            <label for="fechaCompleta" class="form-label">Fecha</label>
            <input type="date" class="form-control" id="fechaCompleta" name="fechaCompleta" required>
          </div>

  

            <div class="col-md-6">
              <label for="NombredelIngeniero" class="form-label">Nombre del Ingeniero</label>
              <input type="text" class="form-control" id="NombredelIngeniero" name="NombredelIngeniero" required oninput="this.value = this.value.toUpperCase();">

            </div>

            <div class="col-md-6">
              <label for="nombreEmpresa" class="form-label">Ingresa nombre de la empresa o unida económica</label>
              <input type="text" class="form-control" id="nombreEmpresa" name="nombreEmpresa" required oninput="this.value = this.value.toUpperCase();">
            </div>
            <div class="col-md-6">
              <label for="Nombredelestudiante" class="form-label">Nombre del Estudiante</label>
              <input type="text" class="form-control" id="Nombredelestudiante" name="Nombredelestudiante" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="numMatricula" class="form-label">Número de Matrícula</label>
              <input type="text" class="form-control" id="numMatricula" name="numMatricula"  maxlength="9" required
              oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
            </div>
            

            <div class="col-md-6">
              <label for="horaInicio" class="form-label">Hora de entrada</label>
              <input type="time" class="form-control" id="horaInicio" name="horaInicio" required>
            </div>
            <div class="col-md-6">
              <label for="horaFinal" class="form-label">Hora de salida</label>
              <input type="time" class="form-control" id="horaFinal" name="horaFinal" required>
            </div>

            <div class="col-md-6">
              <label for="PeríodoMínimodeMeses" class="form-label">Periodo mínimo (meses)</label>
              
              <input type="number"  min="1" max="6" step="1" class="form-control" id="PeríodoMínimodeMeses" name="PeríodoMínimodeMeses" required>
            </div>
            <div class="col-md-6">
              <label for="PeríodoMaximodeMeses"   class="form-label">Periodo máximo (meses)</label>
              <input type="number" min="1" max="6" step="1"  class="form-control" id="PeríodoMaximodeMeses" name="PeríodoMaximodeMeses" required>
            </div>

            <div class="col-md-6">
              <label for="días" class="form-label">Días de asistencia</label>
              <input type="text" class="form-control" id="días" name="días" placeholder="Ej: Lunes a Viernes" required oninput="this.value = this.value.toUpperCase();">
            </div>
            

            <div class="col-md-6">
              <label for="NombreJefadivision" class="form-label">Ingresa nombre de la Jefa de división</label>
              <input type="text" class="form-control" id="NombreJefadivision" name="NombreJefadivision" required oninput="this.value = this.value.toUpperCase();">
            </div>

          </div>

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success" title="Haz clic para generar el formato en Word con tu información actual.">Generar documento</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" title="Haz clic para cancelar la generación del documento.">Cancelar</button>
          </div>
        </form>
      </div>
    </div></div>
  </div>

  <!-- Carta Aceptación -->
  <div class="modal fade" id="modalCartaAceptacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Carta Aceptación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
      <form action="../../CartaAceptacion.php" method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="fechaCompleta" class="form-label">Fecha de inicio</label>
              <input type="date" class="form-control" id="fechaCompleta" name="fechaCompleta" required>
            </div>
            <div class="col-md-6">
              <label for="fechaFinal" class="form-label">Fecha de finalización</label>
              <input type="date" class="form-control" id="fechaFinal" name="fechaFinal" required>
            </div>

            <div class="col-md-6">
              <label for="nombreEmpresa" class="form-label">Nombre de la Empresa</label>
              <input type="text" class="form-control" id="nombreEmpresa" name="nombreEmpresa" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="NombreJefadivision" class="form-label">Nombre de la Jefa de División</label>
              <input type="text" class="form-control" id="NombreJefadivision" name="NombreJefadivision" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="Nombredelestudiante" class="form-label">Nombre del Estudiante</label>
              <input type="text" class="form-control" id="Nombredelestudiante" name="Nombredelestudiante" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="numMatricula" class="form-label">Número de Matrícula</label>
              <input type="text" class="form-control" id="numMatricula" name="numMatricula"  maxlength="9" required
              oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
            </div>

            <div class="col-md-6">
              <label for="area" class="form-label">Área</label>
              <input type="text" class="form-control" id="area" name="area" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="nombredelProyecto" class="form-label">Nombre del Proyecto</label>
              <input type="text" class="form-control" id="nombredelProyecto" name="nombredelProyecto" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="horaInicio" class="form-label">Hora de entrada</label>
              <input type="time" class="form-control" id="horaInicio" name="horaInicio" required>
            </div>
            <div class="col-md-6">
              <label for="horaFinal" class="form-label">Hora de salida</label>
              <input type="time" class="form-control" id="horaFinal" name="horaFinal" required>
            </div>

            <div class="col-md-6">
              <label for="diaInicio" class="form-label">Día de inicio (semana)</label>
              <input type="text" class="form-control" id="diaInicio" name="diaInicio" placeholder="Ej: LUNES" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="diaFinal" class="form-label">Día final (semana)</label>
              <input type="text" class="form-control" id="diaFinal" name="diaFinal" placeholder="Ej: VIERNES" required oninput="this.value = this.value.toUpperCase();">
            </div>

            

            <div class="col-md-6">
              <label for="nombredelEncargadoUN" class="form-label">Nombre del Encargado (Asesor)</label>
              <input type="text" class="form-control" id="nombredelEncargadoUN" name="nombredelEncargadoUN" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="areaEncargada" class="form-label">Área del Encargado</label>
              <input type="text" class="form-control" id="areaEncargada" name="areaEncargada" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-6">
              <label for="diaAsistencia" class="form-label">Día de asistencia al Tec</label>
              <input type="text" class="form-control" id="diaAsistencia" name="diaAsistencia" placeholder="Ej: MIÉRCOLES" required oninput="this.value = this.value.toUpperCase();">
            </div>
          </div>

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success" title="Haz clic para generar el formato en Word con tu información actual.">Generar documento</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" title="Haz clic para cancelar la generación del documento.">Cancelar</button>
          </div>
        </form>

      </div>
    </div></div>
  </div>

  <!-- Anteproyecto -->
  <div class="modal fade" id="modalAnteproyecto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Anteproyecto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
      <form action="../../AnteproyectoResidencia.php" method="POST">
      <div class="row mb-3">
        <div class="col-md-4">
          <label>Fecha de llenado</label>
          <input type="date" name="fechaLlenado" class="form-control" required>
        </div>
      </div>

      <h4>Datos del Alumno</h4>
      <div class="row mb-3">
        <div class="col-md-6">
          <input type="text" name="nombreAlumno" placeholder="Nombre del Alumno" class="form-control" required>
        </div>
        <div class="col-md-3">
          <input type="text" name="numControl" placeholder="No. Control" class="form-control"  maxlength="9" required
          oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
        </div>
        <div class="col-md-3">
          <input type="number" min="1" max="9" step="1" name="numSemestare" placeholder="Semestre" class="form-control" required>
        </div>
        <div class="col-md-3 mt-2">
          <input type="number"  name="numCreditos" placeholder="Créditos" class="form-control" required>
        </div>
      </div>

      <h4>Datos de la Empresa</h4>
      <div class="mb-3">
        <input type="text" name="empresa" placeholder="Nombre de la empresa" class="form-control mb-2" required>
        <input type="text" name="giro" placeholder="Giro" class="form-control mb-2" required>
        <input type="text" name="direccionEmpresa" placeholder="Dirección" class="form-control mb-2" required>
        <input type="tel" name="numTelefono" placeholder="Teléfono" class="form-control mb-2" required>
        <input type="email" name="correoEmpresa" placeholder="Correo electrónico" class="form-control" required>
      </div>

      <h4>Proyecto</h4>
      <input type="text" name="areaProyecto" placeholder="Área del Proyecto" class="form-control mb-2" required>
      <input type="text" name="nombreProyecto" placeholder="Nombre del Proyecto" class="form-control mb-3" required>

      <div class="row mb-3">
        <div class="col-md-6">
          <label>Fecha de Inicio</label>
          <input type="date" name="fechaInicio" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Fecha de Fin</label>
          <input type="date" name="fechaFin" class="form-control" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label>Hora de Inicio</label>
          <input type="time" name="horaInicio" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Hora de Fin</label>
          <input type="time" name="horaFinal" class="form-control" required>
        </div>
      </div>

      <h4>Personas Involucradas (elige una sola)</h4>
      <div class="row">
          <div class="col-md-6">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="personaSeleccionada" value="unaPersona" required>
              <label class="form-check-label">Persona 1</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="personaSeleccionada" value="dosPersona">
              <label class="form-check-label">Persona 2</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="personaSeleccionada" value="tresPersona">
              <label class="form-check-label">Persona 3</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="personaSeleccionada" value="cuatroPersona">
              <label class="form-check-label">Persona 4</label>
            </div>
          </div>
        </div>


      <h4>Responsables</h4>
      <input type="text" name="representanteEmpresa" placeholder="Representante de la Empresa" class="form-control mb-2" required>
      <input type="text" name="nombreAsesorInt" placeholder="Nombre del Asesor Interno" class="form-control mb-2" required>
      <input type="text" name="nombreJefaDeCarrera" placeholder="Nombre de la Jefa de Carrera" class="form-control mb-3" required>
      <h4>Descripción del proyecto</h4>
      <input type="text" name="objetivoProyecto" placeholder="Objetivo" class="form-control mb-2" required>
      <input type="text" name="justificaProyecto" placeholder="Justificación" class="form-control mb-2" required>
      <h4>Descripción de la actividad</h4>
      <input type="text" name="actividaFaseUno" placeholder="Fase 1:" class="form-control mb-2" required>
      <input type="text" name="actividaFaseDos" placeholder="Fase 2" class="form-control mb-2" required>
      <input type="text" name="actividaFaseTres" placeholder="Fase 3:" class="form-control mb-2" required>
      <input type="text" name="actividaFaseCuatro" placeholder="Fase 4" class="form-control mb-2" required>
      


      <button type="submit" class="btn btn-secondary" title="Haz clic para generar el formato en Word con tu información actual.">Generar Word</button>
    </form>
      </div>
    </div></div>
  </div>

  <!-- 4 -->
  <div class="modal fade" id="modalAsesoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Asesoría</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
      <form action="../../FormatoDeRegistroDeAsesorias.php" method="POST">
        <div class="row g-3">

          <div class="col-md-4">
            <label for="fechaCompleta" class="form-label">Fecha de elaboración</label>
            <input type="date" class="form-control" id="fechaCompleta" name="fechaCompleta" required>
          </div>

          <div class="col-md-6">
            <label for="departamentoAcademico" class="form-label">Departamento Académico</label>
            <input type="text" class="form-control" id="departamentoAcademico" name="departamentoAcademico" required oninput="this.value = this.value.toUpperCase();">
          </div>

          <div class="col-md-6">
            <label for="nombreAlumno" class="form-label">Nombre del Alumno</label>
            <input type="text" class="form-control" id="nombreAlumno" name="nombreAlumno" required oninput="this.value = this.value.toUpperCase();">
          </div>

          <div class="col-md-6">
            <label for="numControl"  class="form-label">Número de Control</label>
            <input type="text" class="form-control" id="numControl" name="numControl"  maxlength="9" required
            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
          </div>

          <div class="col-md-6">
            <label for="nombreProyecto" class="form-label">Nombre del Proyecto</label>
            <input type="text" class="form-control" id="nombreProyecto" name="nombreProyecto" required oninput="this.value = this.value.toUpperCase();">
          </div>

          <!-- Fecha de inicio -->
          <div class="col-md-4">
            <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
          </div>

          <!-- Fecha de fin -->
          <div class="col-md-4">
            <label for="fechaFin" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="fechaFin" name="fechaFin" required>
          </div>

          <div class="col-md-6">
            <label for="empresa" class="form-label">Empresa</label>
            <input type="text" class="form-control" id="empresa" name="empresa" required oninput="this.value = this.value.toUpperCase();">
          </div>

          <div class="col-md-6">
            <label for="numAsesoria"  class="form-label">Número de Asesoría</label>
            <input type="number"   min="1" step="1"  class="form-control" id="numAsesoria" name="numAsesoria" required>
          </div>

          <div class="col-md-6">
            <label for="tipoAsesoria" class="form-label">Tipo de Asesoría</label>
            <input type="text" class="form-control" id="tipoAsesoria" name="tipoAsesoria" required oninput="this.value = this.value.toUpperCase();">
          </div>

          <div class="col-md-6">
            <label for="temaAserorar" class="form-label">Tema a Asesorar</label>
            <input type="text" class="form-control" id="temaAserorar" name="temaAserorar" required oninput="this.value = this.value.toUpperCase();">
          </div>

          <div class="col-md-12">
            <label for="solucionAsesoria" class="form-label">Solución de la Asesoría</label>
            <textarea class="form-control" id="solucionAsesoria" name="solucionAsesoria" required rows="3" oninput="this.value = this.value.toUpperCase();"></textarea>
          </div>

          <div class="col-md-6">
            <label for="nombreAsesorInt" class="form-label">Nombre del Asesor Interno</label>
            <input type="text" class="form-control" id="nombreAsesorInt" name="nombreAsesorInt" required oninput="this.value = this.value.toUpperCase();">
          </div>

        </div>

        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-success" title="Haz clic para generar el formato en Word con tu información actual.">Generar documento</button>
        </div>
      </form>


      </div>
    </div></div>
  </div>

  <!-- 5 -->
  <div class="modal fade" id="modalVisita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../../Visitas.php" method="POST">
                    <!-- Fecha -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Fecha de llenado</label>
                            <input type="date" name="fechaLlenado" class="form-control" required>
                        </div>
                    </div>

                    <!-- Datos del Alumno -->
                    <h4>Datos del Alumno</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreAlumno" placeholder="Nombre del Alumno" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="numControl" placeholder="No. Control" class="form-control"  maxlength="9" required
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
                        </div>
                    </div>

                    <!-- Datos del Proyecto -->
                    <h4>Proyecto</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreProyecto" placeholder="Nombre del Proyecto" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="nombreEmpresa" placeholder="Nombre de la empresa" class="form-control" required>
                        </div>
                    </div>

                    <!-- Fecha y Hora -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Fecha de reporte</label>
                            <input type="date" name="fechaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Hora</label>
                            <input type="time" name="horaInicio" class="form-control" required>
                        </div>
                    </div>

                    <!-- Descripción del Proyecto -->
                    <h4>Descripción del Proyecto</h4>
                    <input type="text" name="objetivoProyecto" placeholder="Objetivo del Proyecto" class="form-control mb-2" required>

                    <!-- Contacto -->
                    <h4>Tipo de Entrevista</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="tel" value="Teléfono">
                        <label class="form-check-label">Teléfono</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Corr" value="Correo Electrónico">
                        <label class="form-check-label">Correo Electrónico</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="InS" value="In Situ">
                        <label class="form-check-label">In Situ</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="otros" value="Otros">
                        <label class="form-check-label">Otros</label>
                        <input type="text" name="otrosTexto" placeholder="Especificar" class="form-control mt-2">
                    </div>

                    <!-- Selección de Seguimientos (solo uno puede ser seleccionado) -->
                    <h4>Seguimientos</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pri" value="Teléfono">
                        <label class="form-check-label">Primer Seguimiento</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="seg" value="Correo Electrónico">
                        <label class="form-check-label">Segundo Seguimiento</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ter" value="In Situ">
                        <label class="form-check-label">Tercer Seguimiento</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cuarto" value="In Situ">
                        <label class="form-check-label">Cuarto Seguimiento</label>
                    </div>

                    <!-- Asesor Externo -->
                    <h4>Asesor Externo</h4>
                    <input type="text" name="nombreAsesorExt" placeholder="Nombre del Asesor Externo" class="form-control mb-2" required>
                    <input type="text" name="CargoEncargado" placeholder="Cargo del Encargado" class="form-control mb-2" required>
                    <input type="text" name="observacionesAsesorExt" placeholder="Observaciones del Asesor Externo" class="form-control mb-2" required>

                    <!-- Avance y Observaciones -->
                    <h4>Avance y Observaciones</h4>
                    <input type="number" name="numAvance" placeholder="Avance"   min="1" step="1" class="form-control mb-2" required>

                    <input type="text" name="observacionesAsesorInt" placeholder="Observaciones del Asesor Interno" class="form-control mb-2" required>

                    <button type="submit" class="btn btn-secondary" title="Generar documento Word con la información ingresada">Generar Word</button>
                </form>
            </div>
        </div>
    </div>
</div>


  <!-- 6 -->
  <div class="modal fade" id="modalEvaluacion1" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Evaluación </h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">  
        <form action="../../Evaluacion.php" method="POST">
      <h4>Datos de evaluación de Residente</h4>

                    <!-- Fecha -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Fecha de llenado</label>
                            <input type="date" name="fechaLlenado" class="form-control" required>
                        </div>
                    </div>

                    <!-- Datos del Alumno -->
                    <h4>Datos del Alumno</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreAlumno" placeholder="Nombre del Alumno" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="numControl" placeholder="No. Control" class="form-control"  maxlength="9" required
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
                        </div>
                    </div>

                    <!-- Datos del Proyecto -->
                    <h4>Proyecto</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreProyecto" placeholder="Nombre del Proyecto" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="programaEducativo" placeholder="Programa Educativo" class="form-control" required>
                        </div>
                    </div>

                    <!-- Fecha y Hora -->
                     <h4>Periodo de Residencias Profesionales</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Fecha Inicio </label>
                            <input type="date" name="fechaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Fecha de Final</label>
                            <input type="date" name="fechaFinal" class="form-control" required>
                        </div>

                    </div>

                   
                    <!-- Avance y Observaciones -->
                    <h4>Nombres asesor</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreAsesorExte" placeholder="Nombre del asesor externo" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                        <input type="text" name="nombreAsesorInterno" placeholder="Nombre del asesor interno" class="form-control" required>
                        </div>
                    </div>


                    <button type="submit" class="btn btn-secondary" title="Generar documento Word con la información ingresada">Generar Word</button>
                </form>
              </div>
    </div></div>
  </div>

  <!-- 7 -->
  <div class="modal fade" id="modalEvaluacion2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Evaluación Final</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
      <form action="../../EvaluacionFinal.php" method="POST">
      <h4>Datos de evaluación de Residente</h4>

                    <!-- Fecha -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Fecha de llenado</label>
                            <input type="date" name="fechaLlenado" class="form-control" required>
                        </div>
                    </div>

                    <!-- Datos del Alumno -->
                    <h4>Datos del Alumno</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreAlumno" placeholder="Nombre del Alumno" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="numControl" placeholder="No. Control" class="form-control"  maxlength="9" required
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
                        </div>
                    </div>

                    <!-- Datos del Proyecto -->
                    <h4>Proyecto</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreProyecto" placeholder="Nombre del Proyecto" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="programaEducativo" placeholder="Programa Educativo" class="form-control" required>
                        </div>
                    </div>

                    <!-- Fecha y Hora -->
                     <h4>Periodo de Residencias Profesionales</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Fecha Inicio </label>
                            <input type="date" name="fechaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Fecha de Final</label>
                            <input type="date" name="fechaFinal" class="form-control" required>
                        </div>

                    </div>

                   

                    
                    <!-- Avance y Observaciones -->
                    <h4>Nombres asesor</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" name="nombreAsesorExte" placeholder="Nombre del asesor externo" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                        <input type="text" name="nombreAsesorInterno" placeholder="Nombre del asesor interno" class="form-control" required>
                        </div>
                    </div>


                    <button type="submit" class="btn btn-secondary" title="Generar documento Word con la información ingresada">Generar Word</button>
                </form>
      </div>
    </div></div>
  </div>

  <!-- Carta Término -->
  <div class="modal fade" id="modalCartaTermino" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Carta Término</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form action="../../CartaTermino.php" method="POST">
          <div class="row g-3">
          <div class="col-md-4">
            <label for="fechaCompleta" class="form-label">Fecha</label>
            <input type="date" class="form-control" id="fechaCompleta" name="fechaCompleta" required>
          </div>

  

            <div class="col-md-6">
              <label for="NombreJefadivision" class="form-label">Ingresa nombre de la Jefa de división</label>
              <input type="text" class="form-control" id="NombreJefadivision" name="NombreJefadivision" required oninput="this.value = this.value.toUpperCase();">

            </div>

            <div class="col-md-6">
              <label for="nombreEmpresa" class="form-label">Ingresa nombre de la empresa o unida económica</label>
              <input type="text" class="form-control" id="nombreEmpresa" name="nombreEmpresa" required oninput="this.value = this.value.toUpperCase();">
            </div>
            <div class="col-md-6">
              <label for="Nombredelestudiante" class="form-label">Nombre del Estudiante</label>
              <input type="text" class="form-control" id="Nombredelestudiante" name="Nombredelestudiante" required oninput="this.value = this.value.toUpperCase();">
            </div>

            <div class="col-md-5">
              <label for="numMatricula" class="form-label">Número de Matrícula</label>
              <input type="text" class="form-control" id="numMatricula" name="numMatricula"  maxlength="9" required
              oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9);">
            </div>

            <div class="col-md-6">
              <label for="area" class="form-label">Área asignada</label>
              <input type="text" class="form-control" id="area" name="area" required oninput="this.value = this.value.toUpperCase();">
            </div>
            
            <div class="row mb-3 pt-3">
                        <div class="col-md-3">
                            <label>Fecha Inicio </label>
                            <input type="date" name="fechaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Fecha de Final</label>
                            <input type="date" name="fechaFinal" class="form-control" required>
                        </div>
            <div class="col-md-6">
              <label for="NombreProyec" class="form-label">Nombre del Proyecto</label>
              <input type="text" class="form-control" id="NombreProyec" name="NombreProyec" required oninput="this.value = this.value.toUpperCase();">
            </div>
            </div>
            




            <div class="col-md-6">
              <label for="horaInicio" class="form-label">Hora de entrada</label>
              <input type="time" class="form-control" id="horaInicio" name="horaInicio" required>
            </div>
            <div class="col-md-6">
              <label for="horaFinal" class="form-label">Hora de salida</label>
              <input type="time" class="form-control" id="horaFinal" name="horaFinal" required>
            </div>

           

            <div class="col-md-6">
              <label for="dias" class="form-label">Días de asistencia</label>
              <input type="text" class="form-control" id="dias" name="dias" placeholder="Ej: Lunes a Viernes" required oninput="this.value = this.value.toUpperCase();">
            </div>
            

            <div class="col-md-6">
              <label for="nombredelEncargadoUN" class="form-label">Ingrese nombre encargado empresa</label>
              <input type="text" class="form-control" id="nombredelEncargadoUN" name="nombredelEncargadoUN" required oninput="this.value = this.value.toUpperCase();">
            </div>  
            <div class="col-md-6">
              <label for="nombredelEncargadoUN" class="form-label">Ingrese nombre encargado empresa</label>
              <input type="text" class="form-control" id="nombredelEncargadoUN" name="areaEncargada" required oninput="this.value = this.value.toUpperCase();">
            </div>  

          </div>

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success" title="Haz clic para generar el formato en Word con tu información actual.">Generar documento</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" title="Haz clic para cancelar la generación del documento.">Cancelar</button>
          </div>
        </form>
      </div>
    </div></div>
  </div>



<!--FIN  Generar Reporte formato modal -->












      <!-- Información adicional -->
      <div class="row g-3 mt-4">
        <div class="card-header  text-white text-center" style="background-color: #8a2036;">
          <h2>Datos del Alumno</h2>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Matrícula:</h6>
              <p class="text-muted"><?php echo "<p>$matricula</p>"; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark"><strong>Nombre:</strong></h6>
              <p class="mb-2">

                <?php echo "$nombre_alumno $apellido_paterno $apellido_materno"; ?>
              </p>

            </div>
          </div>
        </div>







        



        <div class="col-md-2 p-0">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark"><strong>Correo Electrónico:</strong></h6>
              <p class="mb-2"> <?php echo "<p> $correo_alumno</p>"; ?></p>

            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark"> <strong>Teléfono:</strong></h6>
              <p class="mb-2"> <?php echo "<p> $telefono_alumno</p>"; ?></p>
            </div>
          </div>
        </div>
        <!-- // echo "<p>Horario de Asistencia: $horario_asistencia</p>";
      // echo "<p>Días de Asistencia: $dias_asistencia</p>"; -->
        <div class="col-md-2 ">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Horario Escuela</h6>
              <?php

              echo '<p class="text-muted pe-2 ps-2">' . (!empty($horario_asistencia) ? $horario_asistencia : ' En proceso de asignación') . '</p>';
              ?>

            </div>
          </div>
        </div>
        <div class="col-md-2 ">
          <div class="card border-light shadow-sm h-100 " style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Horario Empresa</h6>
              <?php

              echo '<p class="text-muted pe-2 ps-2">' . (!empty($horario_asistencia) ? $horario_asistencia : ' En proceso de asignación') . '</p>';
              ?>

            </div>
          </div>
        </div>


        <div class="col-md-4 " style="margin-left: 200px;">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
            <h6 class="text-dark">Docente asignado</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($nombre_completo_docente) ?  $nombre_completo_docente : ' En proceso de asignación') . '</p>';

              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($telefono_docente) ?  $telefono_docente : ' S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>

        
        <div class="col-md-4">
          <div class="card border-light shadow-sm h-100" style="background-color: #F1E2DC;">
            <div class="card-body text-center">
              <h6 class="text-dark">Tutor asignado empresa</h6>
              <?php

              echo '<p class="text-muted">' . (!empty($tutor_asignado) ?  $tutor_asignado : ' En proceso de asignación') . '</p>';
              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($contacto_empresa) ?   $contacto_empresa : ' S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>


      </div>

      <!-- fin de Datos del alumno  -->

      

      <!-- Subir Evidencias -->




      <div class="card form-container my-4 shadow-lg rounded-4 border-0 mx-auto" style="max-width: 900px;">
    <div class="card-body p-4">
        <h2 class="text-center mb-4 fw-bold text-black">Subir Evidencias</h2>

       <form enctype="multipart/form-data" method="POST" action="../Modelo/guardar_doc.php" id="uploadForm">
            <div class="input-group mb-3">
                <span class="input-group-text bg-primary text-white border-0"><i class="bi bi-upload"></i></span>
                <input class="form-control border-0 shadow-sm" type="file" name="evidencia" accept=".jpg, .jpeg, .png, .pdf" required id="fileInput" title="Haz clic para seleccionar tu evidencia."/>
                <input type="hidden" id="Matricula" name="Matricula" value="<?php echo "$matricula"; ?>">
                <input type="hidden" id="Alumno" name="Alumno" value="<?php echo "$id_alumno"; ?>">

            </div>
            <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-primary w-40 fw-bold shadow-sm btn-upload " type="submit" title="Haz clic para cargar tu evidencia.">
                    <i class="bi bi-cloud-upload"></i> Subir Evidencia
                </button>
            </div>

            <!-- Barra de carga -->
            <div class="mt-3 d-none" id="progressContainer">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 1%;" id="progressBar"></div>
                </div>
            </div>
        </form>
        <script>//Envio de documentos
          document.getElementById("uploadForm").addEventListener("submit", function(event) {
          event.preventDefault(); // Evita el envío normal del formulario

          const form = document.getElementById("uploadForm");
          const formData = new FormData(form);
          const progressContainer = document.getElementById("progressContainer");
          const progressBar = document.getElementById("progressBar");
          const fileInput = document.getElementById('fileInput');
          const fileName = fileInput.files[0].name;
            
          // Mostrar la barra de progreso
          progressContainer.classList.remove("d-none");

          const xhr = new XMLHttpRequest();
          xhr.open("POST", "../Modelo/guardar_doc.php", true);

          // Escuchar el progreso de carga
          xhr.upload.onprogress = function(event) {
              if (event.lengthComputable) {
                  const percent = Math.round((event.loaded / event.total) * 100);
                  progressBar.style.width = percent + "%";
                  progressBar.innerText = percent + "%";
              }
          };

          // Al finalizar la carga
          xhr.onload = function() {
              if (xhr.status === 200) {
                  progressBar.classList.remove("progress-bar-animated");
                  progressBar.classList.add("bg-success");
                  progressBar.innerText = "¡"+fileName+" Subido!";
              } else {
                  progressBar.classList.add("bg-danger");
                  progressBar.innerText = "Error al subir";
              }
          };

          // Enviar datos
          xhr.send(formData);
      });
      </script>
    </div>
</div>


      
   <!--Fin Subir Evidencias -->




      <!-- Información adicional -->
      <div class="row g-3 mt-5" id="residencias">
        <div class="card-header  text-white text-center" style="background-color: #8a2036;">
          <h2>Mi Residencias</h2>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Matrícula</h6>
              <p class="text-muted"><?php echo "<p> $matricula</p>"; ?></p>

            </div>
          </div>
        </div>

        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Empresa</h6>
              <?php

              echo '<p class="text-muted">' . (!empty($nombre_empresa) ? $nombre_empresa : ' En proceso de asignación') . '</p>';
              ?>


            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Correo empresa</h6>
              <p class="text-muted">
                <?php

                echo '<p class="text-muted">' . (!empty($correo_empresa) ? $correo_empresa : ' En proceso de asignación') . '</p>';
                ?>

              </p>
            </div>
          </div>
        </div>

        <div class="col-md-5">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Proyecto asignado </h6>
              <p class="text-muted"><?php echo "<p> $proyecto_asignado</p>"; ?></p>


            </div>
          </div>
        </div>
        <div class="col-md-3">
          <!-- Botón para abrir el modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empresaModal" title="Haz clic para ver los datos de la empresa asignada 
          a tu residencia profesional.">
            Ver Empresa Asignada
          </button>

          <!-- Modal -->
          <div class="modal fade" id="empresaModal" tabindex="-1" aria-labelledby="empresaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <!-- Encabezado del Modal -->
                <div class="modal-header">
                  <h5 class="modal-title" id="empresaModalLabel">Información de la Empresa Asignada</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body">
                  <!-- Contenido con Bootstrap Card para mejorar el diseño -->
                  <div class="card">
                    <div class="card-body">
                      <h3 class="card-title">Empresa Asignada</h3>
                      <ul class="list-group list-group-flush">
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($nombre_empresa) ? $nombre_empresa : 'En proceso de revisión') . '</li>';
                        ?>

                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($correo_empresa) ? $correo_empresa : 'En proceso de revisión') . '</li>';
                        ?>
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($contacto_empresa) ? $contacto_empresa : 'En proceso de revisión') . '</li>';
                        ?>
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($tutor_asignado) ? $tutor_asignado : 'En proceso de revisión') . '</li>';
                        ?>
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($horario_asistencia) ? $horario_asistencia : 'En proceso de revisión') . '</li>';
                        ?>

                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($dias_asistencia) ? $dias_asistencia : 'En proceso de revisión') . '</li>';
                        ?>



                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Pie del Modal -->
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Docente asignado</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($nombre_completo_docente) ?  $nombre_completo_docente : ' En proceso de asignación') . '</p>';

              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($telefono_docente) ?  $telefono_docente : 'S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>


        <div class="col-md-4">
          <div class="card border-light shadow-sm h-100" style="background-color: #F1E2DC;">
            <div class="card-body text-center">
              <h6 class="text-dark">Tutor asignado empresa</h6>
              <?php

              echo '<p class="text-muted">' . (!empty($tutor_asignado) ?  $tutor_asignado : ' En proceso de asignación') . '</p>';
              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($contacto_empresa) ?   $contacto_empresa : ' S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>




      </div>

    </div>




  </div>
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
          <a href="https://x.com/ComunidadTESCI?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor" class="me-2"><i
              class="fab fa-twitter"></i></a>
          <a href="https://www.instagram.com/comunidad.tesci/p/Cn4jF-CObxF/" class="me-2"><i
              class="fab fa-instagram"></i></a>
          <a href="https://tesci.edomex.gob.mx/"><i class="fas fa-globe"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts de Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <!-- CAMBIO DE COLOR Alondra-->
   <script src="../Controlador/jsCambioTemaProfesores.js"></script>
  <!-- CAMBIO DE COLOR FIN-->
</body>

</html>