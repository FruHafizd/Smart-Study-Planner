<?php

require_once __DIR__ . '/../core/Model.php';

class LogAktivitas extends Model
{
    // Dipanggil dari Model lain setiap ada aksi penting di grup
    public function catat(int $grupId, ?int $userId, string $aksi, ?string $keterangan = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO log_aktivitas_grup (grup_id, user_id, aksi, keterangan)
            VALUES (:grup_id, :user_id, :aksi, :keterangan)"
        );
        $stmt->execute([
            'grup_id' => $grupId,
            'user_id' => $userId,
            'aksi' => $aksi,
            'keterangan' => $keterangan,
        ]);
    }

    // Ambil riwayat aktivitas 1 grup, lengkap nama pelakunya
    public function getByGrup(int $grupId): array
    {
        $stmt = $this->db->prepare(
            "SELECT log_aktivitas_grup.*, users.nama
            FROM log_aktivitas_grup
            LEFT JOIN users ON users.id = log_aktivitas_grup.user_id
            WHERE grup_id = :grup_id
            ORDER BY created_at DESC
            LIMIT 50"
        );
        $stmt->execute(['grup_id' => $grupId]);
        return $stmt->fetchAll();
    }



}