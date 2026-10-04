<?php

require_once __DIR__ . '/../core/Model.php';

class SesiBelajar extends Model
{   
    // Mulai sesi baru (sesuai flowchart: INSERT status BERJALAN)
    public function mulai(
        int $userId,
        ?int $mataKuliahId,
        ?int $tugasId,
        string $jenis,
        int $durasiRencanaMenit
    ): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO sesi_belajar 
                (user_id, mata_kuliah_id, tugas_id, jenis, durasi_rencana_menit, waktu_mulai, status)
            VALUES 
                (:user_id, :mata_kuliah_id, :tugas_id, :jenis, :durasi_rencana_menit, :waktu_mulai, 'BERJALAN')"
        );

        $stmt->execute([
            'user_id' => $userId,
            'mata_kuliah_id' => $mataKuliahId,
            'tugas_id' => $tugasId,
            'jenis' => $jenis,
            'durasi_rencana_menit' => $durasiRencanaMenit,
            'waktu_mulai' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }


    // Tandai sesi selesai normal (timer habis)
    public function selesai(int $sesiId, int $userId, int $durasiAktualDetik): void
    {
        $stmt = $this->db->prepare(
            "UPDATE sesi_belajar 
            SET status = 'SELESAI', durasi_aktual_detik = :durasi, waktu_selesai = :waktu_selesai
            WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute([
            'durasi' => $durasiAktualDetik,
            'waktu_selesai' => date('Y-m-d H:i:s'),
            'id' => $sesiId,
            'user_id' => $userId,
        ]);
    }

     // Tandai sesi dihentikan manual (user klik Stop sebelum waktu habis)
    public function hentikan(int $sesiId, int $userId, int $durasiAktualDetik): void
    {
        $stmt = $this->db->prepare(
            "UPDATE sesi_belajar 
            SET status = 'DIHENTIKAN', durasi_aktual_detik = :durasi, waktu_selesai = :waktu_selesai
            WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute([
            'durasi' => $durasiAktualDetik,
            'waktu_selesai' => date('Y-m-d H:i:s'),
            'id' => $sesiId,
            'user_id' => $userId,
        ]);
    }

     // Hitung berapa sesi FOKUS yang sudah SELESAI hari ini (untuk tentukan istirahat pendek/panjang)
    public function hitungSesiFokusHariIni(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS jumlah FROM sesi_belajar
            WHERE user_id = :user_id 
                AND jenis = 'FOKUS' 
                AND status = 'SELESAI'
                AND DATE(waktu_mulai) = CURDATE()"
        );
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetch()['jumlah'];
    }

    
}