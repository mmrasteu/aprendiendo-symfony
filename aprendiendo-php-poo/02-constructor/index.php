<?php
require_once 'coche.php';

$coche1 = new Coche("Amarillo", "Renault", "Clio", 150, 200, 5);
$coche2 = new Coche("Verde", "Seat", "Panda", 250, 400, 2);
$coche3 = new Coche("Rojo", "Citroen", "Zara", 100, 250, 4);
$coche4 = new Coche("Azul", "Mercedez", "Clase A", 350, 500, 5);

// Color es un atributo public y podemos acceder directamente
$coche1->color = "ROSA";

// Marca es un atributo protected, no podemos acceder directamente por lo que
// necesitamos un método en la clase que pueda modificarlo
// $coche->marca = "Audi"; *Esto daría error*
$coche1->setMarca("Audi");

// Modelo es un atributo private, no podemos acceder a el directamente por lo que
// necesitamos un método que muestre su valor
// var_dump($coche->modelo); *Esto daría error*
//var_dump($coche->getModelo());


//var_dump($coche);


echo $coche1->mostrarInformacion("HOLA MUNDO DESDE UN METODO");

