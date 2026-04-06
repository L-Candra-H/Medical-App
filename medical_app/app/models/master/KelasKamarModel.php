<?php
class KelasKamarModel {
  // === GET ALL (tanpa pagination) ===
  public static function getAll($conn) {
    $result = $conn->query("SELECT * FROM kelas_kamar ORDER BY id DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  // === COUNT ALL (dengan pencarian opsional) ===
  public static function countAll($conn, string $search = '') {
    $sql = "SELECT COUNT(*) AS jml FROM kelas_kamar WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (nama_kelas LIKE ? OR jenis_pasien LIKE ?)";
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
    $sql = "SELECT * FROM kelas_kamar WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (nama_kelas LIKE ? OR jenis_pasien LIKE ?)";
    }
    $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";

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
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  // === INSERT ===
  public static function insert($nama, $jenis, $tarif, $conn) {
    $stmt = $conn->prepare("INSERT INTO kelas_kamar (nama_kelas, jenis_pasien, tarif) VALUES (?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("ssd", $nama, $jenis, $tarif);
    return $stmt->execute();
  }

  // === FIND BY ID ===
  public static function find($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM kelas_kamar WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  // === UPDATE ===
  public static function update($id, $nama, $jenis, $tarif, $conn) {
    $stmt = $conn->prepare("UPDATE kelas_kamar SET nama_kelas = ?, jenis_pasien = ?, tarif = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ssdi", $nama, $jenis, $tarif, $id);
    return $stmt->execute();
  }

  // === DELETE ===
  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM kelas_kamar WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  // === GET TARIF BY ID ===
  public static function getTarifById($conn, $id) {
    $stmt = $conn->prepare("SELECT tarif FROM kelas_kamar WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['tarif'] ?? 0;
  }

  // === COUNT BY SEARCH ===
  public static function countBySearch($conn, string $search = '') {
      $sql = "SELECT COUNT(*) AS jml FROM kelas_kamar WHERE 1=1";
      if (!empty($search)) {
          $sql .= " AND (nama_kelas LIKE ? OR jenis_pasien LIKE ?)";
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
}
