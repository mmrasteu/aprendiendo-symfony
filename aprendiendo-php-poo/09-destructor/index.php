<?php

class Usuario{
    public $nombre;
    public $email;
    
    public function __construct() {
        $this->nombre = "Victor Robles";
        $this->email = "victor@victor.com";
        echo "Creando el objeto <br/>";
        //No se debe imprimir desde el constructor pero lo usaremos con fines explicativos
    }
    
    public function __toString() {
        return "Hola, {$this->nombre}, estas registrado con {$this->email}";
    }
    
    public function __destruct() {
        echo "<br/> Destruyendo el objeto";
    }
}

$usuario = new Usuario();
echo $usuario;
    
