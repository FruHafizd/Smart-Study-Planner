<?php

require_once __DIR__ . '/../core/Model.php';

class KalenderEvent extends Model
{
    public function create(int $userId, ?int $mataKuliahId, string $judul, ?string $deskripsi, string $jenis, string $waktuMulai, ?string $waktuSelesai): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO kalender_event (user_id, mata_kuliah_id, judul, deskripsi, jenis, waktu_mulai, waktu_selesai)
            VALUES (:user_id, :mata_kuliah_id, :judul, :deskripsi, :jenis, :waktu_mulai, :waktu_selesai)"
        );
        $stmt->execute([
            'user_id' => $userId,
            'mata_kuliah_id' => $mataKuliahId,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'jenis' => $jenis,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // Ambil event custom dalam rentang tanggal tertentu (1 bulan)
    public function getRange(int $userId, string $awal, string $akhir): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM kalender_event
                WHERE user_id = :user_id
                AND DATE(waktu_mulai) BETWEEN :awal AND :akhir"
        );
        $stmt->execute(['user_id' => $userId, 'awal' => $awal, 'akhir' => $akhir]);
        return $stmt->fetchAll();
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM kalender_event WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}