<?php
$isDashboard = false;
$isCetak = true;
include(__DIR__ . '/../layout/header.php');
?>

<div class="kuitansi-wrapper">
  <?php for ($i = 0; $i < 2; $i++): ?>
    <div class="kuitansi">
      <!-- Header Institusi -->
      <div class="logo-nama">
        <img src="/medical_app/public/assets/img/logo_institusi.png" alt="Logo Institusi" />
        <div class="institusi-info">
          <p><?= htmlspecialchars($data['nama_institusi'] ?? '-') ?></p>
          <p><?= htmlspecialchars($data['sub_institusi'] ?? '-') ?></p>
          <p><?= htmlspecialchars($data['alamat'] ?? '-') ?> | Telp: <?= htmlspecialchars($data['telepon'] ?? '-') ?></p>
        </div>
      </div>

      <!-- Judul Tengah -->
      <h2 class="kuitansi-title">KUITANSI UANG MUKA</h2>

      <!-- Isi Informasi -->
      <table class="kuitansi-table">
        <tr>
          <td><strong>Sudah Terima dari</strong></td>
          <td>: <?= htmlspecialchars($data['nama_pasien'] ?? '-') ?> (<?= htmlspecialchars($data['nomor_register'] ?? '-') ?>)</td>
        </tr>
        <tr>
          <td><strong>Terbilang</strong></td>
          <td>: <?= htmlspecialchars($data['terbilang'] ?? '-') ?></td>
        </tr>
        <tr>
          <td><strong>Untuk Pembayaran</strong></td>
          <td>: <?= htmlspecialchars($data['keterangan'] ?? '-') ?></td>
        </tr>
      </table>

      <!-- Jumlah & Footer sejajar horizontal -->
      <div class="align-footer">
        <div class="jumlah-info">
          Jumlah : Rp. <?= number_format($data['jumlah'] ?? 0, 0, ',', '.') ?>
        </div>

        <div class="footer-block">
          <p><strong>Malang, <?= !empty($data['tanggal']) ? date('d-m-Y', strtotime($data['tanggal'])) : '-' ?></strong></p>
          <img src="<?= htmlspecialchars($data['qr_path'] ?? '#') ?>" class="qr-kuitansi" alt="QR Code" />
          <p><strong><?= htmlspecialchars($data['nama_petugas'] ?? '-') ?></strong></p>
          <p class="nip-info">NIP: <?= htmlspecialchars($data['nip'] ?? '-') ?></p>
        </div>
      </div>

      <!-- Garis Pemisah -->
      <div class="garis-pemisah"></div>
    </div>
  <?php endfor; ?>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>