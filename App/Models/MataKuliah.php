<?php

require_once __DIR__ . '/../Core/Model.php';

class MataKuliah extends Model 
{
    public function getAllByUser(int $userId) : array 
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM mata_kuliah WHERE user_id = :user_id ORDER BY nama_mk ASC"
        );

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId) : array|false 
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM mata_kuliah WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function create(int $userId, string $namaMk, int $sks, int $bobotPrioritas) : int 
    {
        $stmt = $this->db->prepare(
            "INSERT INTO mata_kuliah (user_id, nama_mk, sks, bobot_prioritas)
            VALUES (:user_id, :nama_mk, :sks, :bobot_prioritas)"
        );
        $stmt->execute([
            'user_id' => $userId,
            'nama_mk' => $namaMk,
            'sks' => $sks,
            'bobot_prioritas' => $bobotPrioritas,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $namaMk, int $sks, int $bobotPrioritas) : void 
    {
        $stmt = $this->db->prepare(
            "UPDATE mata_kuliah
            SET nama_mk = :nama_mk, sks = :sks, bobot_prioritas = :bobot_prioritas
            WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute([
            'nama_mk' => $namaMk,
            'sks' => $sks,
            'bobot_prioritas' => $bobotPrioritas,
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function delete(int $id, int $userId) : void 
    {
        $stmt = $this->db->prepare(
            "DELETE FROM mata_kuliah WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
    


}