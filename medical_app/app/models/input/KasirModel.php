<?php
class KasirModel {
  // === GET ALL ===
  public static function getAll($conn) {
    $query = "
      SELECT 
        k.id, k.nama_petugas, k.nip, k.status, k.qr_filename, k.user_id,
        j.nama_jabatan
      FROM kasir k
      LEFT JOIN jabatan j ON k.jabatan_id = j.id
      ORDER BY k.id DESC
    ";
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  // === COUNT ALL (dengan pencarian opsional) ===
  public static function countAll($conn, string $search = '') {
    $sql = "SELECT COUNT(*) AS jml FROM kasir k 
            LEFT JOIN jabatan j ON k.jabatan_id = j.id 
            WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (k.nama_petugas LIKE ? OR k.nip LIKE ? OR j.nama_jabatan LIKE ? OR k.status LIKE ?)";
      $stmt = $conn->prepare($sql);
      $like = "%$search%";
      $stmt->bind_param("ssss", $like, $like, $like, $like);
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
      SELECT 
        k.id, k.nama_petugas, k.nip, k.status, k.qr_filename, k.user_id,
        j.nama_jabatan
      FROM kasir k
      LEFT JOIN jabatan j ON k.jabatan_id = j.id
      WHERE 1=1
    ";
    if (!empty($search)) {
      $sql .= " AND (k.nama_petugas LIKE ? OR k.nip LIKE ? OR j.nama_jabatan LIKE ? OR k.status LIKE ?)";
    }
    $sql .= " ORDER BY k.id DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];

    if (!empty($search)) {
      $like = "%$search%";
      $stmt->bind_param("ssssii", $like, $like, $like, $like, $limit, $offset);
    } else {
      $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  // === FIND BY ID ===
  public static function find($id, $conn) {
    $stmt = $conn->prepare("
      SELECT k.id, k.user_id, k.nama_petugas, k.nip, k.status, k.qr_filename, k.jabatan_id,
             j.nama_jabatan
      FROM kasir k
      LEFT JOIN jabatan j ON k.jabatan_id = j.id
      WHERE k.id = ?
    ");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() ?? null : null;
  }

  // === GET BY USER ID ===
  public static function getByUserId(mysqli $conn, int $userId) {
    $stmt = $conn->prepare("
      SELECT k.id, k.user_id, k.nama_petugas, k.nip, k.status, k.qr_filename, k.jabatan_id,
             j.nama_jabatan
      FROM kasir k
      LEFT JOIN jabatan j ON k.jabatan_id = j.id
      WHERE k.user_id = ?
    ");
    if (!$stmt) return null;
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() ?? null : null;
  }

  // === CREATE ===
  public static function createKasir($userId, $namaPetugas, $nip, $jabatanId, $conn) {
    // Cek duplikasi NIP
    $check = $conn->prepare("SELECT COUNT(*) FROM kasir WHERE nip = ?");
    if (!$check) return false;
    $check->bind_param("s", $nip);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) return false;

    $status = 'aktif';
    $stmt = $conn->prepare("
      INSERT INTO kasir (user_id, nama_petugas, nip, status, jabatan_id)
      VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmt) return false;
    $stmt->bind_param("isssi", $userId, $namaPetugas, $nip, $status, $jabatanId);
    return $stmt->execute();
  }

  // === UPDATE ===
  public static function update($id, $namaPetugas, $nip, $jabatanId, $status, $conn) {
    $stmt = $conn->prepare("
      UPDATE kasir SET nama_petugas = ?, nip = ?, jabatan_id = ?, status = ? WHERE id = ?
    ");
    if (!$stmt) return false;
    $stmt->bind_param("ssisi", $namaPetugas, $nip, $jabatanId, $status, $id);
    return $stmt->execute();
  }

  public static function updateStatus($id, $status, $conn) {
    $stmt = $conn->prepare("UPDATE kasir SET status = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
  }

  public static function updateQR($id, $filename, $conn) {
    $stmt = $conn->prepare("UPDATE kasir SET qr_filename = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("si", $filename, $id);
    return $stmt->execute();
  }

  // === DELETE ===
  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM kasir WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  // === LAST INSERTED ID ===
  public static function getLastInsertedId($conn) {
    return $conn->insert_id;
  }

  // === FIND BY NIP ===
  public static function findByNip($nip, $conn) {
    $stmt = $conn->prepare("
      SELECT nama_petugas, nip, qr_filename 
      FROM kasir 
      WHERE nip = ?
    ");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }
}