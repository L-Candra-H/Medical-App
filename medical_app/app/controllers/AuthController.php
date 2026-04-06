<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/input/KasirModel.php';
require_once __DIR__ . '/../helpers/QRCodeHelper.php';

class AuthController {
  public static function login() {
    global $conn;
    self::startSession();

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
      $_SESSION['login_error'] = "Username dan Password wajib diisi.";
      self::redirect('login');
    }

    $user = UserModel::getByUsername($username, $conn);

    if (!$user || !password_verify($password, $user['password'])) {
      $_SESSION['login_error'] = "Login gagal. Username atau password salah.";
      self::redirect('login');
    }

    $role = strtolower($user['role'] ?? '');
    $kasir = KasirModel::getByUserId($conn, $user['id']);

    // Validasi status kasir jika ada
    if ($kasir && strtolower($kasir['status']) !== 'aktif') {
      $_SESSION['login_error'] = "Login ditolak. Petugas Anda telah dinonaktifkan.";
      self::redirect('login');
    }

    // Gabungkan data user dan kasir
    $_SESSION['user'] = array_merge($user, [
      'nama_petugas' => $kasir['nama_petugas'] ?? $user['username'],
      'nip'          => $kasir['nip'] ?? '-',
      'jabatan'      => $kasir['nama_jabatan'] ?? '-'
    ]);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['last_login'] = date('Y-m-d H:i:s');
    $_SESSION['role']       = $role;
    $_SESSION['source']     = $kasir ? 'kasir' : 'admin';
    $_SESSION['nama']       = $_SESSION['user']['nama_petugas'];
    $_SESSION['nip']        = $_SESSION['user']['nip'];
    $_SESSION['jabatan']    = $_SESSION['user']['jabatan'];

    self::redirect('dashboard');
  }

  public static function register() {
    global $conn;
    self::startSession();

    $username     = trim($_POST['username'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $nama_petugas = trim($_POST['nama_petugas'] ?? '');
    $nip          = trim($_POST['nip'] ?? '');
    $jabatan_id   = $_POST['jabatan_id'] ?? null;

    if (strlen($password) < 5) {
      $_SESSION['register_error'] = "Password minimal 5 karakter.";
      self::redirect('register');
    }

    if (!preg_match('/^[A-Za-z0-9]{4,5}$/', $nip)) {
      $_SESSION['register_error'] = "NIP harus 4–5 karakter, boleh huruf dan angka.";
      self::redirect('register');
    }

    if (empty($jabatan_id)) {
      $_SESSION['register_error'] = "Jabatan wajib dipilih.";
      self::redirect('register');
    }

    $hashed   = password_hash($password, PASSWORD_DEFAULT);
    $user_id  = UserModel::createUser($username, $hashed, $conn);
    KasirModel::createKasir($user_id, $nama_petugas, $nip, $jabatan_id, $conn);

    $qr_filename = QRCodeHelper::generate($nip, $nama_petugas);

    $stmt = $conn->prepare("UPDATE kasir SET qr_filename = ? WHERE user_id = ?");
    $stmt->bind_param("si", $qr_filename, $user_id);
    $stmt->execute();

    $_SESSION['register_success'] = "Akun berhasil dibuat.";
    self::redirect('login');
  }

  public static function reset() {
    global $conn;
    self::startSession();

    $username     = trim($_POST['username'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');

    if (strlen($new_password) < 5) {
      $_SESSION['reset_error'] = "Password minimal 5 karakter.";
      self::redirect('reset_password');
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $result = UserModel::resetPassword($username, $hashed, $conn);

    $_SESSION[$result ? 'reset_success' : 'reset_error'] = $result
      ? "Password berhasil diubah."
      : "Username tidak ditemukan.";

    self::redirect($result ? 'login' : 'reset_password');
  }

  // 🔒 Helper internal
  private static function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  private static function redirect(string $page) {
    header("Location: index.php?page={$page}");
    exit;
  }
}
