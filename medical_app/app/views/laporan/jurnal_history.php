<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Helper
function formatRupiah($angka) {
  return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1 class="mb-2">HISTORY JURNAL HARIAN</h1>
  </section>

  <section class="content">
    <!-- Filter Tanggal -->
    <form method="GET" action="index.php" class="form-inline mb-4">
      <input type="hidden" name="page" value="jurnal_history">

      <div class="form-group mr-3">
        <label for="tanggal" class="mr-2">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal ?? '') ?>">
      </div>

      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <!-- Tabel Saldo Per Shift -->
    <?php if (!empty($list_shift)): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
          <thead class="bg-light">
            <tr>
              <th>Shift</th>
              <th>Jam</th>
              <th>Saldo Akhir</th>
              <th>Petugas</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($list_shift as $shift): 
              $id = $shift['id'];
              $nama = $shift['nama_shift'];
              $jam = date('H:i', strtotime($shift['jam_mulai'])) . ' - ' . date('H:i', strtotime($shift['jam_selesai']));
              $saldo = isset($saldo_per_shift[$id]) ? (float) $saldo_per_shift[$id] : 0;
            ?>
              <tr>
                <td><?= htmlspecialchars($nama) ?></td>
                <td><?= $jam ?></td>
                <td><strong><?= formatRupiah($saldo) ?></strong></td>
                <td>
                  <?= !empty($petugas_per_shift[$id]) 
                        ? htmlspecialchars(implode(', ', $petugas_per_shift[$id])) 
                        : '-' ?>
                </td>
                <td class="text-center">
                  <a href="index.php?page=cetak_jurnal_harian&tanggal=<?= urlencode($tanggal) ?>&shift_id=<?= $id ?>"
                     class="btn btn-sm btn-info"
                     title="Cetak Jurnal"
                     target="_blank">
                    <i class="fas fa-print"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-warning mt-4">
        Tidak ada data shift tersedia.
      </div>
    <?php endif; ?>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>