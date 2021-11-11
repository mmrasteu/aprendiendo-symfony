<?php
// Programación Orientada a Objetos en PHP (POO)

// Definir una clase (molde para crear mas objetos coche con caracteristicas parecidas)
class Coche{
    
    // Atributos o propiedades (variables)
    // Se pueden apilar los atributos en una sola linea
    //public $color, $marca, $modelo, $velocidad, $caballaje, $plazas;
    public $color = "Rojo";
    public $marca = "Ferrari";
    public $modelo = "Aventador";
    public $velocidad = 300;
    public $caballaje = 500;
    public $plazas = 2;
    
    // Metodos son acciones (funciones)
    public function getColor(){
        // $this significa "busca en esta clase la propiedad x"
        return $this->color; 
    }
    
    public function setColor($color){
        $this->color = $color;
    }
    
    public function setModelo($modelo){
        $this->modelo = $modelo;
    }
    
    public function acelerar(){
        $this->velocidad++;
    }
    
    public function frenar(){
        $this->velocidad--;
    }
    
    public function getVelocidad(){
        return $this->velocidad;
    }
} // Fin definicion de la clase

// Crear un objeto o instanciar la clase

$coche = new Coche();

// Usar los metodos 

$coche->setColor('Amarillo');

echo 'El color del coche es: ' . $coche->getColor() . '<br>';

$coche->acelerar();
$coche->acelerar();
$coche->acelerar();
$coche->acelerar();

$coche->frenar();

echo 'Velocidad del coche: ' . $coche->getVelocidad() . '<br>';

$coche2 = new Coche();
$coche2->setColor("Verde");
$coche2->setModelo("Gallardo");

var_dump($coche);
var_dump($coche2);




?>

