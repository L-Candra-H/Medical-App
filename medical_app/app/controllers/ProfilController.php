<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

class Profil {
  public static function index($conn) {
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
      echo "<p class='text-danger'>User belum login.</p>";
      return;
    }

    // Ambil data user, kasir, dan jabatan
    $stmt = $conn->prepare("
      SELECT 
        u.username, u.role, 
        k.nama_petugas, k.nip, k.status, k.qr_filename, 
        j.nama_jabatan
      FROM user u
      LEFT JOIN kasir k ON u.id = k.user_id
      LEFT JOIN jabatan j ON k.jabatan_id = j.id
      WHERE u.id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $profil = $stmt->get_result()->fetch_assoc() ?? [];

    // Tambahkan path QR ke array profil
    if (!empty($profil['qr_filename'])) {
      $profil['qr_path'] = '/medical_app/public/qrcode/' . $profil['qr_filename'];
    }

    include __DIR__ . '/../views/profil/index.php';
  }

  public static function update($conn) {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
      echo "<p class='text-danger'>User belum login.</p>";
      return;
    }

    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($new_password && $new_password === $confirm_password) {
      $hashed = password_hash($new_password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
      $stmt->bind_param("si", $hashed, $userId);
      $stmt->execute();

      $_SESSION['success'] = "✅ Password berhasil diperbarui.";
    } else {
      $_SESSION['error'] = "❌ Konfirmasi password tidak cocok.";
    }

    header("Location: index.php?page=profil");
    exit;
  }
}
