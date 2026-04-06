<?php
class PotonganModel {

  public static function getAllByRawatInap($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("
      SELECT p.*, ps.nama_pasien, ps.nomor_register, k.nama_petugas, k.nip,
             r.status AS status_tiket
      FROM potongan_rawat_inap p
      JOIN tiket_rawat_inap r ON r.id = p.rawat_inap_id
      JOIN pasien ps ON ps.id = r.pasien_id
      LEFT JOIN kasir k ON k.id = p.dibuat_oleh_id
      WHERE p.rawat_inap_id = ?
      ORDER BY p.tanggal ASC
    ");
    if (!$stmt) return [];

    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function getById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM potongan_rawat_inap WHERE id = ?");
    if (!$stmt) return null;

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_assoc() : null;
  }

  public static function store($conn, $data) {
    $stmt = $conn->prepare("
      INSERT INTO potongan_rawat_inap 
      (rawat_inap_id, tanggal, jumlah, tipe, keterangan, created_at, updated_at, dibuat_oleh_id)
      VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)
    ");
    if (!$stmt) return false;

    $jumlah = floatval($data['jumlah']);
    $stmt->bind_param(
      "issssi",
      $data['rawat_inap_id'],
      $data['tanggal'],
      $jumlah,
      $data['tipe'],
      $data['keterangan'],
      $data['dibuat_oleh_id']
    );

    return $stmt->execute();
  }

  public static function update($conn, $id, $data) {
    $stmt = $conn->prepare("
      UPDATE potongan_rawat_inap SET
        tanggal = ?, 
        jumlah = ?, 
        tipe = ?, 
        keterangan = ?, 
        rawat_inap_id = ?,
        updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    ");
    if (!$stmt) return false;

    $jumlah = floatval($data['jumlah']);
    $stmt->bind_param(
      "ssssii",
      $data['tanggal'],
      $jumlah,
      $data['tipe'],
      $data['keterangan'],
      $data['rawat_inap_id'],
      $id
    );

    return $stmt->execute();
  }

  public static function delete($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM potongan_rawat_inap WHERE id = ?");
    if (!$stmt) return false;

    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  public static function existsForRawatInap($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("SELECT id FROM potongan_rawat_inap WHERE rawat_inap_id = ? LIMIT 1");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result && $result->num_rows > 0);
  }

  // === COUNT ALL (dengan pencarian opsional) ===
  public static function countAll($conn, string $search = ''): int {
    $sql = "SELECT COUNT(*) AS jml
            FROM potongan_rawat_inap p
            JOIN tiket_rawat_inap r ON r.id = p.rawat_inap_id
            JOIN pasien pa ON pa.id = r.pasien_id
            WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (pa.nama_pasien LIKE ? OR pa.nomor_register LIKE ? OR p.tipe LIKE ?)";
      $stmt = $conn->prepare($sql);
      $like = "%$search%";
      $stmt->bind_param("sss", $like, $like, $like);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc();
      return (int)($result['jml'] ?? 0);
    } else {
      $result = $conn->query($sql);
      return $result ? (int)$result->fetch_assoc()['jml'] : 0;
    }
  }

  // === GET PAGINATED (dengan pencarian opsional) ===
  public static function getPaginated($conn, int $limit, int $offset, string $search = ''): array {
    $sql = "SELECT p.*, r.tanggal_masuk, r.status AS status_tiket,
                   pa.nomor_register, pa.nama_pasien
            FROM potongan_rawat_inap p
            JOIN tiket_rawat_inap r ON r.id = p.rawat_inap_id
            JOIN pasien pa ON pa.id = r.pasien_id
            WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (pa.nama_pasien LIKE ? OR pa.nomor_register LIKE ? OR p.tipe LIKE ?)";
    }
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";

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
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  // === GET ALL (tanpa pagination) ===
  public static function getAllWithPasien($conn) {
    $query = "
      SELECT 
        p.*, 
        r.tanggal_masuk, 
        r.status AS status_tiket,
        pa.nomor_register, 
        pa.nama_pasien
      FROM potongan_rawat_inap p
      JOIN tiket_rawat_inap r ON r.id = p.rawat_inap_id
      JOIN pasien pa ON pa.id = r.pasien_id
      ORDER BY p.created_at DESC
    ";
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function getTotalByRawatInap($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("
      SELECT SUM(jumlah) as total 
      FROM potongan_rawat_inap 
      WHERE rawat_inap_id = ?
    ");
    if (!$stmt) return 0;

    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;

    return floatval($row['total'] ?? 0);
  }

  public static function getPasienByRawatInap($conn, int $rawat_inap_id) {
      $stmt = $conn->prepare("
          SELECT r.id AS rawat_inap_id,
                 r.tanggal_masuk,
                 r.status AS status_tiket,
                 ps.id AS pasien_id,
                 ps.nama_pasien,
                 ps.nomor_register
          FROM tiket_rawat_inap r
          JOIN pasien ps ON ps.id = r.pasien_id
          WHERE r.id = ?
          LIMIT 1
      ");
      if (!$stmt) return null;

      $stmt->bind_param("i", $rawat_inap_id);
      $stmt->execute();
      $result = $stmt->get_result();

      return $result ? $result->fetch_assoc() : null;
  }
}