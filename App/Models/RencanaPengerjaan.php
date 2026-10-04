<?php

require_once __DIR__ . '/../core/Model.php';

class RencanaPengerjaan extends Model 
{
    public function simpan(int $userId, string $metode, string $waktuMulai, array $urutanTugas): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Insert header rencana
            $stmt = $this->db->prepare(
                "INSERT INTO rencana_pengerjaan (user_id, metode, waktu_mulai, total_tugas)
                VALUES (:user_id, :metode, :waktu_mulai, :total_tugas)"
            );
            $stmt->execute([
                'user_id' => $userId,
                'metode' => $metode,
                'waktu_mulai' => $waktuMulai,
                'total_tugas' => count($urutanTugas),
            ]);

            $rencanaId = (int) $this->db->lastInsertId();

            // 2. Insert tiap baris detail (per tugas, dengan urutan & jadwal mulai/selesai)
            $stmtDetail = $this->db->prepare(
                "INSERT INTO rencana_pengerjaan_detail (rencana_id, tugas_id, urutan, rencana_mulai, rencana_selesai)
                VALUES (:rencana_id, :tugas_id, :urutan, :rencana_mulai, :rencana_selesai)"
            );

            foreach ($urutanTugas as $item) {
                $stmtDetail->execute([
                    'rencana_id' => $rencanaId,
                    'tugas_id' => $item['tugas_id'],
                    'urutan' => $item['urutan'],
                    'rencana_mulai' => $item['rencana_mulai'],
                    'rencana_selesai' => $item['rencana_selesai'],
                ]);
            }

            $this->db->commit();

            return $rencanaId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Ambil rencana terbaru milik user, lengkap dengan detail + judul tugas
    public function getRencanaTerbaru(int $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM rencana_pengerjaan 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC LIMIT 1"
        );

        $stmt->execute(['user_id' => $userId]);
        $rencana = $stmt->fetch();

        if (!$rencana) {
            return false;
        }

        $stmtDetail = $this->db->prepare(
            "SELECT rencana_pengerjaan_detail.*, tugas.judul, tugas.prioritas, tugas.deadline
            FROM rencana_pengerjaan_detail
            JOIN tugas ON tugas.id = rencana_pengerjaan_detail.tugas_id
            WHERE rencana_id = :rencana_id
            ORDER BY urutan ASC"
        );

        $stmtDetail->execute(['rencana_id' => $rencana['id']]);
        $rencana['detail'] = $stmtDetail->fetchAll();

        return $rencana;
    }

    
}