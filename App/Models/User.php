<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model 
{
    public function emailExists(string $email) : bool 
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() !== false;
    }

    public function npmExists(string $npm) : bool 
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE npm = :npm");
        $stmt->execute(['npm' => $npm]);
        return $stmt->fetch() !== false;
    }

    public function create(string $nama, string $npm, string $email, string $passwordPlain) : int 
    {
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (nama, npm, email, password_hash)
            VALUES (:nama, :npm, :email, :password_hash)"
        );
        $stmt->execute([
            'nama' => $nama,
            'npm' => $npm,
            'email' => $email,
            'password_hash' => $hash,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email) : array|false 
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }


}