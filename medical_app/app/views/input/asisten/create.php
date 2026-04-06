<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h3 class="mb-2">Tambah Asisten Dokter</h3>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=asisten&action=store">
      <div class="form-group">
        <label for="nama_asisten">Nama Asisten</label>
        <input type="text" name="nama_asisten" id="nama_asisten" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="keterangan">Keterangan</label>
        <select name="keterangan" id="keterangan" class="form-control" required>
          <option value="">-- Pilih --</option>
          <option>Asisten Bedah</option>
          <option>Asisten Kandungan</option>
          <option>Asisten Anastesi</option>
          <option>Asisten Anak</option>
          <option>Instrumen/Onlop</option>
        </select>
      </div>
      <div class="form-group mt-3">
        <a href="index.php?page=asisten" class="btn btn-secondary">
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
