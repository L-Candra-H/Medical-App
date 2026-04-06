<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Tambah Data Ambulance</h3>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-primary">
        <div class="card-header">
          <h5 class="card-title">Form Ambulance</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="index.php?page=ambulance&action=store">
            <div class="form-group">
              <label for="tipe">Tipe Ambulance</label>
              <select name="tipe" id="tipe" class="form-control" required>
                <option value="">-- Pilih Tipe --</option>
                <option value="Internal">Internal</option>
                <option value="Eksternal">Eksternal</option>
              </select>
            </div>

            <div class="form-group">
              <label for="asal_ambulance">Asal Ambulance</label>
              <input type="text" name="asal_ambulance" id="asal_ambulance" class="form-control" placeholder="Masukkan asal ambulance" required>
            </div>

            <div class="form-group mt-3">
              <a href="index.php?page=ambulance" class="btn btn-secondary">
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
