<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Helper format rupiah
function formatRupiah($angka) {
  return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Ambil nama dokter aktif
$namaDokterAktif = '';
foreach ($list_dokter as $dokter) {
  if ($dokter['id'] == ($_GET['dokter_id'] ?? '')) {
    $namaDokterAktif = $dokter['nama_dokter'];
    break;
  }
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1 class="mb-2">Rekap HR Dokter</h1>
  </section>

  <section class="content">
    <!-- Filter Form -->
    <form method="GET" action="index.php" class="form-inline mb-3">
      <input type="hidden" name="page" value="rekap_dokter">

      <!-- Dokter -->
      <div class="form-group mr-3">
        <label for="dokter_id" class="mr-2">Nama Dokter:</label>
        <select name="dokter_id" id="dokter_id" class="form-control">
          <option value="">Pilih Dokter</option>
          <?php foreach ($list_dokter as $dokter): ?>
            <option value="<?= $dokter['id'] ?>" <?= $dokter['id'] == ($_GET['dokter_id'] ?? '') ? 'selected' : '' ?>>
              <?= htmlspecialchars($dokter['nama_dokter']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Bulan -->
      <div class="form-group mr-3">
        <label for="bulan" class="mr-2">Bulan:</label>
        <select name="bulan" id="bulan" class="form-control">
          <option value="">Pilih Bulan</option>
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= $i == ($_GET['bulan'] ?? '') ? 'selected' : '' ?>>
              <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>

      <!-- Tahun -->
      <div class="form-group mr-3">
        <label for="tahun" class="mr-2">Tahun:</label>
        <select name="tahun" id="tahun" class="form-control">
          <option value="">Pilih Tahun</option>
          <?php
            $currentYear = date('Y');
            for ($y = $currentYear - 1; $y <= $currentYear + 3; $y++):
          ?>
            <option value="<?= $y ?>" <?= $y == ($_GET['tahun'] ?? '') ? 'selected' : '' ?>>
              <?= $y ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <?php if (!empty($rekap)): ?>
      <!-- Ringkasan -->
      <div class="mb-3">
        <strong>Dokter:</strong> <?= htmlspecialchars($namaDokterAktif) ?> |
        <strong>Periode:</strong> <?= date('F', mktime(0, 0, 0, $_GET['bulan'], 1)) ?> <?= $_GET['tahun'] ?>
        <?php if (!empty($rekap)): ?>
          <?php
          $urlCetak = 'index.php?page=cetak_rekap_dokter'
            . '&dokter_id=' . trim(urlencode($_GET['dokter_id']))
            . '&bulan=' . trim(urlencode($_GET['bulan']))
            . '&tahun=' . trim(urlencode($_GET['tahun']))
            . '&nama_dokter=' . trim(urlencode($namaDokterAktif));
          ?>

          <a href="<?= $urlCetak ?>" target="_blank" class="btn btn-success btn-sm float-right">
            <i class="fa fa-print"></i> Cetak Rekap
          </a>
        <?php endif; ?>
      </div>

      <!-- Tabel Rekap -->
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
          <thead class="bg-light">
            <tr>
              <th>No</th>
              <th>Nama Pasien</th>
              <th>Tgl Masuk</th>
              <th>Tgl Pulang</th>
              <th>Jenis Bayar</th>
              <th>Visite</th>
              <th>Tindakan</th>
              <th>Konsultasi</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rekap as $i => $row): ?>
              <?php
                $tglMasuk = $row['ibu_mulai'] ?? $row['anak_mulai'] ?? '-';
                $tglPulang = $row['ibu_selesai'] ?? $row['anak_selesai'] ?? '-';
                $jenisBayar = $row['nama_jenis_bayar'] ?? '-';
                $visite = (float) ($row['visite'] ?? 0);
                $tindakan = (float) ($row['tindakan'] ?? 0);
                $konsultasi = (float) ($row['konsultasi'] ?? 0);
                $total = $visite + $tindakan + $konsultasi;
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                <td><?= $tglMasuk ?></td>
                <td><?= $tglPulang ?></td>
                <td><?= $jenisBayar ?></td>
                <td><?= formatRupiah($visite) ?></td>
                <td><?= formatRupiah($tindakan) ?></td>
                <td><?= formatRupiah($konsultasi) ?></td>
                <td><strong><?= formatRupiah($total) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-warning mt-4">
        Silakan pilih filter dan tekan <strong>"Tampilkan"</strong> untuk melihat data.
      </div>
    <?php endif; ?>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>