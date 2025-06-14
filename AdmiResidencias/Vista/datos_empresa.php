<?php
// datos_empresa.php

$host = "localhost";
$usuario = "root";
$password = "";
$basedatos = "residencias_db";

$conexion = new mysqli($host, $usuario, $password, $basedatos);
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

$sql = "SELECT * FROM empresa";
$resultado = $conexion->query($sql);

$empresas = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $empresas[] = $fila;
    }
}

$conexion->close();
?>
