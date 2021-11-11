<?php

class Usuario{
    
    const URL_COMPLETA = "http://localhost";
    public $email;
    public $password;
    
    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setEmail($email){
        $this->email = $email;
    }

    public function setPassword($password){
        $this->password = $password;
    }
}

//Las cosntantes son estaticas
$usuario = new Usuario();
echo $usuario::URL_COMPLETA;

echo Usuario::URL_COMPLETA;

var_dump($usuario);