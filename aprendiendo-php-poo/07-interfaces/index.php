<?php

// Una interface es un contrato en el cual definimos que metodos y en que orden 
// van a estar en una clase.

interface Ordenador{
    
    public function encender();
    public function apagar();
    public function reiniciar();
    public function desfragmentar();
    public function detectarUSB();
}

class iMac implements Ordenador{
    private $modelo;
    
    public function getModelo() {
        return $this->modelo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }
    
    //Si no definimos los metodos de la interfaz dará error.
    
    public function encender(){
        return "Lo que sea";
    
    }
    public function apagar(){
        return "Lo que sea";
    
    }
    public function reiniciar(){
        return "Lo que sea";
    
    }
    public function desfragmentar(){
        return "Lo que sea";
    
    }
    public function detectarUSB(){
        return "Lo que sea";
    
    }
    


}

$maquintosh = new iMac();
$maquintosh->setModelo("MacBook Pro 2019");

echo $maquintosh->getModelo();

var_dump($maquintosh);


