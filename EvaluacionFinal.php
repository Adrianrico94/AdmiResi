<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cargar la plantilla
    $template = new TemplateProcessor('Formato de evaluacion.docx');

    // Fecha de llenado
    $fecha = explode('-', $_POST['fechaLlenado']);
    $template->setValue('numDia', $fecha[2]);
    $template->setValue('mes', $fecha[1]);
    $template->setValue('numAño', $fecha[0]);

    // Datos del Alumno
    $template->setValue('nombreAlumno', $_POST['nombreAlumno']);
    $template->setValue('numControl', $_POST['numControl']);

    // Datos del Proyecto
    $template->setValue('nombreProyecto', $_POST['nombreProyecto']); 
    $template->setValue('programaEducativo', $_POST['programaEducativo']);
    // Fecha y hora
    $inicio = explode('-', $_POST['fechaInicio']);
    $template->setValue('numDiaInicio', $inicio[2]);
    $template->setValue('numMesInicio', $inicio[1]);
    $template->setValue('numAñoInicio', $inicio[0]);

    $inicio = explode('-', $_POST['fechaFinal']);
    $template->setValue('numDiaFin', $inicio[2]);
    $template->setValue('numMesFin', $inicio[1]);
    $template->setValue('numAñoFin', $inicio[0]);

    $template->setValue('nombreAsesorExte', $_POST['nombreAsesorExte']);
    $template->setValue('nombreAsesorInterno', $_POST['nombreAsesorInterno']);
   

    // Guardar el documento generado
    $archivo = 'Formato de evaluacion Final.docx';
    $template->saveAs($archivo);

    // Forzar la descarga del archivo generado
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename=$archivo");
    readfile($archivo);

    // Eliminar el archivo temporal
    unlink($archivo);
    exit;
}
?>
