<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $campos = [
        'fechaCompleta', // Nueva fecha desde calendario
        'nombreEmpresa',
        'Nombredelestudiante','numMatricula', 'area','NombreProyec',
        'horaInicio','días', 'horaFinal', 'NombreJefadivision'
    ];

    foreach ($campos as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo "El campo $campo es obligatorio.";
            exit;
        }
        $$campo = htmlspecialchars(trim($_POST[$campo]));
    }

    // Convertir la fecha seleccionada (formato: YYYY-MM-DD)
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaCompleta);
    if (!$fecha) {
        echo "La fecha ingresada no es válida.";
        exit;
    }

    // Extraer día, mes y año
    $numDia = $fecha->format('d');       // Día con dos cifras
    $mes = $fecha->format('F');          // Mes en inglés (Ej: April)
    $numAño = $fecha->format('Y');       // Año con cuatro cifras

    // Opcional: traducir el mes a español
    $meses = [
        'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo',
        'April' => 'abril', 'May' => 'mayo', 'June' => 'junio',
        'July' => 'julio', 'August' => 'agosto', 'September' => 'septiembre',
        'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
    ];
    $mes = $meses[$mes] ?? $mes;

    try {
        $template = new TemplateProcessor('Carta termino.docx');

        // Reemplazar los marcadores en el documento Word
        $template->setValue('nombreEmpresa', $nombreEmpresa);
        $template->setValue('numDia', $numDia);       
        $template->setValue('mes', $mes);             
        $template->setValue('numAño', $numAño);       
        $template->setValue('nombredelEncargadoUN', $nombredelEncargadoUN);
        $template->setValue('Nombredelestudiante', $Nombredelestudiante);
        $template->setValue('numMatricula', $numMatricula);
        $template->setValue('horaInicio', $horaInicio);
    
        $template->setValue('días', $días);
        $template->setValue('horaFinal', $horaFinal);
        $template->setValue('NombreJefadivision', $NombreJefadivision);
         $inicio = explode('-', $_POST['fechaInicio']);
    $template->setValue('numDiaInicio', $inicio[2]);
    $template->setValue('numMesInicio', $inicio[1]);
    $template->setValue('numAñoInicio', $inicio[0]);

    $inicio = explode('-', $_POST['fechaFinal']);
    $template->setValue('numDiaFin', $inicio[2]);
    $template->setValue('numMesFin', $inicio[1]);
    $template->setValue('numAñoFin', $inicio[0]);

    $template->setValue('area', $area);

    $template->setValue('NombreProyec', $NombreProyec);



        // Guardar y enviar documento
        $archivo = 'Carta Termino.docx';
        $template->saveAs($archivo);

        header("Content-Disposition: attachment; filename=$archivo");
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        readfile($archivo);
        unlink($archivo);
        exit;
    } catch (Exception $e) {
        echo "Ocurrió un error al generar el documento: " . $e->getMessage();
        exit;
    }
}
?>
