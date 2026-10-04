<?php

require_once __DIR__ . '/../core/Model.php';

class Notifikasi extends Model
{
    // Fungsi utama: generate semua notifikasi H-1 untuk 1 user
    public function generateH1(int $userId): void
    {
        $besok = date('Y-m-d', strtotime('+1 day'));
        $namaHariBesok = $this->namaHariIndonesia($besok);

        $this->cekJadwalBesok($userId, $besok, $namaHariBesok);
        $this->cekTugasBesok($userId, $besok);
        $this->cekTugasGrupBesok($userId, $besok);
    }

    // Helper: ubah tanggal jadi nama hari dalam Bahasa Indonesia
    private function namaHariIndonesia(string $tanggal): string
    {
        $hariInggris = date('l', strtotime($tanggal));
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        return $map[$hariInggris];
    }

    // Cari jadwal kuliah besok, buat notifikasi
    private function cekJadwalBesok(int $userId, string $besok, string $namaHari): void
    {
        $stmt = $this->db->prepare(
            "SELECT jadwal_kuliah.id, mata_kuliah.nama_mk, jadwal_kuliah.jam_mulai
            FROM jadwal_kuliah
            JOIN mata_kuliah ON mata_kuliah.id = jadwal_kuliah.mata_kuliah_id
            WHERE mata_kuliah.user_id = :user_id AND jadwal_kuliah.hari = :hari"
        );
        $stmt->execute(['user_id' => $userId, 'hari' => $namaHari]);

        foreach ($stmt->fetchAll() as $jadwal) {
            $jamMulai = substr($jadwal['jam_mulai'], 0, 5);
            $this->insertNotifikasi(
                $userId,
                'KELAS',
                $jadwal['id'],
                $besok,
                'Kelas besok: ' . $jadwal['nama_mk'],
                $jadwal['nama_mk'] . ' dimulai pukul ' . $jamMulai . ' besok (' . $namaHari . ').'
            );
        }
    }

    // Cari tugas pribadi deadline besok, belum selesai
    private function cekTugasBesok(int $userId, string $besok): void
    {
        $stmt = $this->db->prepare(
            "SELECT id, judul FROM tugas
            WHERE user_id = :user_id AND deadline = :besok AND status != 'SELESAI'"
        );
        $stmt->execute(['user_id' => $userId, 'besok' => $besok]);

        foreach ($stmt->fetchAll() as $tugas) {
            $this->insertNotifikasi(
                $userId,
                'DEADLINE_TUGAS',
                $tugas['id'],
                $besok,
                'Deadline besok: ' . $tugas['judul'],
                'Tugas "' . $tugas['judul'] . '" harus selesai besok.'
            );
        }
    }

    // Cari tugas grup deadline besok, untuk user ini, belum selesai
    private function cekTugasGrupBesok(int $userId, string $besok): void
    {
        $stmt = $this->db->prepare(
            "SELECT tugas_grup.id, tugas_grup.judul
            FROM tugas_grup_anggota
            JOIN tugas_grup ON tugas_grup.id = tugas_grup_anggota.tugas_grup_id
            WHERE tugas_grup_anggota.user_id = :user_id
                AND DATE(tugas_grup.deadline) = :besok
                AND tugas_grup_anggota.status != 'SELESAI'"
        );
        $stmt->execute(['user_id' => $userId, 'besok' => $besok]);

        foreach ($stmt->fetchAll() as $tugas) {
            $this->insertNotifikasi(
                $userId,
                'DEADLINE_GRUP',
                $tugas['id'],
                $besok,
                'Deadline tugas grup besok: ' . $tugas['judul'],
                'Tugas grup "' . $tugas['judul'] . '" harus selesai besok.'
            );
        }
    }

    // INSERT IGNORE -- cegah duplikat karena UNIQUE KEY di tabel
    private function insertNotifikasi(
        int $userId, string $jenis, int $referensiId, string $tanggalAcuan, string $judul, string $pesan
    ): void {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO notifikasi (user_id, jenis, referensi_id, tanggal_acuan, judul, pesan)
            VALUES (:user_id, :jenis, :referensi_id, :tanggal_acuan, :judul, :pesan)"
        );
        $stmt->execute([
            'user_id' => $userId,
            'jenis' => $jenis,
            'referensi_id' => $referensiId,
            'tanggal_acuan' => $tanggalAcuan,
            'judul' => $judul,
            'pesan' => $pesan,
        ]);
    }

    // Ambil notifikasi user, terbaru dulu
    public function getByUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifikasi WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Hitung yang belum dibaca (untuk badge ikon lonceng)
    public function hitungBelumDibaca(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE user_id = :user_id AND sudah_dibaca = 0"
        );
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetch()['jumlah'];
    }

    public function tandaiSemuaDibaca(int $userId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE notifikasi SET sudah_dibaca = 1 WHERE user_id = :user_id AND sudah_dibaca = 0"
        );
        $stmt->execute(['user_id' => $userId]);
    }
}