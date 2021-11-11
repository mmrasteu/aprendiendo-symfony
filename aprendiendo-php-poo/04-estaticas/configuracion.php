<?php

class Configuracion{
    
    //Las propiedades y metodos estaticos tienen la particularidad de no necesitar
    //instanciar la clase no se necesita crear un objeto y es mucho mas practico
    //en algunas ocasiones.
    
    public static $color;
    public static $newsletter;
    public static $entorno;
    
    //En lugar de usar $this para acceder a las propiedades se usa el operador self::
    
    public static function getColor() {
        return self::$color;
    }

    public static function getNewsletter() {
        return self::$newsletter;
    }

    public static function getEntorno() {
        return self::$entorno;
    }

    public static function setColor($color){
        self::$color = $color;
    }

    public static function setNewsletter($newsletter){
        self::$newsletter = $newsletter;
    }

    public static function setEntorno($entorno){
        self::$entorno = $entorno;
    }


}