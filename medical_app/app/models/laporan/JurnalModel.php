<?php
class JurnalModel {
  private $conn;

  public function __construct($conn) {
    $this->conn = $conn;
  }

  /**
   * Ambil semua data jurnal berdasarkan tanggal dan shift
   */
  public function getByTanggalShift($tanggal, $shift_id) {
    $sql = "
      SELECT j.*, k.nama_petugas, k.nip, k.qr_filename
      FROM jurnal j
      JOIN kasir k ON j.kasir_id = k.user_id
      WHERE j.tanggal = ? AND j.shift_id = ?
      ORDER BY j.id ASC
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('si', $tanggal, $shift_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Tambah entri jurnal baru
   */
  public function insert($tanggal, $shift_id, $keterangan, $debet, $kredit, $kasir_id) {
    $sql = "INSERT INTO jurnal (tanggal, shift_id, keterangan, debet, kredit, kasir_id)
        VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('sisddi', $tanggal, $shift_id, $keterangan, $debet, $kredit, $kasir_id);
    return $stmt->execute();
  }

  /**
   * Hitung total debet, kredit, dan saldo
   */
  public function getTotalByTanggalShift($tanggal, $shift_id) {
    $sql = "
      SELECT 
        SUM(debet) AS total_debet,
        SUM(kredit) AS total_kredit,
        SUM(debet - kredit) AS saldo
      FROM jurnal
      WHERE tanggal = ? AND shift_id = ?
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('si', $tanggal, $shift_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  /**
   * Ambil daftar shift untuk dropdown
   */
  public function getAllShift() {
    $sql = "SELECT * FROM shift ORDER BY id ASC";
    $result = $this->conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  /**
   * Ambil saldo akhir semua shift untuk tanggal tertentu
   */
  public function getSaldoPerTanggal($tanggal) {
    $sql = "
      SELECT 
        shift_id,
        SUM(debet - kredit) AS saldo
      FROM jurnal
      WHERE tanggal = ?
      GROUP BY shift_id
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('s', $tanggal);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $output = [];
    foreach ($result as $row) {
      $output[$row['shift_id']] = (float) $row['saldo'];
    }
    return $output;
  }

  public function getAllKasir() {
    $sql = "SELECT id, nama_petugas, nip, qr_filename FROM kasir ORDER BY nama_petugas ASC";
    $result = $this->conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public function getByTanggalShiftAndKasir($tanggal, $shift_id, $kasir_id) {
    $sql = "
      SELECT j.*, k.nama_petugas AS nama_petugas
      FROM jurnal j
      JOIN kasir k ON j.kasir_id = k.user_id
      WHERE j.tanggal = ? AND j.shift_id = ? AND j.kasir_id = ?
      ORDER BY j.id ASC
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('sii', $tanggal, $shift_id, $kasir_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getLastPetugasByTanggalShift($tanggal, $shift_id) {
    $sql = "
      SELECT k.nama_petugas
      FROM jurnal j
      JOIN kasir k ON j.kasir_id = k.user_id
      WHERE j.tanggal = ? AND j.shift_id = ?
      ORDER BY j.id DESC
      LIMIT 1
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('si', $tanggal, $shift_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function getListPetugasByTanggalShift($tanggal, $shift_id) {
    $sql = "
      SELECT DISTINCT k.nama_petugas
      FROM jurnal j
      JOIN kasir k ON j.kasir_id = k.user_id
      WHERE j.tanggal = ? AND j.shift_id = ?
      ORDER BY k.nama_petugas ASC
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('si', $tanggal, $shift_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return array_column($result, 'nama_petugas');
  }

  public function getPetugasByTanggalShift($tanggal, $shift_id) {
    $sql = "
      SELECT k.nama_petugas
      FROM jurnal j
      JOIN kasir k ON j.kasir_id = k.user_id
      WHERE j.tanggal = ? AND j.shift_id = ?
      ORDER BY j.id DESC
      LIMIT 1
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('si', $tanggal, $shift_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function getPetugasDetailByTanggalShift($tanggal, $shift_id) {
    $sql = "
      SELECT DISTINCT k.nama_petugas, k.nip, k.qr_filename
      FROM jurnal j
      JOIN kasir k ON j.kasir_id = k.user_id
      WHERE j.tanggal = ? AND j.shift_id = ?
      ORDER BY k.nama_petugas ASC
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('si', $tanggal, $shift_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function update($id, $keterangan, $debet, $kredit) {
    $sql = "UPDATE jurnal SET keterangan = ?, debet = ?, kredit = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      throw new Exception("Prepare gagal: " . $this->conn->error);
    }

    $stmt->bind_param('sdii', $keterangan, $debet, $kredit, $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
      throw new Exception("Update gagal: Tidak ada perubahan atau ID tidak ditemukan.");
    }

    $stmt->close();
  }

  public function getById($id) {
    $sql = "
      SELECT j.*, k.nama_petugas, s.nama_shift AS shift_nama
      FROM jurnal j
      JOIN kasir k ON j.kasir_id = k.user_id
      JOIN shift s ON j.shift_id = s.id
      WHERE j.id = ?
      LIMIT 1
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

}