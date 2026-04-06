<?php
class UserModel {
  // Ambil data user berdasarkan username
  public static function getByUsername($username, $conn) {
    $username = trim($username);
    $stmt = $conn->prepare("SELECT id, username, password, role FROM user WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
  }

  // Simpan user baru dan kembalikan user_id untuk relasi ke kasir
  public static function createUser($username, $hashedPassword, $conn) {
    $username = trim($username);
    $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);
    return $stmt->execute() ? $conn->insert_id : false;
  }

  // Reset password user berdasarkan username
  public static function resetPassword($username, $hashedPassword, $conn) {
    $username = trim($username);
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);
    return $stmt->execute();
  }
}
