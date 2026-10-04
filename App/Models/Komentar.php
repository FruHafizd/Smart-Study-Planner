<?php

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class Komentar extends Model
{
    private LogAktivitas $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->logModel = new LogAktivitas();
    }

    public function create(int $tugasGrupId, int $grupId, int $userId, ?int $parentId, string $isi): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO komentar_tugas_grup (tugas_grup_id, user_id, parent_id, isi)
                VALUES (:tugas_grup_id, :user_id, :parent_id, :isi)"
        );
        $stmt->execute([
            'tugas_grup_id' => $tugasGrupId,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'isi' => $isi,
        ]);

        $id = (int) $this->db->lastInsertId();

        $this->logModel->catat($grupId, $userId, 'KOMENTAR', 'Menambahkan komentar');

        return $id;
    }

    public function getByTugasGrup(int $tugasGrupId): array
    {
        $stmt = $this->db->prepare(
            "SELECT komentar_tugas_grup.*, users.nama
                FROM komentar_tugas_grup
                JOIN users ON users.id = komentar_tugas_grup.user_id
                WHERE tugas_grup_id = :tugas_grup_id
                ORDER BY created_at ASC"
        );
        $stmt->execute(['tugas_grup_id' => $tugasGrupId]);
        return $stmt->fetchAll();
    }
}