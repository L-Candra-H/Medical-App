<?php
class JabatanModel {
  private static function getConnection() {
    $conn = require(__DIR__ . '/../../../config/database.php');
    if (!($conn instanceof mysqli)) {
      throw new Exception("Invalid database connection");
    }
    return $conn;
  }

  public static function getAll() {
    $conn = self::getConnection();
    return $conn->query("SELECT * FROM jabatan ORDER BY id ASC");
  }

  public static function getById($id) {
    $conn = self::getConnection();
    $stmt = $conn->prepare("SELECT * FROM jabatan WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function create($nama_jabatan, $keterangan) {
    $conn = self::getConnection();
    $stmt = $conn->prepare("INSERT INTO jabatan (nama_jabatan, keterangan) VALUES (?, ?)");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("ss", $nama_jabatan, $keterangan);
    return $stmt->execute();
  }

  public static function update($id, $nama_jabatan, $keterangan) {
    $conn = self::getConnection();
    $stmt = $conn->prepare("UPDATE jabatan SET nama_jabatan = ?, keterangan = ? WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("ssi", $nama_jabatan, $keterangan, $id);
    return $stmt->execute();
  }

  public static function delete($id) {
    $conn = self::getConnection();
    $stmt = $conn->prepare("DELETE FROM jabatan WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }
}
?>
