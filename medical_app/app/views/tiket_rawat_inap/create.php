<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1 class="mb-2">Tambah Tiket Rawat Inap</h1>
    <p class="text-muted">Isi data pasien yang akan dirawat inap. Pastikan data valid sebelum disimpan.</p>
  </section>

  <section class="content mt-3">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h5 class="card-title">Form Tiket Rawat Inap</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="index.php?page=tiket_rawat_inap&action=store">
          <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
              <?php if (isset($pasien)): ?>
                <input type="hidden" name="pasien_id" value="<?= $pasien['id'] ?>">
                <div class="mb-3">
                  <label class="form-label">Nama Pasien</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($pasien['nama_pasien']) ?>" readonly>
                </div>
              <?php else: ?>
                <div class="mb-3">
                  <label for="pasien_id" class="form-label">Pasien</label>
                  <select name="pasien_id" id="pasien_id" class="form-control" required>
                    <option value="">-- Pilih Pasien --</option>
                    <?php while ($row = $pasienTanpaTiket->fetch_assoc()): ?>
                      <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama_pasien']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              <?php endif; ?>

              <div class="mb-3">
                <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control" required>
                  <option value="">-- Pilih Status --</option>
                  <?php foreach ($statusOptions as $opt): ?>
                    <option value="<?= $opt ?>"><?= $opt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <input type="hidden" name="tanggal_keluar" value="">
            </div>

            <!-- Kolom Kanan -->
            <div class="col-md-6">
              <div class="mb-3 h-100 d-flex flex-column">
                <label for="catatan" class="form-label">Catatan</label>
                <textarea name="catatan" id="catatan" class="form-control flex-grow-1" rows="8" placeholder="Opsional..."></textarea>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <a href="index.php?page=tiket_rawat_inap" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Tiket</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<script src="<?= BASE_URL ?>/assets/js/tiket_rawat_inap.js"></script>
<?php include(__DIR__ . '/../layout/footer.php'); ?>