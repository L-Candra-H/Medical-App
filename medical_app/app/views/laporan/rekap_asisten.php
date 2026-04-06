<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Helper format rupiah
function formatRupiah($angka) {
  return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Ambil nama asisten aktif
$namaAsistenAktif = '';
foreach ($list_asisten as $asisten) {
  if ($asisten['id'] == ($_GET['asisten_id'] ?? '')) {
    $namaAsistenAktif = $asisten['nama_asisten'];
    break;
  }
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1 class="mb-2">Rekap HR Asisten</h1>
  </section>

  <section class="content">
    <!-- Filter Form -->
    <form method="GET" action="index.php" class="form-inline mb-3">
      <input type="hidden" name="page" value="rekap_asisten">

      <!-- Asisten -->
      <div class="form-group mr-3">
        <label for="asisten_id" class="mr-2">Nama Asisten:</label>
        <select name="asisten_id" id="asisten_id" class="form-control">
          <option value="">Pilih Asisten</option>
          <?php foreach ($list_asisten as $asisten): ?>
            <option value="<?= $asisten['id'] ?>" <?= $asisten['id'] == ($_GET['asisten_id'] ?? '') ? 'selected' : '' ?>>
              <?= htmlspecialchars($asisten['nama_asisten']) ?>
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
        <strong>Asisten:</strong> <?= htmlspecialchars($namaAsistenAktif) ?> |
        <strong>Periode:</strong> <?= date('F', mktime(0, 0, 0, $_GET['bulan'], 1)) ?> <?= $_GET['tahun'] ?>
        <?php
          $urlCetak = 'index.php?page=cetak_rekap_asisten'
            . '&asisten_id=' . trim(urlencode($_GET['asisten_id']))
            . '&bulan=' . trim(urlencode($_GET['bulan']))
            . '&tahun=' . trim(urlencode($_GET['tahun']))
            . '&nama_asisten=' . trim(urlencode($namaAsistenAktif));
        ?>
        <a href="<?= $urlCetak ?>" target="_blank" class="btn btn-success btn-sm float-right">
          <i class="fa fa-print"></i> Cetak Rekap
        </a>
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
              <th>Tindakan</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rekap as $i => $row): ?>
              <?php
                $tglMasuk   = $row['ibu_mulai'] ?? $row['anak_mulai'] ?? '-';
                $tglPulang  = $row['ibu_selesai'] ?? $row['anak_selesai'] ?? '-';
                $jenisBayar = $row['nama_jenis_bayar'] ?? '-';
                $tindakan   = (float) ($row['honor_asisten'] ?? 0);
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                <td><?= $tglMasuk ?></td>
                <td><?= $tglPulang ?></td>
                <td><?= $jenisBayar ?></td>
                <td><?= formatRupiah($tindakan) ?></td>
                <td><strong><?= formatRupiah($tindakan) ?></strong></td>
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