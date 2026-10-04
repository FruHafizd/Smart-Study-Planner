<?php

require_once __DIR__ . '/../core/Model.php';

class AlokasiBelajar extends Model
{
    // Simpan/update alokasi — pakai ON DUPLICATE KEY UPDATE sesuai flowchart
    public function upsert(int $userId, int $mataKuliahId, string $mingguMulai, int $rencanaMenit): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO alokasi_belajar (user_id, mata_kuliah_id, minggu_mulai, rencana_menit, dasar_perhitungan)
                VALUES (:user_id, :mata_kuliah_id, :minggu_mulai, :rencana_menit, 'SKS_PRIORITAS')
                ON DUPLICATE KEY UPDATE rencana_menit = :rencana_menit2"
        );
        $stmt->execute([
            'user_id' => $userId,
            'mata_kuliah_id' => $mataKuliahId,
            'minggu_mulai' => $mingguMulai,
            'rencana_menit' => $rencanaMenit,
            'rencana_menit2' => $rencanaMenit,
        ]);
    }

    // Ambil alokasi minggu ini, lengkap dengan nama mata kuliah
    public function getMingguIni(int $userId, string $mingguMulai): array
    {
        $stmt = $this->db->prepare(
            "SELECT alokasi_belajar.*, mata_kuliah.nama_mk
                FROM alokasi_belajar
                JOIN mata_kuliah ON mata_kuliah.id = alokasi_belajar.mata_kuliah_id
                WHERE alokasi_belajar.user_id = :user_id 
                    AND alokasi_belajar.minggu_mulai = :minggu_mulai"
        );
        $stmt->execute(['user_id' => $userId, 'minggu_mulai' => $mingguMulai]);
        return $stmt->fetchAll();
    }
}