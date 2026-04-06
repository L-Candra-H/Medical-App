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
      <h2 class="kuitansi-title">KUITANSI RINCIAN PASIEN</h2>

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
          <?php
            $kelas = $data['nama_kelas_perawatan'] ?? '-';
            $mulai = $data['ibu_mulai'] ?? $data['anak_mulai'] ?? null;
            $selesai = $data['ibu_selesai'] ?? $data['anak_selesai'] ?? null;

            $tglMulai = $mulai ? date('d/m/Y', strtotime($mulai)) : '-';
            $tglSelesai = $selesai ? date('d/m/Y', strtotime($selesai)) : '-';
          ?>
          <td><strong>Untuk Pembayaran</strong></td>
          <td> :
            <div style="margin-left:10px;">
              Rawat Inap di <strong><?= htmlspecialchars($kelas) ?></strong><br>
              Dari Tanggal <strong><?= $tglMulai ?></strong> Sampai Dengan Tanggal <strong><?= $tglSelesai ?></strong>
            </div>
          </td>
        </tr>
      </table>

      <!-- Jumlah & Footer sejajar horizontal -->
      <div class="align-footer">
        <div class="jumlah-info">
          Jumlah : Rp. <?= number_format($data['total_semua_bagian'] ?? 0, 0, ',', '.') ?>
        </div>

        <div class="footer-block">
          <p><strong>Malang, <?= !empty($data['tanggal_pemeriksaan']) ? date('d-m-Y', strtotime($data['tanggal_pemeriksaan'])) : '-' ?></strong></p>

          <?php
            $jenisBayar = strtoupper(trim($data['jenis_bayar'] ?? ''));
            if ($jenisBayar === 'BPJS'):
          ?>
            <div style="height: 80px;"></div> <!-- Ruang kosong untuk tanda tangan basah -->
          <?php else: ?>
            <img src="<?= htmlspecialchars($data['qr_path']) ?>" class="qr-kuitansi" alt="QR Code" />
          <?php endif; ?>

          <p><strong><?= htmlspecialchars($data['nama_petugas'] ?? '-') ?></strong></p>
          <p class="nip-info">NIP: <?= htmlspecialchars($data['nip'] ?? '-') ?></p>
        </div>
      </div> <!-- align-footer -->

      <!-- Garis Pemisah -->
      <div class="garis-pemisah"></div>
    </div> <!-- kuitansi -->
  <?php endfor; ?>
</div> <!-- kuitansi-wrapper -->

<?php include(__DIR__ . '/../layout/footer.php'); ?>