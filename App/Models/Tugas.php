<?php

require_once __DIR__ . '/../core/Model.php';


class Tugas extends Model
{
    // Ambil semua tugas milik user, sekalian nama mata kuliahnya
    public function getAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT tugas.*, mata_kuliah.nama_mk
            FROM tugas
            JOIN mata_kuliah ON mata_kuliah.id = tugas.mata_kuliah_id
            WHERE tugas.user_id = :user_id
            ORDER BY tugas.deadline ASC"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tugas WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function save(
        int $userId,
        int $mataKuliahId,
        string $judul,
        string $deadline,
        int $prioritas,
        int $estimasiMenit
    ): int 
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tugas (user_id, mata_kuliah_id, judul, deadline, prioritas, estimasi_menit, status)
            VALUES (:user_id, :mata_kuliah_id, :judul, :deadline, :prioritas, :estimasi_menit, 'BELUM')"
        );

        $stmt->execute([
            'user_id' => $userId,
            'mata_kuliah_id' => $mataKuliahId,
            'judul' => $judul,
            'deadline' => $deadline,
            'prioritas' => $prioritas,
            'estimasi_menit' => $estimasiMenit,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // Ubah status tugas (sesuai flowchart: SELESAI -> selesai_pada = NOW, else NULL)
    public function updateStatus(int $id, int $userId, string $statusBaru): void
    {
        $selesaiPada = $statusBaru === 'SELESAI' ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare(
            "UPDATE tugas 
            SET status = :status, selesai_pada = :selesai_pada
            WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute([
            'status' => $statusBaru,
            'selesai_pada' => $selesaiPada,
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM tugas WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function getAktif(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tugas 
            WHERE user_id = :user_id AND status IN ('BELUM', 'PROSES')"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}