<?php

require_once __DIR__ . '/../core/Model.php';

class JadwalKuliah extends Model 
{
    public function getAllByUser(int $userId) : array 
    {
        $stmt = $this->db->prepare(
            "SELECT jadwal_kuliah.*, mata_kuliah.nama_mk
            FROM jadwal_kuliah
            JOIN mata_kuliah ON mata_kuliah.id = jadwal_kuliah.mata_kuliah_id
            WHERE mata_kuliah.user_id = :user_id
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai ASC"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function adaBentrok(int $userId, string $hari, string $jamMulai, string $jamSelesai, ?int $kecualiId = null): bool
    {

        $sql = "SELECT jadwal_kuliah.id
                FROM jadwal_kuliah
                JOIN mata_kuliah ON mata_kuliah.id = jadwal_kuliah.mata_kuliah_id
                WHERE mata_kuliah.user_id = :user_id
                    AND jadwal_kuliah.hari = :hari
                    AND jadwal_kuliah.jam_mulai < :jam_selesai
                    AND jadwal_kuliah.jam_selesai > :jam_mulai";

        $params = [
            'user_id' => $userId,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
        ];

        // Saat EDIT jadwal, jangan bandingkan dengan dirinya sendiri
        if ($kecualiId !== null) {
            $sql .= " AND jadwal_kuliah.id != :kecuali_id";
            $params['kecuali_id'] = $kecualiId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() !== false;
    }

    public function create(int $mataKuliahId, string $hari, string $jamMulai, string $jamSelesai): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO jadwal_kuliah (mata_kuliah_id, hari, jam_mulai, jam_selesai)
            VALUES (:mata_kuliah_id, :hari, :jam_mulai, :jam_selesai)"
        );

        $stmt->execute([
            'mata_kuliah_id' => $mataKuliahId,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->db->prepare(
            "DELETE jadwal_kuliah FROM jadwal_kuliah
            JOIN mata_kuliah ON mata_kuliah.id = jadwal_kuliah.mata_kuliah_id
            WHERE jadwal_kuliah.id = :id AND mata_kuliah.user_id = :user_id"
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }



}