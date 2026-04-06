<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['reset_error'])) {
    echo "<script>Swal.fire({
      icon:'error',
      title:'Reset Gagal',
      text:'{$_SESSION['reset_error']}'
    });</script>";
    unset($_SESSION['reset_error']);
}

if (isset($_SESSION['reset_success'])) {
    echo "<script>Swal.fire({
      icon:'success',
      title:'Password Diubah',
      text:'" . addslashes($_SESSION['reset_success']) . "'
    });</script>";
    unset($_SESSION['reset_success']);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - MedicalApp</title>
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
    <!-- HEADER Ikon di atas logo -->
    <div class="auth-header text-center">
      <img src="assets/icons/auth-icon.png" alt="Reset Icon" class="auth-header-icon">
    </div>

    <div class="login-logo">
      <b>Medical App</b>
    </div>

    <div class="card">
      <div class="card-body login-card-body">
        <h4 class="auth-title text-center">RESET PASSWORD</h4>
        <p class="login-box-msg">🔁 Silakan ubah kata sandi Anda</p>

        <form method="POST" action="index.php?page=auth&action=reset" onsubmit="return validateReset()">
          <!-- Username -->
          <div class="input-group mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-user"></span></div>
            </div>
          </div>

          <!-- Password Baru -->
          <div class="input-group mb-3">
            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Password Baru (min 5 karakter)" required minlength="5">
            <div class="input-group-append">
              <div class="input-group-text" onclick="togglePassword('new_password')">
                <i class="fas fa-eye-slash" id="toggle-new_password"></i>
              </div>
            </div>
          </div>

          <!-- Konfirmasi Password -->
          <div class="input-group mb-3">
            <input type="password" id="confirm_password" class="form-control" placeholder="Ulangi Password Baru" required minlength="5">
            <div class="input-group-append">
              <div class="input-group-text" onclick="togglePassword('confirm_password')">
                <i class="fas fa-eye-slash" id="toggle-confirm_password"></i>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-warning btn-block">Reset</button>
        </form>

        <!-- LINK Default -->
        <p class="mt-3 text-center">
          <a href="index.php?page=login">← Kembali ke Login</a>
        </p>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="assets/adminlte/plugins/jquery/jquery.min.js"></script>
  <script src="assets/adminlte/js/adminlte.min.js"></script>

  <!-- Toggle & Validasi Password -->
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

    function validateReset() {
      const pass = document.getElementById("new_password").value;
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
