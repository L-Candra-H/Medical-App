<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Ambil data jurnal berdasarkan ID
$id = $_GET['id'] ?? '';
include_once(__DIR__ . '/../../models/laporan/JurnalModel.php');
$model = new JurnalModel($conn);
$jurnal = $model->getById($id);

// Validasi hak edit
$kasir_id = $_SESSION['user_id'] ?? '';
if (!$jurnal || $jurnal['kasir_id'] != $kasir_id) {
  echo "<div class='alert alert-danger'>Data tidak ditemukan atau Anda tidak berhak mengedit.</div>";
  include(__DIR__ . '/../layout/footer.php');
  exit;
}

// Helper
function formatRupiah($angka) {
  return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1 class="mb-2">EDIT JURNAL HARIAN</h1>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=update_jurnal" class="form-inline mb-4">
      <input type="hidden" name="id" value="<?= $jurnal['id'] ?>">
      <input type="hidden" name="tanggal" value="<?= htmlspecialchars($jurnal['tanggal']) ?>">
      <input type="hidden" name="shift_id" value="<?= htmlspecialchars($jurnal['shift_id']) ?>">
      <input type="hidden" name="kasir_id" value="<?= htmlspecialchars($jurnal['kasir_id']) ?>">

      <div class="form-group mr-3">
        <label for="shift_display" class="mr-2">Shift:</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($jurnal['shift_nama'] ?? '-') ?>" disabled>
      </div>

      <div class="form-group mr-3">
        <label for="tanggal_display" class="mr-2">Tanggal:</label>
        <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($jurnal['tanggal'])) ?>" disabled>
      </div>

      <div class="form-group mr-3">
        <label for="petugas_display" class="mr-2">Petugas:</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($jurnal['nama_petugas']) ?>" disabled>
      </div>
    </form>

    <form method="POST" action="index.php?page=update_jurnal" class="mb-4">
      <input type="hidden" name="id" value="<?= $jurnal['id'] ?>">
      <input type="hidden" name="tanggal" value="<?= htmlspecialchars($jurnal['tanggal']) ?>">
      <input type="hidden" name="shift_id" value="<?= htmlspecialchars($jurnal['shift_id']) ?>">
      <input type="hidden" name="kasir_id" value="<?= htmlspecialchars($jurnal['kasir_id']) ?>">

      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="keterangan">Keterangan:</label>
          <input type="text" name="keterangan" id="keterangan" class="form-control" value="<?= htmlspecialchars($jurnal['keterangan']) ?>" required>
        </div>
        <div class="form-group col-md-3">
          <label for="debet">Debet:</label>
          <input type="number" name="debet" id="debet" class="form-control text-right"
                 step="1" value="<?= (int) $jurnal['debet'] ?>">
        </div>

        <div class="form-group col-md-3">
          <label for="kredit">Kredit:</label>
          <input type="number" name="kredit" id="kredit" class="form-control text-right"
                 step="1" value="<?= (int) $jurnal['kredit'] ?>">
        </div>

      </div>

      <button type="submit" class="btn btn-success">Update Jurnal</button>
      <a href="index.php?page=jurnal_input&tanggal=<?= $jurnal['tanggal'] ?>&shift_id=<?= $jurnal['shift_id'] ?>" class="btn btn-secondary">Batal</a>
    </form>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>