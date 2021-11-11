<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Coche{
    
    // Atributos o propiedades (variables)
    // Se pueden apilar los atributos en una sola linea
    // public $color, $marca, $modelo, $velocidad, $caballaje, $plazas;
    
    
    //PUBLIC: Podemos acceder desde cualquier lugar, dentro de la clase que los 
    //       define, dentro de clases que hereden esta clase o fuera de la clase          
    public $color;
    
    //PROTECTED: Podemos acceder desde la clase que los define y desde las 
    //           clases que hereden esta clase          
    protected $marca;
    
    //PRIVATE: Unicamente se puede acceder desde la clase que los define
    private $modelo;
    
    public $velocidad;
    public $caballaje;
    public $plazas;
    
    public function __construct($color, $marca, $modelo, $velocidad, $caballaje, $plazas) {
       /* 
        $this->color = "Rojo";
        $this->marca = "Ferrari";
        $this->modelo = "Aventador";
        $this->velocidad = 300;
        $this->caballaje = 500;
        $this->plazas = 2;
        */
        
        $this->color = $color;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->velocidad = $velocidad;
        $this->caballaje = $caballaje;
        $this->plazas = $plazas;
        
    }

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
    
    public function getModelo(){
        return $this->modelo;
    }
    
     public function setMarca($marca){
        $this->marca = $marca;
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
    
    public function mostrarInformacion(Coche $miObjeto){
        
        if(is_object($miObjeto)){
            $informacion = "<h1>Informacion del coche</h1>";
            $informacion .= "Color: ".$miObjeto->color;
            $informacion .= "<br/>Modelo: ".$miObjeto->modelo;
            $informacion .= "<br/>Velocidad: ".$miObjeto->velocidad;
        }else{
            $informacion = "Tu dato es este: $miObjeto";
        }
        return $informacion;
    }
}

?>

