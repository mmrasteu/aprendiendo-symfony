<?php
require '../vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;

$html2pdf = new Html2Pdf();

//Recoger la vista a imprimir
//Todo lo que haya entre ob_start() y ob_get_clean() lo podemos guardar en una variable
ob_start();
require_once 'pdf_para_generar.php';
$html = ob_get_clean();

$html2pdf->writeHTML($html);
$html2pdf->output('pdf_generado.pdf');



