<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Pengaturan.php';

class AuthController extends Controller
{
    private User $userModel;
    private Pengaturan $pengaturanModel;

    public function __construct()
    {
        parent::__construct();

        $this->userModel = new User();
        $this->pengaturanModel = new Pengaturan();
    }

    public function showRegister() : void 
    {
        $this->view('auth/register');
    }

    public function register() : void 
    {
        $nama = trim($_POST['nama'] ?? '');
        $npm = trim($_POST['npm'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nama === '' || $email === '' || $password === '') {
            $this->view('auth/register', ['error' => 'Semua field wajib diisi']);
            return;
        }

        if ($this->userModel->emailExists($email)) {
            $this->view('auth/register', ['error' => 'Email sudah terdaftar.']);
            return;
        }

        if ($npm !== '' && $this->userModel->npmExists($npm)) {
            $this->view('auth/register', ['error' => 'NPM sudah terdaftar.']);
            return;
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            $userId = $this->userModel->create($nama, $npm, $email, $password);
            $this->pengaturanModel->createDefault($userId);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $this->view('auth/register', ['error' => 'Gagal mendaftar: '. $e->getMessage()]);
            return;
        }

        $this->redirect('/login');
    }

    public function showLogin() : void 
    {
        $this->view('auth/login');
    }

    public function login() : void 
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '' ;

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->view('auth/login', ['error' => 'Email atau passwrod salah.']);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];

        $this->redirect('/dashboard');
    }

    public function logout() : void 
    {
        session_destroy();
        $this->redirect('/login');
    }

}