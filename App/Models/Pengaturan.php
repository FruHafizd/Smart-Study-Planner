<?php

require_once __DIR__ . '/../Core/Model.php';

class Pengaturan extends Model 
{
    public function createDefault(int $userId) : void 
    {
        $stmt = $this->db->prepare(
            "INSERT INTO user_pengaturan (user_id) VALUES (:user_id)"
        );

        $stmt->execute(['user_id' => $userId]);
    }

    public function getByUser(int $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM user_pengaturan WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }
}