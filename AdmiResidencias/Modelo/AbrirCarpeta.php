<?php
  $n = trim($_POST['control']);
  $d = trim($_POST['documento']);
  shell_exec("start C:/xampp/htdocs/generarword-Git/Alumnos/$n/$d");
  //header('Location: C:/xampp/htdocs/AdmiResidencias/Alumnos/'.$n.'/Historial.pdf');
?>