<?php
class InstitusiModel {
  public static function getAll($conn) {
    $query = "
      SELECT i.*, j.nama_jenis
      FROM institusi i
      JOIN jenis_institusi j ON i.jenis_id = j.id
      ORDER BY i.nama_institusi ASC
    ";
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function find($id, $conn) {
    $stmt = $conn->prepare("
      SELECT i.*, j.nama_jenis
      FROM institusi i
      JOIN jenis_institusi j ON i.jenis_id = j.id
      WHERE i.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function create($data, $conn) {
    $stmt = $conn->prepare("INSERT INTO institusi (
      nama_institusi,
      sub_institusi,
      jenis_id,
      alamat,
      telepon,
      email,
      logo,
      status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
      "ssisssss",
      $data['nama_institusi'],
      $data['sub_institusi'],
      $data['jenis_id'],
      $data['alamat'],
      $data['telepon'],
      $data['email'],
      $data['logo'],
      $data['status']
    );

    return $stmt->execute();
  }

  public static function update($id, $data, $conn) {
    $stmt = $conn->prepare("UPDATE institusi SET
      nama_institusi = ?,
      sub_institusi = ?,
      jenis_id = ?,
      alamat = ?,
      telepon = ?,
      email = ?,
      logo = ?,
      status = ?
      WHERE id = ?");

    $stmt->bind_param(
      "ssisssssi",
      $data['nama_institusi'],
      $data['sub_institusi'],
      $data['jenis_id'],
      $data['alamat'],
      $data['telepon'],
      $data['email'],
      $data['logo'],
      $data['status'],
      $id
    );

    return $stmt->execute();
  }

  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM institusi WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  public static function getJenisList($conn) {
    $result = $conn->query("SELECT id, nama_jenis FROM jenis_institusi ORDER BY nama_jenis ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function isJenisValid($jenis_id, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM jenis_institusi WHERE id = ?");
    $stmt->bind_param("i", $jenis_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0] > 0;
  }

  public static function getInstitusi($conn) {
    $stmt = $conn->prepare("SELECT nama_institusi, sub_institusi, alamat, telepon, logo FROM institusi WHERE status = 'AKTIF' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_assoc() : [];

    // Tambahkan path logo kalau perlu
    if (!empty($data['logo'])) {
      $data['logo_path'] = '/assets/img/' . $data['logo'];
    }

    return $data;
  }
}
