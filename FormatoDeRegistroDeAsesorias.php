<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = [
        'fechaCompleta',
        'departamentoAcademico', 'nombreAlumno', 'numControl',
        'nombreProyecto', 'fechaInicio', 'fechaFin',
        'empresa', 'numAsesoria', 'tipoAsesoria',
        'temaAserorar', 'solucionAsesoria', 'nombreAsesorInt'
    ];

    foreach ($campos as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo "El campo $campo es obligatorio.";
            exit;
        }
        $$campo = htmlspecialchars(trim($_POST[$campo]));
    }

    // Fecha actual (formato largo)
    $fechaActual = DateTime::createFromFormat('Y-m-d', $fechaCompleta);
    $numDia = $fechaActual->format('d');
    $mesEn = $fechaActual->format('F');
    $numAño = $fechaActual->format('Y');

    $meses = [
        'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo',
        'April' => 'abril', 'May' => 'mayo', 'June' => 'junio',
        'July' => 'julio', 'August' => 'agosto', 'September' => 'septiembre',
        'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
    ];
    $mes = $meses[$mesEn] ?? $mesEn;

    // Fecha inicio
    $fechaIni = DateTime::createFromFormat('Y-m-d', $fechaInicio);
    $numDiaInicio = $fechaIni->format('d');
    $numMesInicio = $fechaIni->format('m');
    $numAñoInicio = $fechaIni->format('Y');

    // Fecha fin
    $fechaFinal = DateTime::createFromFormat('Y-m-d', $fechaFin);
    $numDiaFin = $fechaFinal->format('d');
    $numMesFin = $fechaFinal->format('m');
    $numAñoFin = $fechaFinal->format('Y');

    try {
        $template = new TemplateProcessor('Asesoria semanal.docx');

        // Fechas
        $template->setValue('numDia', $numDia);
        $template->setValue('mes', $mes);
        $template->setValue('numAño', $numAño);

        $template->setValue('numDiaInicio', $numDiaInicio);
        $template->setValue('numMesInicio', $numMesInicio);
        $template->setValue('numAñoInicio', $numAñoInicio);

        $template->setValue('numDiaFin', $numDiaFin);
        $template->setValue('numMesFin', $numMesFin);
        $template->setValue('numAñoFin', $numAñoFin);

        // Otros datos
        $template->setValue('departamentoAcademico', $departamentoAcademico);
        $template->setValue('nombreAlumno', $nombreAlumno);
        $template->setValue('numControl', $numControl);
        $template->setValue('nombreProyecto', $nombreProyecto);
        $template->setValue('empresa', $empresa);
        $template->setValue('numAsesoria', $numAsesoria);
        $template->setValue('tipoAsesoria', $tipoAsesoria);
        $template->setValue('temaAserorar', $temaAserorar);
        $template->setValue('solucionAsesoria', $solucionAsesoria);
        $template->setValue('nombreAsesorInt', $nombreAsesorInt);

        // Generar documento
        $archivo = 'documento_generado.docx';
        $template->saveAs($archivo);

        header("Content-Disposition: attachment; filename=$archivo");
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        readfile($archivo);
        unlink($archivo);
        exit;
    } catch (Exception $e) {
        echo "Error al generar el documento: " . $e->getMessage();
        exit;
    }
}
?>
