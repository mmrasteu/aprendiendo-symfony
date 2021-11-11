<?php
namespace PanelAdministrador;

class Usuario{
    public $nombre;
    public $email;
    
    public function __construct() {
        $this->nombre = "Antonio Pineda";
        $this->email = "antonio@antonio.com";
    }
    
    public function informacion(){
        
        //constantes del sistema para clases
        echo __CLASS__;
        echo '<br/>';
        echo __METHOD__;
        echo '<br/>';
        echo __FILE__;
        echo '<br/>';
        echo __NAMESPACE__;
        
        
    }
}

