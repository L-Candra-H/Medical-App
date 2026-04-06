<?php
class DokterModel {
  public static function getAll($conn) {
    $result = $conn->query("SELECT * FROM dokter ORDER BY id DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function insert($nama, $spesialis, $conn) {
    $stmt = $conn->prepare("INSERT INTO dokter (nama_dokter, spesialisasi) VALUES (?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("ss", $nama, $spesialis);
    return $stmt->execute();
  }

  public static function find($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM dokter WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function update($id, $nama, $spesialis, $status, $conn) {
    $stmt = $conn->prepare("UPDATE dokter SET nama_dokter = ?, spesialisasi = ?, status = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("sssi", $nama, $spesialis, $status, $id);
    return $stmt->execute();
  }

  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM dokter WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  public static function getNamaById($id, $conn) {
    if (!$id) return '-';
    $stmt = $conn->prepare("SELECT nama_dokter FROM dokter WHERE id = ?");
    if (!$stmt) return '-';
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['nama_dokter'] ?? '-';
  }

  public static function existsSpesialisasi($conn, string $spesialisasi): bool {
      $stmt = $conn->prepare("SELECT COUNT(*) AS jml FROM dokter WHERE LOWER(spesialisasi) = LOWER(?)");
      if (!$stmt) return false;
      $stmt->bind_param("s", $spesialisasi);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc();
      return ($result['jml'] ?? 0) > 0;
  }

  // 🔹 Hitung total data dengan pencarian
  public static function countAll($conn, $search = '') {
    if (!empty($search)) {
      $stmt = $conn->prepare("SELECT COUNT(*) AS jml 
                              FROM dokter 
                              WHERE nama_dokter LIKE ? OR spesialisasi LIKE ?");
      $like = "%$search%";
      $stmt->bind_param("ss", $like, $like);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc();
      return $result['jml'] ?? 0;
    } else {
      $result = $conn->query("SELECT COUNT(*) AS jml FROM dokter");
      return $result ? $result->fetch_assoc()['jml'] : 0;
    }
  }

  // 🔹 Ambil data dengan pagination + pencarian
  public static function getPaginated($conn, $limit, $offset, $search = '') {
    if (!empty($search)) {
      $stmt = $conn->prepare("SELECT * FROM dokter 
                              WHERE nama_dokter LIKE ? OR spesialisasi LIKE ?
                              ORDER BY id DESC LIMIT ? OFFSET ?");
      $like = "%$search%";
      $stmt->bind_param("ssii", $like, $like, $limit, $offset);
    } else {
      $stmt = $conn->prepare("SELECT * FROM dokter 
                              ORDER BY id DESC LIMIT ? OFFSET ?");
      $stmt->bind_param("ii", $limit, $offset);
    }

    if (!$stmt) return [];
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
  }

}