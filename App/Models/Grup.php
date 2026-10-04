<?php

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class Grup extends Model
{
    private LogAktivitas $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->logModel = new LogAktivitas();
    }

    // Generate kode undangan acak 8 karakter (huruf besar + angka)
    private function buatKodeUndangan(): string
    {
        $karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $kode = '';
        for ($i = 0; $i < 8; $i++) {
            $kode .= $karakter[random_int(0, strlen($karakter) - 1)];
        }
        return $kode;
    }

    // Buat grup baru, otomatis jadikan pembuatnya sebagai KETUA
    public function create(int $userId, string $namaGrup, ?string $deskripsi, ?string $namaMk): int
    {
        $this->db->beginTransaction();

        try {
            $kodeUndangan = $this->buatKodeUndangan();

            $stmt = $this->db->prepare(
                "INSERT INTO grup (nama_grup, deskripsi, nama_mk, kode_undangan, dibuat_oleh)
                VALUES (:nama_grup, :deskripsi, :nama_mk, :kode_undangan, :dibuat_oleh)"
            );
            $stmt->execute([
                'nama_grup' => $namaGrup,
                'deskripsi' => $deskripsi,
                'nama_mk' => $namaMk,
                'kode_undangan' => $kodeUndangan,
                'dibuat_oleh' => $userId,
            ]);

            $grupId = (int) $this->db->lastInsertId();

            // Pembuat grup otomatis jadi anggota dengan peran KETUA
            $stmtAnggota = $this->db->prepare(
                "INSERT INTO grup_anggota (grup_id, user_id, peran) VALUES (:grup_id, :user_id, 'KETUA')"
            );
            $stmtAnggota->execute(['grup_id' => $grupId, 'user_id' => $userId]);

            $this->db->commit();

            $this->logModel->catat($grupId, $userId, 'GABUNG', 'Membuat grup dan menjadi ketua');

            return $grupId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Cari grup berdasarkan kode undangan
    public function findByKode(string $kode): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM grup WHERE kode_undangan = :kode");
        $stmt->execute(['kode' => $kode]);
        return $stmt->fetch();
    }

    // Cek apakah user sudah jadi anggota grup tertentu
    public function sudahJadiAnggota(int $grupId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM grup_anggota WHERE grup_id = :grup_id AND user_id = :user_id"
        );
        $stmt->execute(['grup_id' => $grupId, 'user_id' => $userId]);
        return $stmt->fetch() !== false;
    }

    // Gabung ke grup lewat kode undangan
    public function gabung(int $grupId, int $userId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO grup_anggota (grup_id, user_id, peran) VALUES (:grup_id, :user_id, 'ANGGOTA')"
        );
        $stmt->execute(['grup_id' => $grupId, 'user_id' => $userId]);

        $this->logModel->catat($grupId, $userId, 'GABUNG', 'Bergabung ke grup');
    }

      // Ambil semua grup yang diikuti user
    public function getAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT grup.*, grup_anggota.peran
            FROM grup
            JOIN grup_anggota ON grup_anggota.grup_id = grup.id
            WHERE grup_anggota.user_id = :user_id
            ORDER BY grup.created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM grup WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Ambil semua anggota 1 grup
    public function getAnggota(int $grupId): array
    {
        $stmt = $this->db->prepare(
            "SELECT users.id, users.nama, grup_anggota.peran
            FROM grup_anggota
            JOIN users ON users.id = grup_anggota.user_id
            WHERE grup_anggota.grup_id = :grup_id
            ORDER BY grup_anggota.peran ASC, users.nama ASC"
        );
        $stmt->execute(['grup_id' => $grupId]);
        return $stmt->fetchAll();
    }

    public function peranUser(int $grupId, int $userId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT peran FROM grup_anggota WHERE grup_id = :grup_id AND user_id = :user_id"
        );
        $stmt->execute(['grup_id' => $grupId, 'user_id' => $userId]);
        $hasil = $stmt->fetch();
        return $hasil ? $hasil['peran'] : null;
    }

}