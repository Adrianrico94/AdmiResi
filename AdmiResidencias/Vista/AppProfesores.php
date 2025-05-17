<?php
session_start();

// Tiempo de inactividad máximo (en segundos)
$inactive_time = 60; // 1 minuto

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

  // Consulta para obtener el id_docente, nombre completo, telefono_docente, correo, clave_profesor y observaciones
  $sql_docente = "SELECT d.id_docente, d.telefono_docente, u.nombre, u.apellido_paterno, u.apellido_materno, 
                           u.correo_electronico, d.clave_profesor, d.observaciones 
                    FROM Docentes d 
                    JOIN Usuarios u ON d.correo_institucional = u.correo_electronico 
                    WHERE d.correo_institucional = ?";
  $stmt_docente = $conn->prepare($sql_docente);
  $stmt_docente->bind_param("s", $user_email);
  $stmt_docente->execute();
  $result_docente = $stmt_docente->get_result();

  // Verificar si se encontró el docente
  if ($result_docente->num_rows > 0) {
    $row_docente = $result_docente->fetch_assoc();
    $id_docente = $row_docente['id_docente']; // Obtener el id_docente
    $telefono_docente = $row_docente['telefono_docente']; // Obtener el teléfono del docente
    $nombre = $row_docente['nombre']; // Obtener el nombre
    $apellido_paterno = $row_docente['apellido_paterno']; // Obtener el primer apellido
    $apellido_materno = $row_docente['apellido_materno']; // Obtener el segundo apellido
    $nombre_completo = $nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno; // Construir el nombre completo
    $correo_docente = $row_docente['correo_electronico']; // Obtener el correo
    $clave_profesor = $row_docente['clave_profesor']; // Obtener la clave del profesor
    $observaciones = $row_docente['observaciones']; // Obtener las observaciones
  } else {
    echo "No se encontró un docente con el correo: $user_email";
  }


  // Mostrar la información del docente
  if (isset($nombre_completo) && isset($telefono_docente) && isset($correo_docente) && isset($clave_profesor) && isset($observaciones)) {
    // echo "<p>Nombre completo del docente: $nombre_completo</p>";
    // echo "<p>Nombre: $nombre</p>"; // Mostrar solo el nombre
    // echo "<p>Primer apellido: $apellido_paterno</p>"; // Mostrar solo el primer apellido
    // echo "<p>Teléfono del docente: $telefono_docente</p>";
    // echo "<p>Correo del docente: $correo_docente</p>";
    // echo "<p>Clave del profesor: $clave_profesor</p>";
    // echo "<p>Observaciones: $observaciones</p>";
  }




  // Mostrar la información del docente
  if (isset($nombre_completo) && isset($telefono_docente) && isset($correo_docente) && isset($clave_profesor) && isset($observaciones)) {


    // Consulta para obtener los alumnos asignados al docente
    $sql_alumnos = "SELECT a.id_alumno, a.matricula, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo, 
    a.carrera, a.horario_asignado, u.correo_electronico, a.observaciones,a.proyecto_asignado, a.avance, a.documento, a.notificacion
    FROM Alumnos a 
    JOIN Asignaciones asg ON a.id_alumno = asg.id_alumno 
    JOIN Usuarios u ON a.id_alumno = u.id_usuario
    WHERE asg.id_docente = ?";

    $stmt_alumnos = $conn->prepare($sql_alumnos);
    $stmt_alumnos->bind_param("i", $id_docente);
    $stmt_alumnos->execute();
    $result_alumnos = $stmt_alumnos->get_result();

    // Guarda todos los datos en un array
    $alumnos = [];
    while ($row = $result_alumnos->fetch_assoc()) {
        $alumnos[] = $row;
    }

    // Mostrar la tabla de alumnos
    if ($result_alumnos->num_rows > 0) {
    } else {
      echo "<p>No hay alumnos asignados a este docente.</p>";
    }
  }

  // Cerrar conexiones
  $stmt_docente->close();
  $conn->close();
} else {
  echo "No se ha iniciado sesión.";
}


?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profesores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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
      }, 200000); // 1 minuto = 60000 ms
    }

    // Eventos para detectar movimiento o clics
    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer;
    window.ontouchstart = resetTimer;
    window.onscroll = resetTimer;
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
        <img src="../Recursos/img/logo/rino.png" alt="Logo" width="70" height="40" />
      </a>
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link fw-bolder" href="#">ADMINISTRADOR DE RESIDENCIAS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-bolder" href="#">PROFESORES</a>
        </li>
      </ul>

      <form class="d-flex">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input class="form-control search-bar me-2" type="search" placeholder="Buscar" aria-label="Buscar" id="buscar"/>
        </div>
        <button class="btn btn-search" type="submit">Buscar</button>
      </form>

      <script>
        //Busqueda de datos
        document.getElementById("buscar").addEventListener("input",onInputChangue);
        function onInputChangue(){
          let inputText = document.getElementById("buscar").value.toString().toLowerCase();
          //console.log(inputText);
          let tabla = document.getElementById("cuerpo");
          let filas = tabla.getElementsByTagName("tr");
          for (let i = 0; i < filas.length; i++) {
            let conNombre = filas[i].cells[1].textContent.toString().toLowerCase();
            let conNumero = filas[i].cells[0].textContent.toString().toLowerCase();
            if (conNombre.indexOf(inputText)===-1 && conNumero.indexOf(inputText)===-1){
              filas[i].style.visibility="collapse";
            }else{
              filas[i].style.visibility="";
            }
          }
        }
      </script>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <?php
        $hayNotificaciones = false;
        foreach ($alumnos as $alumno) {
          if ($alumno['notificacion'] !== null) {
            $hayNotificaciones = true;
            break; 
          }
        }
        ?>
        <div class="dropdown">
          <a class="btn m-2 d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if ($hayNotificaciones): ?>
              <i class="bi bi-bell-fill text-danger"></i> <!-- campana roja si hay notificaciones -->
            <?php else: ?>
              <i class="bi bi-bell"></i> <!-- campana normal -->
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#">NOTIFICACIÓNES</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <?php
            foreach ($alumnos as $alumno) {
              if ($alumno['notificacion'] === null){
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-person-fill'></i> Se le ha asignado a <strong>{$alumno['nombre_completo']}</strong> como nuevo alumno. Revise su propuesta para el proyecto</a></li>";                            
              }elseif ($alumno['notificacion'] == 6) {
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-person-fill'></i> El alumno <strong>{$alumno['nombre_completo']}</strong> ha sido eliminado.</a></li>";              
              }elseif ($alumno['notificacion'] == 3) {
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-file-earmark-text-fill'></i> Se ha recibido un documento nuevo de <strong>{$alumno['nombre_completo']}</strong>.</a></li>";              
              }elseif ($alumno['notificacion'] == 2) {
                echo "<li><a class='dropdown-item' href='#'><i class='bi bi-file-earmark-text-fill'></i> <strong>{$alumno['nombre_completo']}</strong> ha propuesto un nuevo proyecto.</a></li>";              
              }
            }
            ?>            
          </ul>
        </div>

        <div class="dropdown">
          <a class="btn btn-outline-dark dropdown-toggle btn m-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle " style="font-size: 25px;"></i>
            <span style="font-size: 18px;"><?php echo "$nombre $apellido_paterno"; ?></span>
          </a>


          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#"><?php echo "Docente"; ?></a></li>

            <li><a class="dropdown-item" href="#"><?php echo "<p> $nombre_completo</p>"; ?></a></li>




            <li><a class="dropdown-item" href="#"><?php echo $_SESSION['user_email']; ?></a></li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li><a class="dropdown-item" href="#">

            <li><a class="dropdown-item" href="#"><?php echo "<p>Teléfono: $telefono_docente</p>"; ?></a></li>
            </a></li>
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


  <!-- Modal Cargando web -->
  <div class="modal fade" id="miModal" tabindex="-1" aria-labelledby="miModalLabel" aria-hidden="true"> 
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">

      <!-- Encabezado del modal -->
      <div class="modal-header border-0">
        <h5 class="modal-title w-100" id="miModalLabel">¡Bienvenido Profesor al Sistema TESCI!</h5>
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


  <!--Fin Modal Cargando web -->




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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" onclick="cerrarSesion()">
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
      <a href="#" class="text-center active">
        <i class="bi bi-house-door-fill"></i>
        <span>Inicio</span>
      </a>
      <a href="#" class="text-center">
        <i class="bi bi-person-fill"></i>
        <span>Mis datos</span>
      </a>
      <a href="#resitentes" class="text-center">
        <i class="bi bi-mortarboard-fill"></i>
        <span>Residentes</span>
      </a>
      <a href="#" class="text-center" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right"></i>
        <span>Salir</span>
      </a>
    </div>

    <!-- Contenido principal -->
    <div class="content pt-0 mt-0 ">
      <div class="col-md-12 p-0" w>
        <div class="card border-light shadow-sm " style="background-color: #8a2036; color: white;">
          <div class="card-body text-center">

            <p class="mb-2"> <?php echo "<h1>Proyección General de residencias</h1>"; ?></p>
            <h6 class="text-dark text-white"><strong>
                <h2><?php echo "<p> Profesores </p>"; ?></h2>
              </strong></h6>

          </div>
        </div>
      </div>


      <div class="col-md-4 pt-3" style="height: 150px;">
        <div class="card  shadow-sm" style="background-color: #efe1ca;">
          <div class="card-body text-center p-0">
            <h6 class="text-dark">
              <strong>
                <h2 style="color: #bf955a;"><?php echo "<p> $nombre_completo</p>"; ?></h2>
              </strong>
            </h6>
            <p>Ahora puedes visualizar a los residentes asignados.</p>
          </div>
        </div>
      </div>





      <div class="col-12">
        <div class="card rounded-3" style="background-color: #efe1ca;">
          <div class="text-center my-2 mb-0 flex-grow-1 fs-6 fs-md-4 pt-2">
            <h3 id="resitentes">ALUMNOS ASIGNADOS</h3>

            <?php
            // Código ya existente de sesión y conexión

            echo '<div class="tabla-scroll" style="max-height: 200px; overflow-y: auto;">'; // Contenedor con scroll
            echo '<table class="table table-striped table-hover table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nombre</th>
                            <th>Carrera</th>
                            <th>Horario</th>
                            <th>Correo</th>
                            <th>Más detalles</th>
                            <th>Documentos</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo">';

            foreach ($alumnos as $alumno) {
              $per = ($alumno['avance'] / 31) * 100;
              echo "<tr>
                        <td>{$alumno['matricula']}</td>
                        <td>{$alumno['nombre_completo']}</td>
                        <td>{$alumno['carrera']}</td>
                        <td>{$alumno['horario_asignado']}</td>
                        <td>{$alumno['correo_electronico']}</td>
                        <td>
                            <!-- Botón para abrir el Modal -->
                            <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modal_{$alumno['id_alumno']}'>
                                Mostrar
                            </button>
                            
                            <!-- Modal -->
                            <div class='modal fade' id='modal_{$alumno['id_alumno']}' tabindex='-1' aria-labelledby='modalLabel_{$alumno['id_alumno']}' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <!-- Cabecera del Modal -->
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='modalLabel_{$alumno['id_alumno']}'>Detalles del Alumno</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <!-- Cuerpo del Modal -->
                                        <div class='modal-body'>
                                            <p><strong>Matrícula:</strong> {$alumno['matricula']}</p>
                                            <p><strong>Nombre Completo:</strong> {$alumno['nombre_completo']}</p>
                                            <p><strong>Carrera:</strong> {$alumno['carrera']}</p>
                                            <p><strong>Horario Asignado:</strong> {$alumno['horario_asignado']}</p>
                                            <p><strong>Correo:</strong> {$alumno['correo_electronico']}</p>
                                            <p><strong>Proyecto Asignado:</strong> {$alumno['proyecto_asignado']}</p>


                                            <!-- Mostrar las observaciones en el modal -->
                                           <div class='mb-3'>
                                                <label for='observaciones' class='form-label'><strong>Descripción del proyecto:</strong></label>
                                                    <textarea 
                                                        id='observaciones' 
                                                        class='form-control' 
                                                        rows='4' 
                                                        readonly 
                                                        style='resize: none; overflow-y: scroll;'>
{$alumno['observaciones']}
                                                    </textarea>
                                            </div>
                                        </div>
                                        <!-- Pie del Modal -->
                                        <div class='modal-footer d-flex justify-content-center'>
                                          

                                            <!-- Botón para Aceptar el Proyecto y actualizar la notificación -->
                                            <form action='' method='POST' class='d-inline'>
                                                <input type='hidden' name='id_alumno' value='{$alumno['id_alumno']}'>
                                                <button type='submit' name='aceptar_proyecto' class='btn btn-success'>Aceptar Proyecto</button>
                                            </form>

                                            <!-- Botón para Rechazar el Proyecto y actualizar la notificación a 0 -->
                                            <form action='' method='POST' class='d-inline'>
                                                <input type='hidden' name='id_alumno' value='{$alumno['id_alumno']}'>
                                                <button type='submit' name='rechazar_proyecto' class='btn btn-danger'>Rechazar Proyecto</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td><td>
                            <!-- Botón para abrir el Modal -->
                            <button type='button' class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#modal2_{$alumno['id_alumno']}'>
                                Ver avance
                            </button>
                            <!-- Modal -->
                            <div class='modal fade' id='modal2_{$alumno['id_alumno']}' tabindex='-1' aria-labelledby='modalLabel_{$alumno['id_alumno']}' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <!-- Cabecera del Modal -->
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='modalLabel_{$alumno['id_alumno']}'>Documentos del proyecto</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <!-- Cuerpo del Modal -->
                                        <div class='modal-body'>
                                            <p><strong>Avanze de proyecto:</strong></p>
                                            <div class='progress' role='progressbar' aria-label='Success example'>
                                            
                                            <div class='progress-bar text-bg-success' style='width: $per%'>{$alumno['avance']}/31</div>
                                          </div>
                                            <table class='table table-striped table-hover table-bordered mt-3'>
                                                <tr>
                                                    <th>Orden</th>
                                                    <th>Ver</th>
                                                    <th>Estado</th>
                                                </tr>
                                          ";
                                          $directorio = "C:/xampp/htdocs/generarword-Git/Alumnos/{$alumno['matricula']}/";
                                          if (is_dir($directorio)){
                                          $archivos = array_diff(scandir($directorio), array('.', '..'));

                                          // Obtener las fechas de creación asociadas a cada archivo
                                          $fechas = [];
                                          foreach ($archivos as $archivo) {
                                              $rutaCompleta = $directorio . $archivo;
                                              $fechas[$archivo] = filectime($rutaCompleta); // Puedes usar filemtime() si prefieres fecha de modificación
                                          }

                                          // Ordenar por fecha descendente (más reciente primero)
                                          asort($fechas);
                                          $n = 0;
                                          foreach ($fechas as $archivo => $fecha) {
                                            //$fechaFormateada = date("Y-m-d H:i:s", $fecha);
                                            if ($archivo !== '.' && $archivo !== '..') {
                                              $n ++;
                                              $ruta = htmlspecialchars("C:/xampp/htdocs/generarword-Git/Alumnos/{$alumno['matricula']}/$archivo");
                                                echo "<tr>
                                                        <td>$n</td>
                                                        <td>
                                                            <form action='$ruta' method='post' target='_blank'>
                                                                <button class='btn' type='submit' onclick=\"openf('{$alumno['matricula']}', '$archivo')\">$archivo</button>
                                                            </form>
                                                        </td>
                                                        <td>";
                                                        if ($n-1 == $alumno['avance']){echo "<!-- Botón para Aceptar el documento -->
                                            <form data-id-alumno='{$alumno['id_alumno']}' method='POST' class='d-inline form-btns aceD'>
                                                <button type='submit' name='aceptar_documento' class='btn btn-success rounded-pill' id='Btn{$alumno['id_alumno']}'><i class='bi bi-check-lg'></i></button>
                                            </form>
                                            <!-- Botón para Rechazar el documento -->
                                            <form data-id-alumno='{$alumno['id_alumno']}' data-ruta='$ruta' method='POST' class='d-inline form-btns reD'>
                                                <button type='submit' name='rechazar_documento' class='btn btn-danger rounded-pill'><i class='bi bi-trash-fill'></i></button>";
                                                        }else if ($n-1< $alumno['avance']){echo "Revisado";
                                                        }else{echo "Por revisar";}
                                                        echo "</form></td>
                                                      </tr>";
                                            }
                                        }}echo "
                                        </table>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>";
            }

            echo '</tbody></table>';
            echo '</div>'; // Cierre del contenedor

            // Código PHP para procesar la actualización al hacer clic en "Aceptar Proyecto"
            if (isset($_POST['aceptar_proyecto'])) {
              $id_alumno = $_POST['id_alumno'];

              // Configuración de la conexión a la base de datos
              $servername = "localhost";
              $username = "root";
              $password = "";
              $dbname = "residencias_db";

              $conn = new mysqli($servername, $username, $password, $dbname);

              // Verificar si hay errores de conexión
              if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
              }

              // Verificar si el ID del alumno existe
              $check_sql = "SELECT * FROM alumnos WHERE id_alumno = ?";
              $check_stmt = $conn->prepare($check_sql);
              $check_stmt->bind_param("i", $id_alumno);
              $check_stmt->execute();
              $result = $check_stmt->get_result();

              if ($result->num_rows == 0) {
                echo "<script>alert('El ID del alumno no existe.');</script>";
                exit();
              }

              // Si el alumno existe, proceder con la actualización
              $sql = "UPDATE alumnos SET notificacion = 1 WHERE id_alumno = ?";
              $stmt = $conn->prepare($sql);

              if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
              }

              $stmt->bind_param("i", $id_alumno);
              $stmt->execute();

              if ($stmt->affected_rows > 0) {
                // Actualización exitosa
              } else {
                echo "<script>alert('No se pudo actualizar la notificación.');</script>";
              }

              $stmt->close();
              $conn->close();
            }

            // Código PHP para procesar la actualización al hacer clic en "Rechazar Proyecto"
            if (isset($_POST['rechazar_proyecto'])) {
              $id_alumno = $_POST['id_alumno'];

              // Configuración de la conexión a la base de datos
              $servername = "localhost";
              $username = "root";
              $password = "";
              $dbname = "residencias_db";

              $conn = new mysqli($servername, $username, $password, $dbname);

              // Verificar si hay errores de conexión
              if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
              }

              // Verificar si el ID del alumno existe
              $check_sql = "SELECT * FROM alumnos WHERE id_alumno = ?";
              $check_stmt = $conn->prepare($check_sql);
              $check_stmt->bind_param("i", $id_alumno);
              $check_stmt->execute();
              $result = $check_stmt->get_result();

              if ($result->num_rows == 0) {
                echo "<script>alert('El ID del alumno no existe.');</script>";
                exit();
              }

              // Si el alumno existe, proceder con la actualización
              $sql = "UPDATE alumnos SET notificacion = 0 WHERE id_alumno = ?";
              $stmt = $conn->prepare($sql);

              if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
              }

              $stmt->bind_param("i", $id_alumno);
              $stmt->execute();

              if ($stmt->affected_rows > 0) {
                // Actualización exitosa
              } else {
                echo "<script>alert('No se pudo actualizar la notificación.');</script>";
              }

              $stmt->close();
              $conn->close();
            }

            // Código PHP para procesar la actualización al hacer clic en "Aceptar Documento"
            if (isset($_POST['aceptar_documento'])) {
              $id_alumno = $_POST['id_alumno'];

              // Configuración de la conexión a la base de datos
              $servername = "localhost";
              $username = "root";
              $password = "";
              $dbname = "residencias_db";

              $conn = new mysqli($servername, $username, $password, $dbname);

              // Verificar si hay errores de conexión
              if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
              }

              // Verificar si el ID del alumno existe
              $check_sql = "SELECT * FROM alumnos WHERE id_alumno = ?";
              $check_stmt = $conn->prepare($check_sql);
              $check_stmt->bind_param("i", $id_alumno);
              $check_stmt->execute();
              $result = $check_stmt->get_result();

              if ($result->num_rows == 0) {
                echo "<script>alert('El ID del alumno no existe.');</script>";
                exit();
              }

              // Si el alumno existe, proceder con la actualización
              $sql = "UPDATE alumnos SET avance = avance + 1 WHERE id_alumno = ?";
              $stmt = $conn->prepare($sql);

              if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
              }

              $stmt->bind_param("i", $id_alumno);
              $stmt->execute();

              if ($stmt->affected_rows > 0) {
                // Actualización exitosa
              } else {
                echo "<script>alert('No se pudo actualizar el avanze.');</script>";
              }

              $stmt->close();
              $conn->close();
            }

            // Código PHP para procesar la actualización al hacer clic en "Rechazar Documento"
            if (isset($_POST['rechazar_documento'])) {
              //$mensaje='';
            //$archivo = basename($_POST['archivo']); // Evita rutas maliciosas
                $ruta = $_POST['archivo'];
            
                if (file_exists($ruta)) {
                    if (unlink($ruta)) {
                        /*$mensaje = "Archivo <strong>$archivo</strong> eliminado correctamente.";
                    } else {
                        $mensaje = "No se pudo eliminar el archivo <strong>$archivo</strong>.";
                    }*/
                } /*else {
                    $mensaje = "El archivo <strong>$archivo</strong> no existe.";*/
                }
            }
            ?>

            <script>
              document.addEventListener("DOMContentLoaded", () => {
                  const forms = document.querySelectorAll(".form-btns");
                  forms.forEach(form => {
                      form.addEventListener("submit", function(e) {
                          e.preventDefault(); // evita el reinicio de la página
                          const B = this.dataset.idAlumno;
                          const button = document.getElementById('Btn'+B);
                          // Deshabilita el botón que fue clicado
                          button.disabled = true;

                          // Encuentra el contenedor (la celda <td>) y deshabilita también el otro botón
                          const container = button.closest('td');
                          const buttons = container.querySelectorAll('button');
                          buttons.forEach(btn => btn.disabled = true);
              });});}); 
              document.addEventListener("DOMContentLoaded", () => {
                  const forms = document.querySelectorAll(".aceD");
                  forms.forEach(form => {
                      form.addEventListener("submit", function(e) {
                          e.preventDefault(); // evita el reinicio de la página
                          if (confirm('Este documento se guardará permanentemente.')) {
                            try {
                              const formData = new FormData();
                              formData.append('id_alumno', this.dataset.idAlumno);
                              formData.append('aceptar_documento', true);

                              fetch('', {
                                  method: 'POST',
                                  body: formData
                              })
                              .then(response => response.json())
                              .then(data => {
                                  // Actualiza el DOM con los nuevos datos sin recargar la página
                                  console.log(data);
                              });
                              alert("Documento guardado.");
                            } catch (error) {
                                
                            }
                          }
                          
              });});}); 
              document.addEventListener("DOMContentLoaded", () => {
                  const forms = document.querySelectorAll(".reD");
                  forms.forEach(form => {
                      form.addEventListener("submit", function(e) {
                          e.preventDefault(); // evita el reinicio de la página
                          if (confirm('¡Este documento se borrará permanentemente!')) {
                            try {
                              const formData = new FormData();
                              formData.append('archivo', this.dataset.ruta);
                              formData.append('rechazar_documento', true);

                              fetch('', {
                                  method: 'POST',
                                  body: formData
                              })
                              .then(response => response.json())
                              .then(data => {
                                  // Actualiza el DOM con los nuevos datos sin recargar la página
                                  console.log(data);
                              });
                              alert("Documento borrado.");
                            } catch (error) {
                                
                            }
                          }
                          
              });});}); 

              function openf(control,documento) {
                fetch("../Modelo/AbrirCarpeta.php", {
                  method: "POST",
                  headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                  },
                  body: `control=${encodeURIComponent(control)}&documento=${encodeURIComponent(documento)}`
                })
                .then(response => response.text())
                (error => {
                  console.error("Error:", error);
                });
              }
            </script>








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
            ©2024 Valdez Rico Adrian Job.
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