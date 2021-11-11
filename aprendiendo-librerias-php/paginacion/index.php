<?php
require '../vendor/autoload.php';

//Conexion a BD
$conexion = new mysqli("localhost", "root", "", "notas_master");
$conexion->query("SET NAMES 'utf8'");

//Consulta a paginar
$consulta = $conexion->query("SELECT * FROM notas");
$numero_elementos = $consulta->num_rows;
$numero_elementos_paginas = 2;


//Hacer paginacion
$pagination = new Zebra_Pagination();

//Numero total de elementos a paginar
$pagination->records($numero_elementos);

//Numero de elementos por paginas
$pagination->records_per_page($numero_elementos_paginas);

$page = $pagination->get_page();

$empieza_aqui = (($page - 1)*$numero_elementos_paginas);
$sql = "SELECT * FROM notas LIMIT $empieza_aqui,$numero_elementos_paginas";
$notas = $conexion->query("$sql");
    
echo '<link rel="stylesheet" href="../vendor/stefangabos/zebra_pagination/public/css/zebra_pagination.css" type="text/css">';
while($nota = $notas->fetch_object()){
    echo "<h1>{$nota->titulo}</h1>";
    echo "<h3>{$nota->descripcion}</h3> </hr>";
}


$pagination->render();