<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$template = new TemplateProcessor('Anteproyecto Residencias.docx');

// Fecha de llenado
$fecha = explode('-', $_POST['fechaLlenado']);
$template->setValue('numDia', $fecha[2]);
$template->setValue('mes', $fecha[1]);
$template->setValue('numAño', $fecha[0]);

// Datos alumno
$template->setValue('nombreAlumno', $_POST['nombreAlumno']);
$template->setValue('numControl', $_POST['numControl']);
$template->setValue('numSemestre', $_POST['numSemestre']);
$template->setValue('numCreditos', $_POST['numCreditos']);

// Empresa
$template->setValue('empresa', $_POST['empresa']);
$template->setValue('giro', $_POST['giro']);
$template->setValue('direccionEmpresa', $_POST['direccionEmpresa']);
$template->setValue('numTelefono', $_POST['numTelefono']);
$template->setValue('correoEmpresa', $_POST['correoEmpresa']);

// Proyecto
$template->setValue('areaProyecto', $_POST['areaProyecto']);
$template->setValue('nombreProyecto', $_POST['nombreProyecto']);

$inicio = explode('-', $_POST['fechaInicio']);
$fin = explode('-', $_POST['fechaFin']);

$template->setValue('numDiaInicio', $inicio[2]);
$template->setValue('numMesInicio', $inicio[1]);
$template->setValue('numAñoInicio', $inicio[0]);

$template->setValue('numDiaFin', $fin[2]);
$template->setValue('numMesFin', $fin[1]);
$template->setValue('numAñoFin', $fin[0]);

$template->setValue('horaInicio', $_POST['horaInicio']);
$template->setValue('horaFinal', $_POST['horaFinal']);

// Personas (solo una se selecciona)
$template->setValue('unaPersona', ($_POST['personaSeleccionada'] == 'unaPersona') ? 'X' : '');
$template->setValue('dosPersona', ($_POST['personaSeleccionada'] == 'dosPersona') ? 'X' : '');
$template->setValue('tresPersona', ($_POST['personaSeleccionada'] == 'tresPersona') ? 'X' : '');
$template->setValue('cuatroPersona', ($_POST['personaSeleccionada'] == 'cuatroPersona') ? 'X' : '');

// Responsables
$template->setValue('representanteEmpresa', $_POST['representanteEmpresa']);
$template->setValue('nombreAsesorInt', $_POST['nombreAsesorInt']);
$template->setValue('nombreJefaDeCarrera', $_POST['nombreJefaDeCarrera']);

 // justificacion
 $template->setValue('objetivoProyecto', $_POST['objetivoProyecto']);
 $template->setValue('justificaProyecto', $_POST['justificaProyecto']);

 //DESCRIPCION DE ACTIVIDADES:
 $template->setValue('actividaFaseUno', $_POST['actividaFaseUno']);
 $template->setValue('actividaFaseDos', $_POST['actividaFaseDos']);
 $template->setValue('actividaFaseTres', $_POST['actividaFaseTres']);
 $template->setValue('actividaFaseCuatro', $_POST['actividaFaseCuatro']);

 


// Guardar
$archivo = 'Anteproyecto_Llenado.docx';
$template->saveAs($archivo);

header("Content-Disposition: attachment; filename=$archivo");
readfile($archivo);
unlink($archivo);
exit;
