<?php

// Una excepcion es un fallo. Un error que se produce en nuestro cogido.
// Por ejemplo cuando no se cumpla una logica

// Try/catch sirve para capturar excepciones, en codigo suceptible a errores

try{
    if(isset($_GET['id'])){
        echo "<h1>El parametro es: {$_GET['id']} </h1>";
    }else{
        throw new Exception('Faltan parametros por la url');
    }
} catch(Exception $e){
    echo "Ha habido un error: ".$e->getMessage();
    
}








