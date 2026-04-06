<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// 🔥 Jika login gagal, tampilkan alert
if (isset($_SESSION['login_error'])) {
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '" . addslashes($_SESSION['login_error']) . "'
      });
    });
  </script>";
  unset($_SESSION['login_error']);
}

// ✅ Jika register berhasil, tampilkan alert sukses
if (isset($_SESSION['register_success'])) {
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '" . addslashes($_SESSION['register_success']) . "',
        confirmButtonColor: '#3085d6'
      });
    });
  </script>";
  unset($_SESSION['register_success']);
}

// ✅ Jika reset berhasil
if (isset($_SESSION['reset_success'])) {
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Reset Berhasil',
        text: '" . addslashes($_SESSION['reset_success']) . "',
        confirmButtonColor: '#3085d6'
      });
    });
  </script>";
  unset($_SESSION['reset_success']);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login MedicalApp</title>
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
      <img src="assets/icons/auth-icon.png" alt="App Icon" class="auth-header-icon">
    </div>

    <div class="login-logo"><b>Medical App</b></div>

    <div class="card">
      <div class="card-body login-card-body">
        <h4 class="auth-title text-center">LOGIN</h4>
        <p class="login-box-msg">Silakan masuk untuk melanjutkan</p>

        <form method="POST" action="index.php?page=auth&action=login">
          <!-- Username -->
          <div class="input-group mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-user"></span></div>
            </div>
          </div>

          <!-- Password -->
          <div class="input-group mb-3">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password (min 5 karakter)" required minlength="5">
            <div class="input-group-append">
              <div class="input-group-text" onclick="togglePassword()">
                <i class="fas fa-eye-slash" id="toggle-icon"></i>
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </div>
          </div>
        </form>

        <p class="mt-3 mb-1 text-center">
          <a href="index.php?page=auth&action=register">Buat Akun</a>
          <a href="index.php?page=reset_password">Lupa Password</a>
        </p>
      </div>
    </div>
  </div>

  <!-- Tambahan versi aplikasi di paling bawah -->
  <div style="color:blue; font-weight:bold; font-size:0.8em; text-align:center; margin-top:20px;">
    © <?= date('Y') ?> @Medical App - versi : 2.0
  </div>

  <!-- Scripts -->
  <script src="assets/adminlte/plugins/jquery/jquery.min.js"></script>
  <script src="assets/adminlte/js/adminlte.min.js"></script>

  <script>
    function togglePassword() {
      const input = document.getElementById("password");
      const icon = document.getElementById("toggle-icon");
      input.type = input.type === "password" ? "text" : "password";
      icon.classList.toggle("fa-eye");
      icon.classList.toggle("fa-eye-slash");
    }
  </script>
</body>
</html>
