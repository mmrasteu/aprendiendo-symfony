<?php

// HERENCIA: La posibilidad de compartir atributos y métodos entre clases.
//

class Persona{
    
        public $nombre;
        public $apellidos;
        public $altura;
        public $edad;
        
        public function getNombre() {
            return $this->nombre;
        }

        public function getApellidos() {
            return $this->apellidos;
        }

        public function getAltura() {
            return $this->altura;
        }

        public function getEdad() {
            return $this->edad;
        }

        public function setNombre($nombre){
            $this->nombre = $nombre;
        }

        public function setApellidos($apellidos){
            $this->apellidos = $apellidos;
        }

        public function setAltura($altura){
            $this->altura = $altura;
        }

        public function setEdad($edad){
            $this->edad = $edad;
        }

        public function hablar(){
            return "Estoy hablando";
        }
        
        public function andar(){
            return "Estoy andando";
        }
        
}

class Informatico extends Persona{ 
    
    public $lenguajes;
    public $experienciaProgramador;
    
    public function __construct() {
        $this->lenguajes = "HTML, CSS y JS";
        $this->experienciaProgramador = 10;
    }
    
    public function sabeLenguajes($lenguajes){
        $this->lenguajes = $lenguajes; 
        
        return $this->lenguajes;
    }  
    
    public function programar(){
        return "Soy programador";
    }
    
    public function repararOrdenador(){
        return "Reparar ordenadores";
    }
    
    public function hacerOfimatica(){
        return "Estoy escribiendo en Word";
    }
    
}

class TecnicoRedes extends Informatico{
    
    public $auditarRedes;
    public $experienciaRedes;
    
    public function __construct() {
        //la palabra reservada parent llama al metodo construct de la clase padre
        parent::__construct();
        
        $this->auditarRedes = "Experto";
        $this->experienciaRedes = 5;
    }
    
    public function auditoria(){
        return "Estoy auditando una red";
    }
    
}

?>