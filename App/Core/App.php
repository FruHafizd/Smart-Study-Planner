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

    protected function route(string $url): array
    {
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

        $routes = [
            // url => [GET method, POST method]
            'register'  => ['showRegister', 'register'],
            'login'     => ['showLogin', 'login'],
            'logout'    => ['logout', 'logout'],
            'dashboard' => ['index', 'index'],

            'matakuliah'        => ['index', 'store'],
            'matakuliah/hapus'  => ['index', 'destroy'],

            'jadwal'        => ['index', 'store'],
            'jadwal/hapus'  => ['index', 'destroy'],

            'tugas'               => ['index', 'store'],
            'tugas/status'        => ['index', 'updateStatus'],
            'tugas/hapus'         => ['index', 'destroy'],
            'tugas/urutan'  => ['halamanUrutan', 'susunUrutan'],

            'pomodoro'          => ['index', 'index'],
            'pomodoro/mulai'    => ['index', 'mulai'],
            'pomodoro/selesai'  => ['index', 'selesai'],
            'pomodoro/stop'     => ['index', 'stop'],

            'alokasi'        => ['index', 'index'],
            'alokasi/hitung' => ['index', 'hitung'],

            'grup'               => ['index', 'store'],
            'grup/gabung'        => ['index', 'gabung'],
            'grup/detail'        => ['detail', 'detail'],
            'grup/tugas'         => ['detail', 'storeTugas'],
            'grup/tugas/status'  => ['detail', 'updateStatusTugas'],
            'grup/komentar'      => ['detail', 'storeKomentar'],

        ];

        $controllerMap = [
            'register'  => 'Auth',
            'login'     => 'Auth',
            'logout'    => 'Auth',
            'dashboard' => 'Dashboard',

            'matakuliah'       => 'MataKuliah',
            'matakuliah/hapus' => 'MataKuliah',

            'jadwal'       => 'Jadwal',
            'jadwal/hapus' => 'Jadwal',

            'tugas'        => 'Tugas',
            'tugas/status' => 'Tugas',
            'tugas/hapus'  => 'Tugas',
            'tugas/urutan'  => 'Tugas',

            'pomodoro'         => 'Pomodoro',
            'pomodoro/mulai'   => 'Pomodoro',
            'pomodoro/selesai' => 'Pomodoro',
            'pomodoro/stop'    => 'Pomodoro',

            'alokasi'        => 'Alokasi',
            'alokasi/hitung' => 'Alokasi',

            'grup'              => 'Grup',
            'grup/gabung'       => 'Grup',
            'grup/detail'       => 'Grup',
            'grup/tugas'        => 'Grup',
            'grup/tugas/status' => 'Grup',
            'grup/komentar'     => 'Grup',

        ];

        if (!isset($routes[$url])) {
            return ['controller' => 'Auth', 'method' => 'showLogin'];
        }

        $method = $isPost ? $routes[$url][1] : $routes[$url][0];

        return [
            'controller' => $controllerMap[$url],
            'method' => $method,
        ];
    }
}