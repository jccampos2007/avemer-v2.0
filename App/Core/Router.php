<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function add(string $method, string $path, string $controller): void
    {
        $this->routes[$method][$path] = $controller;
    }

    public function dispatch(string $uri, string $method): void
    {
        $uri = strtok($uri, '?'); // Eliminar parámetros de consulta
        $uri = rtrim($uri, '/'); // Eliminar barra final

        // Si la URI es la raíz de la aplicación (ej. /php_mvc_app), redirigir a /dashboard
        // Esto asume que el DocumentRoot es 'public' y la base_url es 'http://localhost/'
        if (empty($uri) || $uri === '/' || $uri === '/php_mvc_app/public') {
            header('Location: ' . BASE_URL . 'dashboard'); // Esto es lo correcto
            exit();
        }

        // Manejar rutas con parámetros (ej. /users/edit/1)
        foreach ($this->routes[$method] as $path_pattern => $controller_info) {
            // Convertimos los patrones {param} a expresiones regulares: ({algo})
            $regex = preg_replace('#\{[^/]+\}#', '([^/]+)', $path_pattern);

            // Escapamos los slashes y armamos la expresión regular final
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // El primer match es la ruta completa

                list($controllerNamespace, $methodName) = explode('@', $controller_info);
                $controllerClass = $controllerNamespace;

                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    if (method_exists($controllerInstance, $methodName)) {
                        call_user_func_array([$controllerInstance, $methodName], $matches);
                        return;
                    }
                }
            }
        }

        // Despachar la ruta si no tiene parámetros o no coincide con el patrón
        if (array_key_exists($uri, $this->routes[$method])) {
            list($controllerNamespace, $methodName) = explode('@', $this->routes[$method][$uri]);
            $controllerClass = $controllerNamespace;

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                if (method_exists($controllerInstance, $methodName)) {
                    $controllerInstance->$methodName();
                    return;
                }
            }
        }

        // Si no se encuentra la ruta, mostrar 404
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>La página que buscas no existe.</p>";
    }
}
