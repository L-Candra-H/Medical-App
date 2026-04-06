<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h3 class="mb-2">Edit Data Pasien</h3>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=pasien&action=update&id=<?= $data['id'] ?>">
      <div class="form-group">
        <label for="nomor_register">Nomor Register</label>
        <input type="text" name="nomor_register" id="nomor_register" class="form-control" value="<?= $data['nomor_register'] ?>" required>
      </div>
      <div class="form-group">
        <label for="nama_pasien">Nama Pasien</label>
        <input type="text" name="nama_pasien" id="nama_pasien" class="form-control" value="<?= $data['nama_pasien'] ?>" required>
      </div>
      <div class="form-group mt-3">
        <a href="index.php?page=pasien" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="btn btn-warning">
          <i class="fas fa-edit"></i> Update
        </button>
      </div>
    </form>
  </section>
</div>

<?php 
include(__DIR__ . '/../../layout/footer.php');
?>
