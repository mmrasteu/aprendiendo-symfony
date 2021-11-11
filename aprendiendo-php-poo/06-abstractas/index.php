<?php

//Una clase abstracta es una clase que no se puede isntanciar, es decir, no
//podemos crear objetos con ella pero podemos utilizarla para heredar de ella.
//Esa clase define la estructura una clase que la herede y/o definir una
//funcionalidad.

abstract class Ordenador{   
    public $encendido;
    
    abstract public function encender();
        
    public function apagar(){
         $this->encendido = false;
    }
}

class PcAsus extends Ordenador{
    public $software;
    
    public function arrancarSoftware(){
        $this->software = true;
    }
    
    public function encender(){
        $this->encendido = true;
    }
}

$ordenador = new PcAsus();
$ordenador->arrancarSoftware();
$ordenador->encender();
$ordenador->apagar();

var_dump($ordenador);


