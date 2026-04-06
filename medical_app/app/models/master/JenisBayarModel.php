<?php
class JenisBayarModel {
  public static function getAll($conn) {
    $result = $conn->query("SELECT * FROM jenis_bayar ORDER BY id DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function insert($nama, $conn) {
    $stmt = $conn->prepare("INSERT INTO jenis_bayar (nama_jenis) VALUES (?)");
    if (!$stmt) return false;
    $stmt->bind_param("s", $nama);
    return $stmt->execute();
  }

  public static function find($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM jenis_bayar WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function update($id, $nama, $conn) {
    $stmt = $conn->prepare("UPDATE jenis_bayar SET nama_jenis = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("si", $nama, $id);
    return $stmt->execute();
  }

  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM jenis_bayar WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }
}
