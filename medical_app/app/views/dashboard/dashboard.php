<?php
$isDashboard = true;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

$nama = $_SESSION['nama'] ?? ($_SESSION['user']['username'] ?? 'Pengguna');
$role = strtoupper($_SESSION['user']['role'] ?? 'User');
$isSuperadmin = ($_SESSION['user']['role'] ?? '') === 'admin';

// Sapaan dinamis berdasarkan jam
$jam = (int)date("H");
if ($jam < 11) {
  $sapaan = "Selamat Pagi";
} elseif ($jam < 15) {
  $sapaan = "Selamat Siang";
} elseif ($jam < 18) {
  $sapaan = "Selamat Sore";
} else {
  $sapaan = "Selamat Malam";
}
?>
<?php
require_once __DIR__ . '/../../../config/database.php';

// ✅ Jumlah kasir aktif
$kasirAktif = 0;
$resultKasir = $conn->query("SELECT COUNT(*) AS total FROM kasir WHERE status = 'AKTIF'");
if ($resultKasir) {
  $kasirAktif = $resultKasir->fetch_assoc()['total'];
}

// ✅ Jumlah pasien terdaftar
$jumlahPasien = 0;
$resultPasien = $conn->query("SELECT COUNT(*) AS total FROM pasien");
if ($resultPasien) {
  $jumlahPasien = $resultPasien->fetch_assoc()['total'];
}

// ✅ Tiket rawat inap aktif
$tiketAktif = 0;
$resultTiket = $conn->query("SELECT COUNT(*) AS total FROM tiket_rawat_inap WHERE status = 'AKTIF'");
if ($resultTiket) {
  $tiketAktif = $resultTiket->fetch_assoc()['total'];
}
?>

<div class="content-wrapper dashboard-background text-dark">
  <section class="content-header">
    <div class="container-fluid">
      <!-- Greeting Dinamis -->
      <div class="card greeting-transparent shadow-none border-0 mb-3 fade-up">
        <div class="card-body">
          <h4 class="mb-1 text-white"><?= $sapaan ?>, <strong><?= htmlspecialchars($nama) ?></strong>! 🎉</h4>
          <p class="mb-1 text-white">
            Anda login sebagai 
            <span class="badge <?= $isSuperadmin ? 'badge-dark' : 'badge-primary' ?>">
              <?= $role ?>
            </span>
          </p>
          <p id="clock" style="font-size: 1.2rem;" class="text-white">⏰ Jam: --:--:--</p>
          <small class="text-light">Semoga hari Anda penuh semangat dan produktivitas 💪</small>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">

        <!-- Card Kasir -->
        <div class="col-lg-3 col-6 fade-up">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $kasirAktif ?></h3>
              <p>Petugas Kasir Aktif</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-cog"></i>
            </div>
          </div>
        </div>

      <!-- Card Jumlah Pasien -->
        <div class="col-lg-3 col-6 fade-up">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= $jumlahPasien ?></h3>
              <p>Jumlah Pasien Terdaftar</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-injured"></i>
            </div>
          </div>
        </div>

        <!-- Card Rawat Inap Aktif -->
        <div class="col-lg-3 col-6 fade-up">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $tiketAktif ?></h3>
              <p>Tiket Rawat Inap Aktif</p>
            </div>
            <div class="icon">
              <i class="fas fa-procedures"></i>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </section>
</div>

<!-- ⏰ Jam Realtime -->
<script>
  function updateClock() {
    const now = new Date();
    const jam = now.getHours().toString().padStart(2, '0');
    const menit = now.getMinutes().toString().padStart(2, '0');
    const detik = now.getSeconds().toString().padStart(2, '0');
    document.getElementById("clock").textContent = `⏰ Jam: ${jam}:${menit}:${detik}`;
  }
  setInterval(updateClock, 1000);
  updateClock();
</script>

<?php include(__DIR__ . '/../layout/footer.php'); ?>
