<?php
class UsuarioController{
    //El contorlador encapsulas acciones
    public function mostrarTodos(){
        // Hay que tener en cuenta que este require se lee desde la posicion
        // del index.php por lo que la direccion a poner es como si la pusieramos
        // desde el index
        require_once 'models/usuario.php';
        
        $usuario = new Usuario();
        
        $todos_los_usuarios = $usuario->conseguirTodos();
        
        require_once 'views/usuarios/mostrar-todos.php';
    }
    
    public function crear(){
        require_once 'views/usuarios/crear.php';
    }
}