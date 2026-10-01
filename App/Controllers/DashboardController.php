<?php

require_once __DIR__ . '/../Core/Controller.php';

class DashboardController extends Controller 
{
    public function index() : void 
    {
        $this->requireLogin();

        $this->view('dashboard/index',['nama' => $_SESSION['nama']]);
    }
}