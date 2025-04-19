<?php
require 'vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capturar y validar campos
    $campos = [
        'fechaCompleta', 'fechaFinal',
        'nombreEmpresa', 'NombreJefadivision',
        'Nombredelestudiante', 'numMatricula',
        'area', 'nombredelProyecto',
        'horaInicio', 'horaFinal',
        'diaInicio', 'diaFinal', 
        'nombredelEncargadoUN', 'areaEncargada',
        'diaAsistencia'
    ];

    foreach ($campos as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo "El campo $campo es obligatorio.";
            exit;
        }
        $$campo = htmlspecialchars(trim($_POST[$campo]));
    }

    $fechaInicio = DateTime::createFromFormat('Y-m-d', $fechaCompleta);
    $fechaFin = DateTime::createFromFormat('Y-m-d', $fechaFinal);

    if (!$fechaInicio || !$fechaFin) {
        echo "Las fechas ingresadas no son válidas.";
        exit;
    }

    // Extraer partes de las fechas
    $numDia = $fechaInicio->format('d');
    $mes = $fechaInicio->format('F');
    $numAño = $fechaInicio->format('Y');

    $numDiaFin = $fechaFin->format('d');
    $mesFin = $fechaFin->format('F');
    $numAñoFin = $fechaFin->format('Y');

    $meses = [
        'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo',
        'April' => 'abril', 'May' => 'mayo', 'June' => 'junio',
        'July' => 'julio', 'August' => 'agosto', 'September' => 'septiembre',
        'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
    ];
    $mes = $meses[$mes] ?? $mes;
    $mesFin = $meses[$mesFin] ?? $mesFin;

    try {
        $template = new TemplateProcessor('CartaAceptacion.docx');

        $template->setValue('nombredelaEmpresa', $nombreEmpresa);
        $template->setValue('numDia', $numDia);
        $template->setValue('mes', $mes);
        $template->setValue('numAño', $numAño);
        $template->setValue('nombreDelaJefaDeCarrera', $NombreJefadivision);
        $template->setValue('nombredelEstudiante', $Nombredelestudiante);
        $template->setValue('numMatricula', $numMatricula);
        $template->setValue('numDiaFin', $numDiaFin);
        $template->setValue('mesFin', $mesFin);
        $template->setValue('numAñoFin', $numAñoFin);
        $template->setValue('area', $area);
        $template->setValue('nombredelProyecto', $nombredelProyecto);
        $template->setValue('horaInicio', $horaInicio);
        $template->setValue('horaFinal', $horaFinal);
        $template->setValue('diaInicio', $diaInicio);
        $template->setValue('diaFinal', $diaFinal);
        $template->setValue('nombredelEncargadoUN', $nombredelEncargadoUN);
        $template->setValue('areaEncargada', $areaEncargada);
        $template->setValue('diaAsistencia', $diaAsistencia);

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
