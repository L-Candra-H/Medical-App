<?php
class PasienModel {
  // === GET ALL ===
  public static function getAll($conn) {
    $stmt = $conn->prepare("
      SELECT p.*, tri.id AS rawat_inap_id
      FROM pasien p
      LEFT JOIN tiket_rawat_inap tri 
        ON tri.pasien_id = p.id AND tri.status = 'AKTIF'
      ORDER BY p.id DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  // === COUNT ALL (dengan pencarian opsional) ===
  public static function countAll($conn, string $search = '') {
    $sql = "SELECT COUNT(*) AS jml FROM pasien p WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ?)";
      $stmt = $conn->prepare($sql);
      $like = "%$search%";
      $stmt->bind_param("ss", $like, $like);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc();
      return $result['jml'] ?? 0;
    } else {
      $result = $conn->query($sql);
      return $result ? $result->fetch_assoc()['jml'] : 0;
    }
  }

  // === GET PAGINATED (dengan pencarian opsional) ===
  public static function getPaginated($conn, int $limit, int $offset, string $search = '') {
    $sql = "
      SELECT p.*, tri.id AS rawat_inap_id
      FROM pasien p
      LEFT JOIN tiket_rawat_inap tri 
        ON tri.pasien_id = p.id AND tri.status = 'AKTIF'
      WHERE 1=1
    ";
    if (!empty($search)) {
      $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ?)";
    }
    $sql .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];

    if (!empty($search)) {
      $like = "%$search%";
      $stmt->bind_param("ssii", $like, $like, $limit, $offset);
    } else {
      $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  // === INSERT ===
  public static function insert($no_register, $nama, $conn) {
    $stmt = $conn->prepare("INSERT INTO pasien (nomor_register, nama_pasien) VALUES (?, ?)");
    if (!$stmt) {
      error_log("[PasienModel@insert] Prepare gagal: " . $conn->error);
      return false;
    }
    $stmt->bind_param("ss", $no_register, $nama);
    if (!$stmt->execute()) {
      error_log("[PasienModel@insert] Eksekusi gagal: " . $stmt->error);
      return false;
    }
    return true;
  }

  // === FIND BY ID ===
  public static function find($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM pasien WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  // === UPDATE ===
  public static function update($id, $no_register, $nama, $conn) {
    $stmt = $conn->prepare("UPDATE pasien SET nomor_register = ?, nama_pasien = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ssi", $no_register, $nama, $id);
    return $stmt->execute();
  }

  // === DELETE ===
  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM pasien WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  // === FIND BY ID (ringkas) ===
  public static function findById($conn, $id) {
    $stmt = $conn->prepare("SELECT id, nama_pasien, nomor_register FROM pasien WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }

  // === GET PASIEN BERTIKET ===
  public static function getPasienBertiket(mysqli $conn): array {
    $stmt = $conn->prepare("
      SELECT p.id, p.nama_pasien, p.nomor_register, tri.id AS rawat_inap_id
      FROM pasien p
      JOIN tiket_rawat_inap tri ON tri.pasien_id = p.id
      WHERE tri.status = 'AKTIF'
      ORDER BY p.nama_pasien ASC
    ");
    if (!$stmt) {
      error_log("[PasienModel@getPasienBertiket] Prepare gagal: " . $conn->error);
      return [];
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }
}