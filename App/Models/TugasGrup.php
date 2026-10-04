<?php

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class TugasGrup extends Model
{
    private LogAktivitas $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->logModel = new LogAktivitas();
    }

    // Buat tugas grup baru, sekaligus assign ke anggota yang dipilih
    public function create(
        int $grupId,
        int $dibuatOleh,
        string $judul,
        ?string $deskripsi,
        string $deadline,
        int $prioritas,
        array $anggotaIds
    ): int 
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tugas_grup (grup_id, judul, deskripsi, deadline, prioritas, dibuat_oleh)
                VALUES (:grup_id, :judul, :deskripsi, :deadline, :prioritas, :dibuat_oleh)"
            );

            $stmt->execute([
                'grup_id' => $grupId,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'deadline' => $deadline,
                'prioritas' => $prioritas,
                'dibuat_oleh' => $dibuatOleh,
            ]);

            $tugasGrupId = (int) $this->db->lastInsertId();

            // Assign ke tiap anggota yang dipilih
            $stmtAssign = $this->db->prepare(
                "INSERT INTO tugas_grup_anggota (tugas_grup_id, user_id) VALUES (:tugas_grup_id, :user_id)"
            );

            foreach ($anggotaIds as $userId) {
                $stmtAssign->execute([
                    'tugas_grup_id' => $tugasGrupId,
                    'user_id' => (int) $userId,
                ]);
            }

            $this->db->commit();

            $this->logModel->catat($grupId, $dibuatOleh, 'TUGAS_BARU', "Membuat tugas: $judul");

            return $tugasGrupId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Ambil semua tugas grup + progres (berapa anggota sudah SELESAI dari berapa total)
    public function getAllByGrup(int $grupId): array
    {
        $stmt = $this->db->prepare(
            "SELECT tugas_grup.*,
                    COUNT(tugas_grup_anggota.id) AS total_anggota,
                    SUM(CASE WHEN tugas_grup_anggota.status = 'SELESAI' THEN 1 ELSE 0 END) AS total_selesai
            FROM tugas_grup
            LEFT JOIN tugas_grup_anggota ON tugas_grup_anggota.tugas_grup_id = tugas_grup.id
            WHERE tugas_grup.grup_id = :grup_id
            GROUP BY tugas_grup.id
            ORDER BY tugas_grup.deadline ASC"
        );

        $stmt->execute(['grup_id' => $grupId]);
        return $stmt->fetchAll();
    }


    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM tugas_grup WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Ambil daftar anggota yang ditugaskan + statusnya masing-masing
    public function getAnggotaTugas(int $tugasGrupId): array
    {
        $stmt = $this->db->prepare(
            "SELECT tugas_grup_anggota.*, users.nama
            FROM tugas_grup_anggota
            JOIN users ON users.id = tugas_grup_anggota.user_id
            WHERE tugas_grup_id = :tugas_grup_id
            ORDER BY users.nama ASC"
        );
        $stmt->execute(['tugas_grup_id' => $tugasGrupId]);
        return $stmt->fetchAll();
    }

    // Cek: user ini ditugaskan di tugas_grup ini atau tidak
    public function getAssignmentUser(int $tugasGrupId, int $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tugas_grup_anggota WHERE tugas_grup_id = :tugas_grup_id AND user_id = :user_id"
        );
        $stmt->execute(['tugas_grup_id' => $tugasGrupId, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    // Ubah status pengerjaan (dicek dulu di Controller: anggota itu sendiri atau ketua?)
    public function updateStatus(int $tugasGrupId, int $userId, string $statusBaru): void
    {
        $selesaiPada = $statusBaru === 'SELESAI' ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare(
            "UPDATE tugas_grup_anggota 
            SET status = :status, selesai_pada = :selesai_pada
            WHERE tugas_grup_id = :tugas_grup_id AND user_id = :user_id"
        );
        $stmt->execute([
            'status' => $statusBaru,
            'selesai_pada' => $selesaiPada,
            'tugas_grup_id' => $tugasGrupId,
            'user_id' => $userId,
        ]);
    }

    // Progres tugas grup untuk 1 user (dipakai nanti di Dashboard)
    public function progresSaya(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT tugas_grup.judul, grup.nama_grup, tugas_grup_anggota.status, tugas_grup.deadline
            FROM tugas_grup_anggota
            JOIN tugas_grup ON tugas_grup.id = tugas_grup_anggota.tugas_grup_id
            JOIN grup ON grup.id = tugas_grup.grup_id
            WHERE tugas_grup_anggota.user_id = :user_id
                AND tugas_grup_anggota.status != 'SELESAI'
            ORDER BY tugas_grup.deadline ASC
            LIMIT 5"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

}