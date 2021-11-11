<?php
require_once 'autoload.php';

/*
$usuario = new Usuario();
echo $usuario->nombre;
echo '<br/>';
echo $usuario->email;

$categoria = new Categoria();
echo <br/>.$categoria->nombre;

*/

//ESPACIOS DE NOMBRES Y PAQUETES

use MisClases\Usuario, MisClases\Categoria, MisClases\Entrada;
use PanelAdministrador\Usuario as UsuarioAdmin; 
//Dos objetos o clases no pueden tener el mismo nombre en el codigo
//con 'as' le asignamos un alias para que el programa no se confunda

class Principal{
    public $usuario;
    public $categoria;
    public $entrada;
    
    public function __construct() {
        $this->usuario = new Usuario(); 
        $this->categoria = new Categoria(); 
        $this->entrada = new Entrada();
       
    }
    
    public function getUsuario() {
        return $this->usuario;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    public function getEntrada() {
        return $this->entrada;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setCategoria($categoria) {
        $this->categoria = $categoria;
    }

    public function setEntrada($entrada) {
        $this->entrada = $entrada;
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

//Objeto principal
$principal = new Principal();
//$principal->informacion();

//De esta forma podemos buscar si existe un metodo en una clase
$metodos = get_class_methods($principal);
$busqueda = array_search('setEntrada', $metodos);
var_dump($busqueda);


//Objeto otro paquete
$usuario = new UsuarioAdmin();
$usuario->informacion();


//Comprobar si existe una clase
//Esta funcion no funciona con alias se debe indicar el namespace de la clase a comprobar
//Si ponemos un @delante de la funcion esconde los errores que se puedan generar
$clases = class_exists('MisClases\Usuario');

if($clases){
    echo "<h1>La clase si existe</h1>";
}
else{
    echo "<h1>La clase no existe</h1>";
}



