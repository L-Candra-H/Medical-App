<?php
class RekapAsistenModel {
  private $conn;

  public function __construct($conn) {
    $this->conn = $conn;
  }

  /**
   * Ambil rekap honor asisten berdasarkan periode
   * @param int $asisten_id
   * @param int $bulan
   * @param int $tahun
   * @return array
   */
  public function getRekapAsisten($asisten_id, $bulan, $tahun) {
    if (!$asisten_id || !$bulan || !$tahun) return [];

    $sql = "
      SELECT 
        nama_pasien,
        nomor_register,
        nama_jenis_bayar,
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
            WHEN asisten_tindakan_1_id = ? THEN biaya_asisten_1
            WHEN asisten_tindakan_2_id = ? THEN biaya_asisten_2
            WHEN asisten_tindakan_3_id = ? THEN biaya_asisten_3
            WHEN instrumen_onlop_id = ? THEN biaya_instrumen_onlop
            ELSE 0
          END
        ) AS honor_asisten

      FROM rawat_inap_rincian
      LEFT JOIN kasir ON rawat_inap_rincian.dibuat_oleh = kasir.user_id

      WHERE (
        asisten_tindakan_1_id = ? OR asisten_tindakan_2_id = ? OR asisten_tindakan_3_id = ? OR instrumen_onlop_id = ?
      )
      AND (
        (MONTH(ibu_mulai) = ? AND YEAR(ibu_mulai) = ?)
        OR
        (MONTH(anak_mulai) = ? AND YEAR(anak_mulai) = ?)
      )
      AND rawat_inap_rincian.status = 'SELESAI'
      GROUP BY nomor_register
      ORDER BY COALESCE(ibu_mulai, anak_mulai) ASC
    ";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      throw new Exception("Prepare failed: " . $this->conn->error);
    }

    $stmt->bind_param(
        'iiiiiiiiiiii',
          $asisten_id, $asisten_id, $asisten_id, $asisten_id, // SELECT honor
          $asisten_id, $asisten_id, $asisten_id, $asisten_id, // WHERE
          $bulan, $tahun,
          $bulan, $tahun
    );

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Hitung total honor asisten
   */
  public function getTotalAsisten($asisten_id, $bulan, $tahun) {
    if (!$asisten_id || !$bulan || !$tahun) return 0;

    $sql = "
      SELECT SUM(honor) AS total FROM (
        SELECT 
          CASE WHEN asisten_tindakan_1_id = ? THEN biaya_asisten_1 ELSE 0 END +
          CASE WHEN asisten_tindakan_2_id = ? THEN biaya_asisten_2 ELSE 0 END +
          CASE WHEN asisten_tindakan_3_id = ? THEN biaya_asisten_3 ELSE 0 END +
          CASE WHEN instrumen_onlop_id = ? THEN biaya_instrumen_onlop ELSE 0 END
        AS honor
        FROM rawat_inap_rincian
        WHERE (
          asisten_tindakan_1_id = ? OR asisten_tindakan_2_id = ? OR asisten_tindakan_3_id = ? OR instrumen_onlop_id = ?
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
      'iiiiiiiiiiii',
      $asisten_id, $asisten_id, $asisten_id, $asisten_id, // CASE
      $asisten_id, $asisten_id, $asisten_id, $asisten_id, // WHERE
      $bulan, $tahun,
      $bulan, $tahun
        );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return (float) ($result['total'] ?? 0);
  }
}