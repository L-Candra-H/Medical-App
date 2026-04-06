<?php
class UangMukaModel {

  public static function getAllByRawatInap($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("
      SELECT u.*, p.nama_pasien, p.nomor_register,
        k.nama_petugas AS nama_pembuat,
        k.nip AS nip_pembuat,
        t.status AS status_tiket
      FROM uang_muka_rawat_inap u
      JOIN tiket_rawat_inap t ON t.id = u.rawat_inap_id
      JOIN pasien p ON p.id = t.pasien_id
      LEFT JOIN kasir k ON k.user_id = u.dibuat_oleh_id
      WHERE u.rawat_inap_id = ?
      ORDER BY u.tanggal ASC
    ");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public static function getRiwayatByTiket($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("
      SELECT u.tanggal, u.jumlah, u.metode_pembayaran, u.keterangan,
             k.nama_petugas
      FROM uang_muka_rawat_inap u
      LEFT JOIN kasir k ON k.user_id = u.dibuat_oleh_id
      WHERE u.rawat_inap_id = ?
      ORDER BY u.tanggal DESC
    ");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public static function getAllWithPasien($conn) {
    $query = "
      SELECT t.id AS rawat_inap_id, p.nama_pasien, p.nomor_register, t.tanggal_masuk
      FROM tiket_rawat_inap t
      JOIN pasien p ON p.id = t.pasien_id
      WHERE t.status IN ('AKTIF', 'MENUNGGU')
      ORDER BY t.tanggal_masuk DESC
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
  }

  public static function getPasienInfo($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("
      SELECT t.id AS rawat_inap_id, p.nama_pasien, p.nomor_register
      FROM tiket_rawat_inap t
      JOIN pasien p ON t.pasien_id = p.id
      WHERE t.id = ?
    ");
    if (!$stmt) return [];
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?? [];
  }

  public static function exists($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM uang_muka_rawat_inap WHERE rawat_inap_id = ?");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
  }

  public static function insert($conn, $data) {
    $stmt = $conn->prepare("
      INSERT INTO uang_muka_rawat_inap
      (rawat_inap_id, dibuat_oleh_id, dicetak_oleh_id, tanggal, jumlah, metode_pembayaran, keterangan, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) return false;

    $stmt->bind_param(
      "iiissssss",
      $data['rawat_inap_id'],
      $data['dibuat_oleh_id'],
      $data['dicetak_oleh_id'],
      $data['tanggal'],
      $data['jumlah'],
      $data['metode_pembayaran'],
      $data['keterangan'],
      $data['created_at'],
      $data['updated_at']
    );

    return $stmt->execute();
  }

  public static function update($conn, $id, $data) {
    $stmt = $conn->prepare("
      UPDATE uang_muka_rawat_inap SET
        tanggal = ?,
        jumlah = ?,
        metode_pembayaran = ?,
        keterangan = ?,
        rawat_inap_id = ?,
        dibuat_oleh_id = ?,
        updated_at = NOW()
      WHERE id = ?
    ");
    if (!$stmt) return false;

    $stmt->bind_param(
      "sdsssii",
      $data['tanggal'],
      $data['jumlah'],
      $data['metode_pembayaran'],
      $data['keterangan'],
      $data['rawat_inap_id'],
      $data['dibuat_oleh_id'],
      $id
    );

    return $stmt->execute();
  }

  public static function getDetail($conn, $id) {
    $stmt = $conn->prepare("
      SELECT u.*, p.nama_pasien, p.nomor_register,
        k.nama_petugas AS nama_pembuat,
        k.nip AS nip_pembuat
      FROM uang_muka_rawat_inap u
      JOIN tiket_rawat_inap t ON t.id = u.rawat_inap_id
      JOIN pasien p ON p.id = t.pasien_id
      LEFT JOIN kasir k ON k.user_id = u.dibuat_oleh_id
      WHERE u.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function getCetakData($conn, $id) {
    $stmt = $conn->prepare("
      SELECT u.*, p.nama_pasien, p.nomor_register,
        k.nama_petugas, k.nip
      FROM uang_muka_rawat_inap u
      JOIN tiket_rawat_inap t ON t.id = u.rawat_inap_id
      JOIN pasien p ON p.id = t.pasien_id
      LEFT JOIN kasir k ON k.user_id = u.dibuat_oleh_id
      WHERE u.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function setDicetakOleh($conn, $id, $petugas_id) {
    $stmt = $conn->prepare("UPDATE uang_muka_rawat_inap SET dicetak_oleh_id = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ii", $petugas_id, $id);
    return $stmt->execute();
  }

  public static function getTotalByRawatInap($conn, $rawat_inap_id) {
    $stmt = $conn->prepare("SELECT SUM(jumlah) as total FROM uang_muka_rawat_inap WHERE rawat_inap_id = ?");
    if (!$stmt) return 0;
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
  }

  // === COUNT ALL (dengan pencarian opsional) ===
  public static function countAll($conn, string $search = ''): int {
    $sql = "SELECT COUNT(*) AS jml
            FROM uang_muka_rawat_inap u
            JOIN tiket_rawat_inap t ON t.id = u.rawat_inap_id
            JOIN pasien p ON p.id = t.pasien_id
            LEFT JOIN kasir k ON k.user_id = u.dibuat_oleh_id
            WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ? OR u.metode_pembayaran LIKE ?)";
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
    $sql = "SELECT u.*, p.nama_pasien, p.nomor_register,
                   k.nama_petugas AS nama_pembuat,
                   k.nip AS nip_pembuat,
                   t.status AS status_tiket
            FROM uang_muka_rawat_inap u
            JOIN tiket_rawat_inap t ON t.id = u.rawat_inap_id
            JOIN pasien p ON p.id = t.pasien_id
            LEFT JOIN kasir k ON k.user_id = u.dibuat_oleh_id
            WHERE 1=1";
    if (!empty($search)) {
      $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ? OR u.metode_pembayaran LIKE ?)";
    }
    $sql .= " ORDER BY u.tanggal DESC LIMIT ? OFFSET ?";

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
  public static function getAllTransaksi($conn) {
    $query = "
      SELECT u.*, p.nama_pasien, p.nomor_register,
        k.nama_petugas AS nama_pembuat,
        k.nip AS nip_pembuat,
        t.status AS status_tiket
      FROM uang_muka_rawat_inap u
      JOIN tiket_rawat_inap t ON t.id = u.rawat_inap_id
      JOIN pasien p ON p.id = t.pasien_id
      LEFT JOIN kasir k ON k.user_id = u.dibuat_oleh_id
      ORDER BY u.tanggal DESC
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
  }

  public static function getTiketStatus(mysqli $conn, int $rawat_inap_id): ?array {
    $stmt = $conn->prepare("SELECT status FROM tiket_rawat_inap WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

}