<?php
require_once 'configuracion.php';

//Como las propiedades y metodos de la clase configuracion son estaticos
//no necesitamos crear un objeto como de costumbre, podemos acceder directamente
//a sus metodos usando ::

Configuracion::setColor("blue");
Configuracion::setEntorno("localhost");
Configuracion::setNewsletter(true);

echo Configuracion::$color."<br/>";
echo Configuracion::$entorno."<br/>";
echo Configuracion::$newsletter."<br/>";

$configuracion = new Configuracion();
$configuracion::$color = "rojo";
echo $configuracion::$color;    
var_dump($configuracion);