<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Tambah Jenis Bayar</h3>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-primary">
        <div class="card-header">
          <h5 class="card-title">Form Input Jenis Bayar</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="index.php?page=jenis_bayar&action=store">
            <div class="form-group">
              <label>Nama Jenis Bayar</label>
              <input type="text" name="nama_jenis" class="form-control" required placeholder="Contoh: BPJS, Tunai, Kredit">
            </div>

            <div class="form-group mt-3">
              <a href="index.php?page=jenis_bayar" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
              </a>
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>

<?php 
include(__DIR__ . '/../../layout/footer.php');
?>
