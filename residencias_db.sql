-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-12-2024 a las 20:11:51
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `residencias_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id_alumno` int(11) NOT NULL,
  `matricula` varchar(20) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `proyecto_asignado` varchar(100) DEFAULT NULL,
  `carrera` varchar(100) DEFAULT NULL,
  `ingreso` date DEFAULT NULL,
  `egreso` date DEFAULT NULL,
  `telefono_alumno` varchar(20) DEFAULT NULL,
  `telefono_profesor_asignado` varchar(20) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `horario_asignado` varchar(50) DEFAULT NULL,
  `id_profesor` int(11) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `notificacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`id_alumno`, `matricula`, `empresa`, `proyecto_asignado`, `carrera`, `ingreso`, `egreso`, `telefono_alumno`, `telefono_profesor_asignado`, `observaciones`, `horario_asignado`, `id_profesor`, `id_empresa`, `notificacion`) VALUES
(1, '223107465', 'Tech Solutions', 'Este proyecto de Residencias es una plataforma web que tiene como objetivo gestionar y mostrar inf', 'Ingeniería de Sistemas', '2024-01-02', '2025-12-31', '555-6789', '555-1234', 'Este proyecto de Residencias es una plataforma web que tiene como objetivo gestionar y mostrar información sobre los estudiantes que están realizando su residencia. El sistema está diseñado para tres tipos de usuarios: alumnos, profesores y administradores.\r\n\r\nAlumnos: Pueden ver información general sobre su residencia, como las métricas y estadísticas relacionadas con su desempeño o situación.\r\n\r\nProfesores: Tienen acceso a los estudiantes que tienen asignados, pudiendo consultar sus datos personales y el progreso de su residencia.\r\n\r\nAdministradores: Son los encargados de gestionar la plataforma, ya que pueden insertar, modificar o eliminar registros tanto de alumnos como de profesores, además de tener acceso completo a la información.\r\n\r\nEl proyecto también incluye la capacidad de visualizar gráficas de métricas, y solo los profesores y administradores pueden acceder a información detallada sobre los estudiantes, mientras que los alumnos solo tienen acceso a datos generales.\r\n\r\nEste sistema busca ser una herramienta útil para centralizar la información y facilitar la gestión de las residencias.', 'Lunes a Viernes 9:00 - 10:00', NULL, 1, ''),
(4, '223107089', 'Grupo hábil ', 'Software control inventario tienda.', 'Ingeniería de Sistemas', '2024-12-10', '2024-12-31', '5517473176', '5517473174', 'El proyecto de Software de Control de Inventario para Tienda es una aplicación web diseñada para gestionar productos, ventas y compras de una tienda. Permite a los administradores agregar, editar y eliminar productos, controlar las existencias, y registrar ventas y compras. Además, genera reportes de ventas y estadísticas. La plataforma usa HTML, CSS, JavaScript, PHP y MySQL para el manejo de datos. Su objetivo es facilitar la administración del inventario, mejorar la eficiencia y ofrecer un control completo sobre el stock y las transacciones', 'Lunes a Viernes 10:00 - 11:00', 1, 2, NULL),
(5, '123456789', 'La Generosa ', 'Control de ventas Tienda.', 'Ingeniería de Sistemas', '2024-12-03', '2024-12-25', '5544332211', NULL, 'El proyecto de Control de Ventas para Tienda es una aplicación web diseñada para gestionar las ventas de una tienda de manera eficiente. Los vendedores pueden registrar ventas, actualizar inventarios en tiempo real, y generar reportes de ventas y productos más vendidos. Para el desarrollo de este sistema, se utilizan tecnologías como React para la interfaz de usuario, Node.js para el backend y la lógica de negocio, y MongoDB como base de datos para almacenar las transacciones y el inventario. La aplicación proporciona una solución práctica para controlar las ventas, el inventario y generar reportes detallados de rendimiento.', 'Lunes a Viernes 13:00 - 14:00', 2, 2, NULL),
(6, '2123213163', 'Muebles.CV', 'web app control asistencias', 'Ingeniería de Sistemas', '2024-12-02', '2024-12-31', '5567453656', NULL, 'web app control asistencias para la empresa de mueblas ', 'Lunes a Viernes 9:00 - 10:00', 2, NULL, NULL),
(7, '213312343', 'tesci', 'Administrador biblioteca ', 'Ingeniería de Sistemas', '2024-12-01', '2024-12-31', '5581325060', NULL, 'desarrollar una aplicación para el control de los libros de la biblioteca.', 'Lunes a Viernes 9:00 - 10:00', 2, NULL, NULL),
(8, '213107104', 'TESCI', 'Control asistencia biométrico ', 'Ingeniería de Sistemas', '2024-12-01', '2024-12-31', '556745342341', '5567453867', '\"Desarrollar un control de asistencia por detección biométrica con huella dactilar y con detección de rostro.\"', 'Lunes a Viernes 9:00 - 10:00', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones`
--

CREATE TABLE `asignaciones` (
  `id_asignacion` int(11) NOT NULL,
  `id_alumno` int(11) DEFAULT NULL,
  `id_docente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignaciones`
--

INSERT INTO `asignaciones` (`id_asignacion`, `id_alumno`, `id_docente`) VALUES
(1, 1, 1),
(2, 4, 1),
(3, 5, 1),
(4, 6, 2),
(5, 7, 2),
(31, 8, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id_calificacion` int(11) NOT NULL,
  `id_alumno` int(11) DEFAULT NULL,
  `id_proyecto` int(11) DEFAULT NULL,
  `calificacion` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id_calificacion`, `id_alumno`, `id_proyecto`, `calificacion`) VALUES
(1, 1, 1, 95.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL,
  `clave_profesor` varchar(20) DEFAULT NULL,
  `carrera` varchar(100) DEFAULT NULL,
  `telefono_docente` varchar(20) DEFAULT NULL,
  `correo_institucional` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `horario_asignado` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id_docente`, `clave_profesor`, `carrera`, `telefono_docente`, `correo_institucional`, `observaciones`, `horario_asignado`) VALUES
(1, 'D123', 'Ingeniería de Software', '5561738374', 'juan.perez@instituto.com', 'Docente experimentado', 'Lunes a Viernes 9:00 - 12:00'),
(2, 'Aron1231', 'Sistemas computacionales', '5567453867', 'aron.lopez@alumno.com', 'Maestro de Programacion', 'Lunes a Viernes 9:00 - 10:00'),
(82, '521VW5623FG', 'ingeniería en sistemas', '5587736354', 'eli2246162@gmail.com', 'maestra de apoyo ', 'lunes 10:00 am');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(100) DEFAULT NULL,
  `correo_empresa` varchar(100) DEFAULT NULL,
  `contacto_empresa` varchar(100) DEFAULT NULL,
  `tutor_asignado` varchar(100) DEFAULT NULL,
  `horario_asistencia` varchar(50) DEFAULT NULL,
  `dias_asistencia` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre_empresa`, `correo_empresa`, `contacto_empresa`, `tutor_asignado`, `horario_asistencia`, `dias_asistencia`) VALUES
(1, 'Tech Solutions', 'contacto@techsolutions.com', '5563928483', 'Armando Contreras Pacheco', '9:00 AM - 5:00 PM', 'Lunes a Viernes'),
(2, 'Grupo hábil ', 'Grupo hábil@gmail.com', '5564856437', 'Pable González Vásquez', 'Lunes a Viernes 9:00 - 10:00', ' 9:00 AM - 5:00 PM');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifica`
--

CREATE TABLE `notifica` (
  `id_notificacion` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `texto` int(100) NOT NULL,
  `leido` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `id_proyecto` int(11) NOT NULL,
  `nombre_proyecto` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estatus` enum('Pendiente','Aceptado','Rechazado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id_proyecto`, `nombre_proyecto`, `descripcion`, `estatus`) VALUES
(1, 'Desarrollo de Aplicación Móvil', 'Aplicación móvil para gestión de tareas', 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido_paterno` varchar(100) DEFAULT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `correo_electronico` varchar(150) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `tipo_usuario` enum('Alumno','Docente','Superusuario') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido_paterno`, `apellido_materno`, `correo_electronico`, `contrasena`, `tipo_usuario`) VALUES
(1, 'Adrian Job', 'Valdez', 'Rico ', 'adrian@correo.com', '12345', 'Alumno'),
(2, 'Juan', 'Pérez', 'Gómez', 'juan.perez@instituto.com', '12345', 'Docente'),
(3, 'Aron', 'Fonseca ', 'Rico', 'aron.lopez@alumno.com', '12345', 'Docente'),
(4, 'Antonio', 'Rodriguez', 'Ocampo', 'antoronio863@gmail.com', '12345', 'Alumno'),
(5, 'Augusto', 'Montorroso', 'del Sol', 'agusto@2345', '12345', 'Alumno'),
(6, 'Sofia', 'Gandara', 'Ortega ', 'Sofia@gmail.com', '$2y$10$x4hlpgIEL/3922mi3ZKESOJMxFJf3HO8FYAAidbZX0GiqlSyCbacK', 'Alumno'),
(7, 'OMAR URIEL', 'ANGELES', 'Mandujano', 'omar@gmail.com', '12345', 'Alumno'),
(8, 'ALONSO', 'SANCHEZ', 'GUERRA', '213107104@gmail.com', '$2y$10$RIYNC0ovRaKopM6uHWaTae0R2PF4P0dPTobzEged0vYaP1TJ59dG2', 'Alumno'),
(9, 'Óscar ', 'López ', 'Olivares', 'O2231AG312@gmail.com', '12345', 'Superusuario'),
(82, 'Elizabeth', 'Gomez ', 'Bravo', 'eli2246162@gmail.com', '$2y$10$CgMh44uBsIJGhAzo01365exA8B51ajYW/xwGjLt1Qb3NLpYXZeG26', 'Docente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id_alumno`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD KEY `id_profesor` (`id_profesor`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `id_alumno` (`id_alumno`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id_calificacion`),
  ADD KEY `id_alumno` (`id_alumno`),
  ADD KEY `id_proyecto` (`id_proyecto`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id_docente`),
  ADD UNIQUE KEY `clave_profesor` (`clave_profesor`),
  ADD UNIQUE KEY `correo_institucional` (`correo_institucional`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`),
  ADD KEY `tutor_asignado` (`tutor_asignado`);

--
-- Indices de la tabla `notifica`
--
ALTER TABLE `notifica`
  ADD PRIMARY KEY (`id_notificacion`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id_proyecto`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo_electronico` (`correo_electronico`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id_calificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de la tabla `notifica`
--
ALTER TABLE `notifica`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id_proyecto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `docentes` (`id_docente`) ON DELETE SET NULL,
  ADD CONSTRAINT `alumnos_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE SET NULL;

--
-- Filtros para la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE,
  ADD CONSTRAINT `asignaciones_ibfk_2` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE,
  ADD CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
