<?php
class RekapDokterModel {
  private $conn;

  public function __construct($conn) {
    $this->conn = $conn;
  }

  /**
   * Ambil rekap HR dokter berdasarkan filter
   * @param int $dokter_id
   * @param int $bulan
   * @param int $tahun
   * @return array
   */
  public function getRekapDokter($dokter_id, $bulan, $tahun) {
    if (!$dokter_id || !$bulan || !$tahun) return [];

    $sql = "
      SELECT 
        nama_pasien,
        nomor_register,
        nama_jenis_bayar,
        nama_kelas_perawatan,
        ibu_mulai,
        ibu_selesai,
        anak_mulai,
        anak_selesai,
        rawat_inap_rincian.nip,
        rawat_inap_rincian.qr_validasi_path AS qr_path,
        rawat_inap_rincian.tanggal_pemeriksaan,
        rawat_inap_rincian.dibuat_oleh,
        kasir.nama_petugas AS nama_petugas,
        kasir.user_id AS user_id,
        kasir.nip,
        kasir.qr_filename,

        SUM(
          CASE 
            WHEN dokter_visite_1_id = ? THEN total_visite_1
            WHEN dokter_visite_2_id = ? THEN total_visite_2
            WHEN dokter_visite_3_id = ? THEN total_visite_3
            ELSE 0
          END
        ) AS visite,

        SUM(
          CASE 
            WHEN dokter_tindakan_1_id = ? THEN biaya_tindakan_dokter_1
            WHEN dokter_tindakan_2_id = ? THEN biaya_tindakan_dokter_2
            WHEN dokter_tindakan_3_id = ? THEN biaya_tindakan_dokter_3
            ELSE 0
          END
        ) AS tindakan,

        SUM(
          CASE 
            WHEN dokter_konsultasi_1_id = ? THEN biaya_konsul_1
            WHEN dokter_konsultasi_2_id = ? THEN biaya_konsul_2
            WHEN dokter_konsultasi_3_id = ? THEN biaya_konsul_3
            ELSE 0
          END
        ) AS konsultasi

      FROM rawat_inap_rincian
      LEFT JOIN kasir ON rawat_inap_rincian.dibuat_oleh = kasir.user_id

      WHERE (
        dokter_visite_1_id = ? OR dokter_visite_2_id = ? OR dokter_visite_3_id = ?
        OR dokter_tindakan_1_id = ? OR dokter_tindakan_2_id = ? OR dokter_tindakan_3_id = ?
        OR dokter_konsultasi_1_id = ? OR dokter_konsultasi_2_id = ? OR dokter_konsultasi_3_id = ?
      )
      AND (
        (MONTH(ibu_mulai) = ? AND YEAR(ibu_mulai) = ?)
        OR
        (MONTH(anak_mulai) = ? AND YEAR(anak_mulai) = ?)
      )
      AND rawat_inap_rincian.status = ?
      GROUP BY nomor_register
      ORDER BY COALESCE(ibu_mulai, anak_mulai) ASC
    ";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      throw new Exception("Prepare failed: " . $this->conn->error);
    }

    $status = 'SELESAI';

    $stmt->bind_param(
      'iiiiiiiiiiiiiiiiiiiiiis',
      $dokter_id, $dokter_id, $dokter_id, // SELECT visite
      $dokter_id, $dokter_id, $dokter_id, // SELECT tindakan
      $dokter_id, $dokter_id, $dokter_id, // SELECT konsultasi

      $dokter_id, $dokter_id, $dokter_id, // WHERE visite
      $dokter_id, $dokter_id, $dokter_id, // WHERE tindakan
      $dokter_id, $dokter_id, $dokter_id, // WHERE konsultasi

      $bulan, $tahun, // ibu_mulai
      $bulan, $tahun, // anak_mulai
      $status
    );

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Ambil total honor dokter dari semua peran dalam periode
   * @param int $dokter_id
   * @param int $bulan
   * @param int $tahun
   * @return float
   */
  public function getTotalDokter($dokter_id, $bulan, $tahun) {
    if (!$dokter_id || !$bulan || !$tahun) return 0;

    $sql = "
      SELECT SUM(honor) AS total FROM (
        SELECT 
          CASE WHEN dokter_visite_1_id = ? THEN total_visite_1 ELSE 0 END +
          CASE WHEN dokter_visite_2_id = ? THEN total_visite_2 ELSE 0 END +
          CASE WHEN dokter_visite_3_id = ? THEN total_visite_3 ELSE 0 END +
          CASE WHEN dokter_tindakan_1_id = ? THEN biaya_tindakan_dokter_1 ELSE 0 END +
          CASE WHEN dokter_tindakan_2_id = ? THEN biaya_tindakan_dokter_2 ELSE 0 END +
          CASE WHEN dokter_tindakan_3_id = ? THEN biaya_tindakan_dokter_3 ELSE 0 END +
          CASE WHEN dokter_konsultasi_1_id = ? THEN biaya_konsul_1 ELSE 0 END +
          CASE WHEN dokter_konsultasi_2_id = ? THEN biaya_konsul_2 ELSE 0 END +
          CASE WHEN dokter_konsultasi_3_id = ? THEN biaya_konsul_3 ELSE 0 END
        AS honor
        FROM rawat_inap_rincian
        WHERE (
          dokter_visite_1_id = ? OR dokter_visite_2_id = ? OR dokter_visite_3_id = ?
          OR dokter_tindakan_1_id = ? OR dokter_tindakan_2_id = ? OR dokter_tindakan_3_id = ?
          OR dokter_konsultasi_1_id = ? OR dokter_konsultasi_2_id = ? OR dokter_konsultasi_3_id = ?
        )
        AND (
          (MONTH(ibu_mulai) = ? AND YEAR(ibu_mulai) = ?)
          OR
          (MONTH(anak_mulai) = ? AND YEAR(anak_mulai) = ?)
        )
        AND status = 'SELESAI'
      ) AS subquery
    ";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      throw new Exception("Prepare failed: " . $this->conn->error);
    }

    $stmt->bind_param(
      'iiiiiiiiiiiiiiiiii',
      $dokter_id, $dokter_id, $dokter_id,
      $dokter_id, $dokter_id, $dokter_id,
      $dokter_id, $dokter_id, $dokter_id,

      $dokter_id, $dokter_id, $dokter_id,
      $dokter_id, $dokter_id, $dokter_id,
      $dokter_id, $dokter_id, $dokter_id,

      $bulan, $tahun,
      $bulan, $tahun
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return (float) ($result['total'] ?? 0);
  }
}