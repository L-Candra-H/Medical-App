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
    <h1 class="mb-2">INPUT JURNAL HARIAN</h1>
  </section>

  <section class="content">
    <!-- 🔍 Filter Form -->
    <form method="GET" action="index.php" class="form-inline mb-4">
      <input type="hidden" name="page" value="jurnal_input">

      <div class="form-group mr-3">
        <label for="shift_id" class="mr-2">Shift:</label>
        <select name="shift_id" id="shift_id" class="form-control">
          <option value="">Pilih Shift</option>
          <?php foreach ($list_shift as $shift): ?>
            <option value="<?= $shift['id'] ?>" <?= $shift['id'] == $shift_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($shift['nama_shift']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group mr-3">
        <label for="tanggal" class="mr-2">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
      </div>

      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <!-- 📝 Form Input Jurnal -->
    <?php if (!empty($shift_id) && !empty($tanggal)): ?>
    <form method="POST" action="index.php?page=jurnal_input&aksi=tambah" class="mb-4">
      <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
      <input type="hidden" name="shift_id" value="<?= htmlspecialchars($shift_id) ?>">
      <input type="hidden" name="kasir_id" value="<?= htmlspecialchars($kasir_id) ?>">

      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="keterangan">Keterangan:</label>
          <input type="text" name="keterangan" id="keterangan" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
          <label for="nama_petugas">Petugas:</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($nama_petugas) ?>" disabled>
        </div>
        <div class="form-group col-md-3">
          <label for="debet">Debet:</label>
          <input type="number" name="debet" id="debet" class="form-control text-right" step="0.01">
        </div>
        <div class="form-group col-md-3">
          <label for="kredit">Kredit:</label>
          <input type="number" name="kredit" id="kredit" class="form-control text-right" step="0.01">
        </div>
      </div>

      <button type="submit" class="btn btn-success">Simpan Jurnal</button>
    </form>
    <?php endif; ?>

    <!-- 📊 Tabel Jurnal -->
    <?php if (!empty($jurnal)): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="bg-light text-center">
          <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 30%;">Keterangan</th>
            <th style="width: 15%;">Debet</th>
            <th style="width: 15%;">Kredit</th>
            <th style="width: 20%;">Petugas</th>
            <th style="border:1px solid #dee2e6;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($jurnal as $i => $row): 
            $debet = (float) $row['debet'];
            $kredit = (float) $row['kredit'];
          ?>
          <tr class="text-center">
            <td style="border:1px solid #dee2e6;"><?= $i + 1 ?></td>
            <td class="text-left" style="border:1px solid #dee2e6;"><?= htmlspecialchars($row['keterangan']) ?></td>
            <td style="border:1px solid #dee2e6;"><?= formatRupiah($debet) ?></td>
            <td style="border:1px solid #dee2e6;"><?= formatRupiah($kredit) ?></td>
            <td style="border:1px solid #dee2e6;"><?= htmlspecialchars($row['nama_petugas']) ?></td>
            <td style="border:1px solid #dee2e6;">
              <?php if ($row['kasir_id'] == $kasir_id): ?>
                <a href="index.php?page=edit_jurnal&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-edit"></i>
                </a>
              <?php else: ?>
                <span style="color: #aaa;">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <!-- Baris TOTAL -->
          <tr class="bg-light text-center font-weight-bold">
              <td colspan="2" class="text-right" style="border:1px solid #dee2e6;">Total</td>
              <td style="border:1px solid #dee2e6;"><?= formatRupiah($total['total_debet'] ?? 0) ?></td>
              <td style="border:1px solid #dee2e6;"><?= formatRupiah($total['total_kredit'] ?? 0) ?></td>
              <td style="border:1px solid #dee2e6;"></td>
              <td style="border:1px solid #dee2e6;"></td>
            </tr>
          </tr>
          <!-- Baris SALDO -->
          <tr class="bg-secondary text-white text-center font-weight-bold">
              <td colspan="6" style="border:1px solid #dee2e6;">SALDO AKHIR: <?= formatRupiah($total['saldo'] ?? 0) ?></td>
            </tr>
          </tfoot>
      </table>
    </div>
    <?php else: ?>
    <div class="alert alert-warning mt-4">
      Silakan pilih shift dan tanggal, lalu tekan <strong>"Tampilkan"</strong> untuk melihat jurnal harian.
    </div>
    <?php endif; ?>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>