<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Landing Rawat Inap</title>
  <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
  <div class="container">
    <h1>👋 Selamat Datang di <strong>Sistem Rawat Inap</strong></h1>

    <a href="index.php?page=auth&action=login" class="login-button login-primary">🔐 Klik Untuk Login</a>

    <div class="disclaimer">
      <span>⚠️ CATATAN PENTING :</span>
      <p>
        Sistem ini untuk membantu pengelolaan biaya pasien rawat inap.<br><br>
        Segala penyebaran data di luar sistem merupakan tanggung jawab pengguna.<br><br>
        Pembuat sistem tidak bertanggung jawab atas penyalahgunaan data.
      </p>
      <div class="pdf-btn-container">
        <button onclick="openModal()" class="login-button login-pdf">📘 Panduan Aplikasi</button>
      </div>

      <!-- Tambahan versi aplikasi di bawah tombol Panduan -->
        <div style="color:blue; font-weight:bold; font-size:0.8em; margin-top:10px; text-align:center;">
          © <?= date('Y') ?> @Medical App - versi : 2.0
        </div>

    </div>
  </div>

  <div id="pdfModal">
    <div class="modal-content">
      <h3>📘 Panduan Penggunaan Aplikasi Medical App</h3>
      <iframe src="assets/docs/panduan.pdf"></iframe>
      <button onclick="closeModal()" class="close-btn">❌ Tutup</button>
    </div>
  </div>

  <script src="assets/js/landing.js"></script>
</body>
</html>
