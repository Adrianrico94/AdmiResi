<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cargar la plantilla
    $template = new TemplateProcessor('formato de seguimiento de residencia profesional.docx');

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

    // Fecha y hora
    $inicio = explode('-', $_POST['fechaInicio']);
    $template->setValue('numDiaInicio', $inicio[2]);
    $template->setValue('numMesInicio', $inicio[1]);
    $template->setValue('numAñoInicio', $inicio[0]);
    $template->setValue('numHora', $_POST['horaInicio']);

    // Descripción del Proyecto
    $template->setValue('objetivoProyecto', $_POST['objetivoProyecto']);

    // Contacto: Solo dejar la palomita de los seleccionados y limpiar los demás
    $template->setValue('tel', isset($_POST['tel']) ? '✔' : '');
    $template->setValue('Corr', isset($_POST['Corr']) ? '✔' : '');
    $template->setValue('InS', isset($_POST['InS']) ? '✔' : '');
    $template->setValue('otros', isset($_POST['otros']) ? '✔' . $_POST['otrosTexto'] : '');

    // Selección de Seguimiento: Solo dejar la palomita de los seleccionados y limpiar los demás
    $template->setValue('PriS', isset($_POST['seguimiento']) && $_POST['seguimiento'] == 'Primer Seguimiento' ? '✔ ' : '');
    $template->setValue('SegS', isset($_POST['seguimiento']) && $_POST['seguimiento'] == 'Segundo Seguimiento' ? '✔' : '');
    $template->setValue('TerS', isset($_POST['seguimiento']) && $_POST['seguimiento'] == 'Tercer Seguimiento' ? '✔ Tercer Seguimiento' : '');
    $template->setValue('CuaS', isset($_POST['seguimiento']) && $_POST['seguimiento'] == 'Cuarto Seguimiento' ? '✔ Cuarto Seguimiento' : '');

    // Asesor Externo
    $template->setValue('nombreAsesorExt', $_POST['nombreAsesorExt']);
    $template->setValue('CargoEncargado', $_POST['CargoEncargado']);
    $template->setValue('observacionesAsesorExt', $_POST['observacionesAsesorExt']);

    // Avance y Observaciones
    $template->setValue('numAvance', $_POST['numAvance']);
    $template->setValue('observacionesAsesorInt', $_POST['observacionesAsesorInt']);

    // Guardar el documento generado
    $archivo = 'Anteproyecto_Llenado.docx';
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
