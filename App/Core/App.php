<?php

class App
{
    protected string $controllerName = 'Home';
    protected string $method = 'index';
    protected array $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        $route = $this->route($url);

        $controllerFile = __DIR__ . '/../controllers/' . $route['controller'] . 'Controller.php';

        if (!file_exists($controllerFile)) {
            die("Controller Tidak ditemukan: " . $route['controller']);
        }

        require_once $controllerFile;

        $controllerClass = $route['controller'] . 'Controller';
        $controllerObject = new $controllerClass();

        $method = $route['method'];

        if (!method_exists($controllerObject, $method)) {
            die("Method tidak ditemukan: " . $method);
        }

        call_user_func_array([$controllerObject, $method], []);
    }

    protected function parseUrl() : string 
    {
        $url = $_GET['url'] ?? '';
        return trim($url, '/');
    }

    protected function route(string $url) : array 
    {
        $map = [
            'register' => ['controller' => 'Auth', 'method' => 'register'],
            'login' => ['controller' => 'Auth', 'method' => 'login'],
            'dashboard' => ['controller' => 'Dashboard', 'method' => 'index'],
            'logout' => ['controller' => 'Auth', 'method' => 'logout'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($url === 'register') {
                return ['controller' => 'Auth', 'method' => 'showRegister'];
            }
            if ($url === 'login') {
                return ['controller' => 'Auth', 'method' => 'Showlogin'];
            }
            if ($url === 'dashboard') {
                return ['controller' => 'Dashboard', 'method' => 'index'];
            }
            if ($url === 'logout') {
                return ['controller' => 'Auth', 'method' => 'logout'];
            }
        }

        return $map[$url] ?? ['controller' => 'Auth', 'method' => 'showLogin'];
    }
}