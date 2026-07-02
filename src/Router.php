<?php

class Router
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->addRoute('GET', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->addRoute('POST', $path, $callback);
    }

    public function addRoute($method, $path, $callback)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function dispatch($method, $path)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Converter path com parâmetros em regex
            $pattern = preg_replace('/\{(\w+)\}/', '(\d+)', $route['path']);
            $pattern = str_replace('/', '\/', $pattern);
            $pattern = "/^{$pattern}$/";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove o match completo

                $callback = $route['callback'];

                if (is_array($callback)) {
                    $controllerName = $callback[0];
                    $methodName = $callback[1];

                    $controller = new $controllerName();
                    return call_user_func_array([$controller, $methodName], $matches);
                } else {
                    return call_user_func_array($callback, $matches);
                }
            }
        }

        http_response_code(404);
        echo 'Página não encontrada';
    }
}
