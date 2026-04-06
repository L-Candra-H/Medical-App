<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

$tanggal_masuk = $tiket['tanggal_masuk'] ?? date('Y-m-d');
$statusOptions = ['AKTIF', 'SELESAI', 'BATAL'];
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1 class="mb-2">Edit Tiket Rawat Inap</h1>
    <p class="text-muted">Perbarui data tiket rawat inap. Pastikan perubahan sesuai dengan kondisi pasien.</p>
  </section>

  <section class="content mt-3">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h5 class="card-title">Form Edit Tiket</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="index.php?page=tiket_rawat_inap&action=update&id=<?= $tiket['id'] ?>">
          <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Pasien</label>
                <input type="text" class="form-control bg-light"
                       value="<?= htmlspecialchars($tiket['nomor_register'] ?? '-') ?> - <?= htmlspecialchars($tiket['nama_pasien'] ?? '-') ?>" readonly>
                <input type="hidden" name="pasien_id" value="<?= $tiket['pasien_id'] ?>">
              </div>

              <div class="mb-3">
                <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="<?= $tanggal_masuk ?>" required>
              </div>

              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control" required>
                  <?php foreach ($statusOptions as $opt): ?>
                    <option value="<?= $opt ?>" <?= $opt === ($tiket['status'] ?? '') ? 'selected' : '' ?>><?= $opt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Tanggal Keluar disembunyikan -->
              <input type="hidden" name="tanggal_keluar" value="">
            </div>

            <!-- Kolom Kanan -->
            <div class="col-md-6 d-flex flex-column justify-content-between">
              <div>
                <div class="mb-3">
                  <label for="catatan" class="form-label">Catatan</label>
                  <textarea name="catatan" id="catatan" class="form-control" rows="6"><?= htmlspecialchars($tiket['catatan'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                  <label class="form-label">Dibuat Oleh</label>
                  <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($tiket['dibuat_oleh'] ?? '-') ?>" readonly>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <a href="index.php?page=tiket_rawat_inap" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Update Tiket</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<script src="<?= BASE_URL ?>/assets/js/tiket_rawat_inap.js"></script>
<?php include(__DIR__ . '/../layout/footer.php'); ?>