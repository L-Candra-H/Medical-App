<?php
$isDashboard = false;
$isCetak = true;
include(__DIR__ . '/../layout/header.php');

// Ambil parameter
$asisten_id = $_GET['asisten_id'] ?? '';
$bulan      = $_GET['bulan'] ?? '';
$tahun      = $_GET['tahun'] ?? '';
$namaAsisten = $_GET['nama_asisten'] ?? '-';

// Validasi
if (!$asisten_id || !$bulan || !$tahun) {
  echo "<p class='text-center'>Parameter tidak lengkap.</p>";
  exit;
}

// Koneksi & Model
include_once(__DIR__ . '/../../models/laporan/RekapAsistenModel.php');
$model = new RekapAsistenModel($conn);
$rekap = $model->getRekapAsisten($asisten_id, $bulan, $tahun);
$total = $model->getTotalAsisten($asisten_id, $bulan, $tahun);

// Institusi
$query  = "SELECT nama_institusi, sub_institusi, alamat, telepon FROM institusi LIMIT 1";
$result = mysqli_query($conn, $query);
$data   = mysqli_fetch_assoc($result) ?? [
  'nama_institusi' => '-', 'sub_institusi' => '-', 'alamat' => '-', 'telepon' => '-'
];

// Petugas
$namaPetugas = $rekap[0]['nama_petugas'] ?? '-';
$nipPetugas  = $rekap[0]['nip'] ?? '-';
$qrPath      = $rekap[0]['qr_path'] ?? null;

// Helper
function formatRupiah($angka) {
  return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<div class="kuitansi-wrapper">
  <div class="kuitansi rincian-mode">
    <!-- Header Institusi -->
    <div class="logo-nama">
      <img src="/medical_app/public/assets/img/logo_institusi.png" alt="Logo Institusi" />
      <div class="institusi-info">
        <p><?= htmlspecialchars($data['nama_institusi']) ?></p>
        <p><?= htmlspecialchars($data['sub_institusi']) ?></p>
        <p><?= htmlspecialchars($data['alamat']) ?> | Telp: <?= htmlspecialchars($data['telepon']) ?></p>
      </div>
    </div>

    <!-- Judul -->
    <h2 class="kuitansi-title">REKAP HONOR ASISTEN</h2>
    <p class="text-center">
      Periode: <?= date('F', mktime(0,0,0,$bulan,1)) ?> <?= $tahun ?><br>
      Asisten: <strong><?= htmlspecialchars($namaAsisten) ?></strong>
    </p>

    <div class="garis-pemisah"></div>

    <?php if (!empty($rekap)): ?>
      <table class="rincian-table" style="border-collapse: collapse; width: 100%;">
        <thead>
          <tr>
            <?php
              $headers = ['No', 'Nama Pasien', 'Tgl Masuk', 'Tgl Pulang', 'Jenis Bayar', 'Tindakan (Rp)', 'Total (Rp)'];
              foreach ($headers as $h) {
                echo "<th style='border:1px solid #000;'>$h</th>";
              }
            ?>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($rekap as $i => $row):
              $tglMasuk   = $row['ibu_mulai'] ?? $row['anak_mulai'] ?? '-';
              $tglPulang  = $row['ibu_selesai'] ?? $row['anak_selesai'] ?? '-';
              $jenisBayar = $row['nama_jenis_bayar'] ?? '-';
              $honor      = (float) ($row['honor_asisten'] ?? 0);
          ?>
            <tr>
              <td style="border:1px solid #000;"><?= $i + 1 ?></td>
              <td style="border:1px solid #000;"><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
              <td style="border:1px solid #000;"><?= $tglMasuk ?></td>
              <td style="border:1px solid #000;"><?= $tglPulang ?></td>
              <td style="border:1px solid #000;"><?= $jenisBayar ?></td>
              <td style="border:1px solid #000;"><?= formatRupiah($honor) ?></td>
              <td style="border:1px solid #000;"><strong><?= formatRupiah($honor) ?></strong></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="6" style="border:1px solid #000; text-align:right;"><strong>Total Honor</strong></td>
            <td style="border:1px solid #000;"><strong><?= formatRupiah($total) ?></strong></td>
          </tr>
        </tbody>
      </table>

      <!-- Footer Petugas -->
      <div class="align-footer">
        <div class="footer-left"></div>
        <div class="footer-right">
          <p>Malang, <?= date('d-m-Y') ?></p>
          <p>Bagian Keuangan,</p>
          <?php if (!empty($qrPath)): ?>
            <img src="<?= htmlspecialchars($qrPath) ?>" class="qr-kuitansi" alt="QR Petugas" />
          <?php endif; ?>
          <p><strong><?= htmlspecialchars($namaPetugas) ?></strong></p>
          <p class="nip-info">NIP: <strong><?= htmlspecialchars($nipPetugas) ?></strong></p>
        </div>
      </div>
    <?php else: ?>
      <p class="text-center">Tidak ada data rekap untuk periode ini.</p>
    <?php endif; ?>
  </div>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>