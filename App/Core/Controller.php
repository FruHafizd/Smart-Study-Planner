<?php

abstract class Controller 
{
    protected function view(string $viewPath, array $data = []) : void 
    {
        extract($data);
        $file = __DIR__ . '/../views/' . $viewPath . '.php';
        if (file_exists($file)) {
            require $file;
        }else {
            die("View Tidak ditemukan: " . $viewPath);
        }
    }

    protected function json(array $data) : void 
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function redirect(string $path) : void 
    {
        header('Location: /' . ltrim($path, '/'));
        exit;
    }
}