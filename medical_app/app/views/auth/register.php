<?php
// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Load model Jabatan
require_once(__DIR__ . '/../../../app/models/master/JabatanModel.php');

// Ambil daftar jabatan
try {
  $daftarJabatan = JabatanModel::getAll(); // ✅ tanpa $conn
} catch (Exception $e) {
  die('Gagal mengambil data jabatan: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register - MedicalApp</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- AdminLTE & Font Awesome -->
  <link rel="stylesheet" href="assets/adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/adminlte/css/adminlte.min.css">
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- SweetAlert -->
  <script src="assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="auth-header text-center">
      <img src="assets/icons/auth-icon.png" alt="Register Icon" class="auth-header-icon">
    </div>
    <div class="login-logo"><b>Medical App</b></div>
    <div class="card">
      <div class="card-body login-card-body">
        <h4 class="auth-title text-center">REGISTER</h4>
        <p class="login-box-msg">📝 Buat Akun Baru</p>

        <form method="POST" action="index.php?page=auth&action=register" onsubmit="return validatePassword()">
          <!-- Username -->
          <div class="input-group mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-user"></span></div>
            </div>
          </div>

          <!-- Password -->
          <div class="input-group mb-3">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required minlength="5">
            <div class="input-group-append">
              <div class="input-group-text" onclick="togglePassword('password')">
                <i class="fas fa-eye-slash" id="toggle-password"></i>
              </div>
            </div>
          </div>

          <!-- Konfirmasi Password -->
          <div class="input-group mb-3">
            <input type="password" id="confirm_password" class="form-control" placeholder="Ulangi Password" required minlength="5">
            <div class="input-group-append">
              <div class="input-group-text" onclick="togglePassword('confirm_password')">
                <i class="fas fa-eye-slash" id="toggle-confirm"></i>
              </div>
            </div>
          </div>

          <!-- Nama Petugas -->
          <div class="input-group mb-3">
            <input type="text" name="nama_petugas" class="form-control" placeholder="Nama Petugas" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-id-badge"></span></div>
            </div>
          </div>

          <!-- NIP -->
          <div class="input-group mb-3">
            <input type="text" name="nip" class="form-control" placeholder="NIP (4-5 karakter alfanumerik)" required pattern="[A-Za-z0-9]{4,5}">
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-barcode"></span></div>
            </div>
          </div>

          <!-- Jabatan -->
          <div class="input-group mb-3">
            <select name="jabatan_id" class="form-control" required>
              <option value="">Pilih Jabatan</option>
              <?php foreach ($daftarJabatan as $j): ?>
                <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jabatan']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-briefcase"></span></div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="mt-3 text-center">
          <a href="index.php?page=login">← Kembali ke Login</a>
        </p>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="assets/adminlte/plugins/jquery/jquery.min.js"></script>
  <script src="assets/adminlte/js/adminlte.min.js"></script>

  <!-- Toggle Password Script -->
  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      const icon = document.getElementById('toggle-' + id);
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      }
    }

    function validatePassword() {
      const pass = document.getElementById("password").value;
      const confirm = document.getElementById("confirm_password").value;
      if (pass !== confirm) {
        Swal.fire({
          icon: 'error',
          title: 'Password Tidak Cocok',
          text: 'Mohon pastikan kedua password sama.'
        });
        return false;
      }
      return true;
    }
  </script>
</body>
</html>
