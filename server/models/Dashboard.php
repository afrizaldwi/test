<?php
require_once __DIR__ . '/../config/connect.php';

class Dashboard
{
    private $conn;

    public function __construct()
    {
        try {
            $this->conn = (new Database())->getCon();
        } catch (\Throwable $e) {
            $this->conn = null;
            error_log("[" . date("Y-m-d H:i:s") . "] Connection failed: " . $e->getMessage() . "\r\n", 3, __DIR__ . '/../logs/error.log');
        }
    }

    public function __destruct()
    {
        if ($this->conn !== null) {
            pg_close($this->conn);
        }
    }
    public function adminDashboard()
    {
        try {
            if ($this->conn === null) {
                throw new Exception("Database connection failed");
            }

            $kamar = "SELECT status_kamar, COUNT(*) as total FROM kamar GROUP BY status_kamar";

            $keluhan = "SELECT COUNT(*) FROM keluhan WHERE status_keluhan = 'pending'";

            $tagihan = "SELECT status_tagihan, COUNT(*) as count, SUM(total_tagihan) as total FROM tagihan WHERE status_tagihan IN ('belum_bayar', 'telat') GROUP BY status_tagihan";

            $pengeluaran = "SELECT SUM(jumlah_pengeluaran) FROM pengeluaran WHERE DATE_TRUNC('month', tanggal_pengeluaran) = DATE_TRUNC('month', CURRENT_DATE)";

            $tamu = "SELECT COUNT(*) FROM buku_tamu WHERE DATE(waktu_berkunjung) = CURRENT_DATE";

            $getKamar = pg_query($this->conn,  $kamar);
            $getKeluhan = pg_query($this->conn,  $keluhan);
            $getTagihan = pg_query($this->conn,  $tagihan);
            $getPengeluaran = pg_query($this->conn,  $pengeluaran);
            $getTamu = pg_query($this->conn,  $tamu);

            $dataKamar = pg_fetch_all($getKamar) ?: [];
            $dataKeluhan = pg_fetch_all($getKeluhan) ?: [];
            $dataTagihan = pg_fetch_all($getTagihan) ?: [];
            $dataPengeluaran = pg_fetch_all($getPengeluaran) ?: [];
            $dataTamu = pg_fetch_all($getTamu) ?: [];

            $data = [
                'kamar' => $dataKamar,
                'keluhan' => $dataKeluhan,
                'tagihan' => $dataTagihan,
                'pengeluaran' => $dataPengeluaran,
                'tamu' => $dataTamu
            ];

            return $data;
        } catch (\Throwable $e) {
            error_log("[" . date("Y-m-d H:i:s") . "] Query failed: " . $e->getMessage() . "\r\n", 3, __DIR__ . '/../logs/error.log');
            return null;
        }
    }

    public function penyewaDashboard()
    {
        try {
            if ($this->conn === null) throw new Exception("Database connection failed");

            $payload = verifyJWT($_COOKIE['kost_token']);
            $id_auth = $payload['id_auth'];

            $kamar = "SELECT 
            k.nomor_kamar, 
            k.fasilitas, 
            k.harga_bulanan,
            rs.tanggal_masuk,
            rs.tanggal_keluar,
            rs.status_sewa
        FROM kamar k
        JOIN riwayat_sewa rs ON rs.id_kamar = k.id_kamar
        JOIN users u ON u.id_user = rs.id_user
        WHERE u.id_auth = $1 AND rs.status_sewa = 'aktif'";

            $tagihan = "SELECT 
            t.kode_invoice,
            t.total_tagihan,
            t.tanggal_jatuh_tempo,
            t.status_tagihan
        FROM tagihan t
        JOIN riwayat_sewa rs ON rs.id_sewa = t.id_sewa
        JOIN users u ON u.id_user = rs.id_user
        WHERE u.id_auth = $1 
        AND t.status_tagihan IN ('belum_bayar', 'telat')
        ORDER BY t.tanggal_jatuh_tempo ASC
        LIMIT 1";

            $keluhan = "SELECT 
            status_keluhan,
            COUNT(*) as total
        FROM keluhan
        WHERE id_sewa IN (
            SELECT rs.id_sewa 
            FROM riwayat_sewa rs
            JOIN users u ON u.id_user = rs.id_user
            WHERE u.id_auth = $1
        )
        GROUP BY status_keluhan";

            pg_prepare($this->conn, "get_kamar_penyewa",   $kamar);
            pg_prepare($this->conn, "get_tagihan_penyewa", $tagihan);
            pg_prepare($this->conn, "get_keluhan_penyewa", $keluhan);

            $dataKamar   = pg_fetch_all(pg_execute($this->conn, "get_kamar_penyewa",   [$id_auth])) ?: [];
            $dataTagihan = pg_fetch_all(pg_execute($this->conn, "get_tagihan_penyewa", [$id_auth])) ?: [];
            $dataKeluhan = pg_fetch_all(pg_execute($this->conn, "get_keluhan_penyewa", [$id_auth])) ?: [];

            return [
                'kamar'   => $dataKamar[0]   ?? null,
                'tagihan' => $dataTagihan[0] ?? null,
                'keluhan' => $dataKeluhan
            ];
        } catch (\Throwable $e) {
            error_log("[" . date("Y-m-d H:i:s") . "] Query failed: " . $e->getMessage() . "\r\n", 3, __DIR__ . '/../logs/error.log');
            return null;
        }
    }
}
