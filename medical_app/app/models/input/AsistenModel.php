<?php
class AsistenModel {
  // === GET ALL ===
  public static function getAll($conn) {
    $result = $conn->query("SELECT * FROM asisten_dokter ORDER BY id DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  // === INSERT ===
  public static function insert($nama, $keterangan, $status, $conn) {
    $stmt = $conn->prepare("INSERT INTO asisten_dokter (nama_asisten, keterangan, status) VALUES (?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("sss", $nama, $keterangan, $status);
    return $stmt->execute();
  }

  // === FIND BY ID ===
  public static function find($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM asisten_dokter WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  // === UPDATE ===
  public static function update($id, $nama, $keterangan, $status, $conn) {
    $stmt = $conn->prepare("UPDATE asisten_dokter SET nama_asisten = ?, keterangan = ?, status = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("sssi", $nama, $keterangan, $status, $id);
    return $stmt->execute();
  }

  // === DELETE ===
  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM asisten_dokter WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  // === GET NAMA BY ID ===
  public static function getNamaById($id, $conn) {
    if (!$id) return '-';
    $stmt = $conn->prepare("SELECT nama_asisten FROM asisten_dokter WHERE id = ?");
    if (!$stmt) return '-';
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['nama_asisten'] ?? '-';
  }

  // === COUNT ALL (dengan pencarian opsional) ===
  public static function countAll($conn, string $search = '') {
    $sql = "SELECT COUNT(*) AS jml FROM asisten_dokter WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (nama_asisten LIKE ? OR keterangan LIKE ? OR status LIKE ?)";
      $stmt = $conn->prepare($sql);
      $like = "%$search%";
      $stmt->bind_param("sss", $like, $like, $like);
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
    $sql = "SELECT * FROM asisten_dokter WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (nama_asisten LIKE ? OR keterangan LIKE ? OR status LIKE ?)";
    }
    $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];

    if (!empty($search)) {
      $like = "%$search%";
      $stmt->bind_param("sssii", $like, $like, $like, $limit, $offset);
    } else {
      $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
  }
}