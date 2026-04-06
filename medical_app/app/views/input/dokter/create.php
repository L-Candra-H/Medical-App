<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h3 class="mb-2">Tambah Dokter</h3>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=dokter&action=store">
      <div class="form-group">
        <label for="nama_dokter">Nama Dokter</label>
        <input type="text" name="nama_dokter" id="nama_dokter" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="spesialisasi">Spesialisasi</label>
        <input type="text" name="spesialisasi" id="spesialisasi" class="form-control" required>
      </div>
      <div class="form-group mt-3">
        <a href="index.php?page=dokter" class="btn btn-secondary">
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
