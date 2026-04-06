<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');

// Ambil daftar jabatan dari model
require_once(__DIR__ . '/../../models/master/JabatanModel.php');
$daftarJabatan = JabatanModel::getAll();
?>

<div class="content-wrapper">
  <section class="content-header">
    <h3 class="mb-2">Tambah Petugas Kasir</h3>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=kasir&action=store">
      <div class="form-group">
        <label for="nama_petugas">Nama Petugas</label>
        <input type="text" name="nama_petugas" id="nama_petugas" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="nip">NIP</label>
        <input type="text" name="nip" id="nip" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="jabatan_id">Jabatan</label>
        <select name="jabatan_id" id="jabatan_id" class="form-control" required>
          <option value="">-- Pilih Jabatan --</option>
          <?php while ($j = $daftarJabatan->fetch_assoc()): ?>
            <option value="<?= $j['id'] ?>"><?= $j['nama_jabatan'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- ℹ️ Info QR akan digenerate otomatis -->
      <div class="form-text text-muted mb-3">
        <i class="fas fa-qrcode"></i> QR Code akan dibuat otomatis untuk petugas setelah disimpan.
      </div>

      <div class="form-group mt-3">
        <a href="index.php?page=kasir" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save"></i> Simpan
        </button>
      </div>
    </form>
  </section>
</div>

<?php 
include(__DIR__ . '/../../layout/footer.php');
?>
