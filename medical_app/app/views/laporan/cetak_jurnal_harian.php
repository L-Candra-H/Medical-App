<?php
$isDashboard = false;
$isCetak = true;
include(__DIR__ . '/../layout/header.php');

// Ambil parameter
$tanggal  = $_GET['tanggal'] ?? '';
$shift_id = $_GET['shift_id'] ?? '';

// Validasi
if (!$tanggal || !$shift_id) {
  echo "<p class='text-center'>Parameter tidak lengkap.</p>";
  exit;
}

// Ambil data institusi
$query  = "SELECT nama_institusi, sub_institusi, alamat, telepon FROM institusi LIMIT 1";
$result = mysqli_query($conn, $query);
$data   = mysqli_fetch_assoc($result) ?? [
  'nama_institusi' => '-', 'sub_institusi' => '-', 'alamat' => '-', 'telepon' => '-'
];

// Ambil data jurnal
include_once(__DIR__ . '/../../models/laporan/JurnalModel.php');
$model         = new JurnalModel($conn);
$jurnal        = $model->getByTanggalShift($tanggal, $shift_id);
$total         = $model->getTotalByTanggalShift($tanggal, $shift_id);
$listShift     = $model->getAllShift();
$petugas_list  = $model->getPetugasDetailByTanggalShift($tanggal, $shift_id);

// Ambil nama shift
$shiftNama = '-';
foreach ($listShift as $s) {
  if ($s['id'] == $shift_id) {
    $shiftNama = $s['nama_shift'];
    break;
  }
}

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
    <h2 class="kuitansi-title">JURNAL HARIAN</h2>
    <p class="text-center">
      Tanggal: <?= date('d-m-Y', strtotime($tanggal)) ?><br>
      Shift: <strong><?= htmlspecialchars($shiftNama) ?></strong>
    </p>

    <div class="garis-pemisah"></div>

    <?php if (!empty($jurnal)): ?>
      <table class="rincian-table" style="border-collapse: collapse; width: 100%;">
        <thead>
          <tr>
            <th style="border:1px solid #000;">No</th>
            <th style="border:1px solid #000;">Keterangan</th>
            <th style="border:1px solid #000;">Debet</th>
            <th style="border:1px solid #000;">Kredit</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($jurnal as $i => $row): ?>
            <tr>
              <td style="border:1px solid #000;"><?= $i + 1 ?></td>
              <td style="border:1px solid #000;"><?= htmlspecialchars($row['keterangan']) ?></td>
              <td style="border:1px solid #000;"><?= formatRupiah($row['debet']) ?></td>
              <td style="border:1px solid #000;"><?= formatRupiah($row['kredit']) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="2" style="border:1px solid #000; text-align:right;"><strong>Total</strong></td>
            <td style="border:1px solid #000;"><strong><?= formatRupiah($total['total_debet'] ?? 0) ?></strong></td>
            <td style="border:1px solid #000;"><strong><?= formatRupiah($total['total_kredit'] ?? 0) ?></strong></td>
          </tr>
          <tr>
            <td colspan="4" style="border:1px solid #000; text-align: right;">
              <strong>Saldo Akhir: <?= formatRupiah($total['saldo'] ?? 0) ?></strong>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Tanggal & Bagian Keuangan -->
      <div style="margin-top: 50px; text-align: center;">
        <p>Malang, <?= date('d-m-Y', strtotime($tanggal)) ?></p>
        <p>Bagian Keuangan</p>
      </div>

      <!-- QR & Petugas -->
      <div style="margin-top: 30px; display: flex; gap: 40px; justify-content: center; flex-wrap: wrap;">
        <?php foreach ($petugas_list as $p): ?>
          <?php
            $filename = $p['qr_filename'] ?? '';
            $qrPath = $filename !== ''
              ? "/medical_app/public/qrcode/" . $filename
              : null;
          ?>
          <div style="text-align: center; margin-bottom: 20px;">
            <?php if ($qrPath): ?>
              <img src="<?= htmlspecialchars($qrPath) ?>" class="qr-kuitansi" alt="QR Petugas" /><br>
            <?php else: ?>
              <div class="qr-kuitansi" style="width:100px; height:100px; border:1px dashed #aaa; display:flex; align-items:center; justify-content:center;">
                <span style="font-size:10px;">QR tidak tersedia</span>
              </div>
            <?php endif; ?>
            <strong><?= htmlspecialchars($p['nama_petugas'] ?? '-') ?></strong><br>
            NIP: <?= htmlspecialchars($p['nip'] ?? '-') ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center">Tidak ada data jurnal untuk tanggal dan shift ini.</p>
    <?php endif; ?>
  </div>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>