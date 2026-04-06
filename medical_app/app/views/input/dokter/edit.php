<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h3 class="mb-2">Edit Dokter</h3>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=dokter&action=update&id=<?= $data['id'] ?>">
      <div class="form-group">
        <label for="nama_dokter">Nama Dokter</label>
        <input type="text" name="nama_dokter" id="nama_dokter" class="form-control" value="<?= $data['nama_dokter'] ?>" required>
      </div>
      <div class="form-group">
        <label for="spesialisasi">Spesialisasi</label>
        <input type="text" name="spesialisasi" id="spesialisasi" class="form-control" value="<?= $data['spesialisasi'] ?>" required>
      </div>
      <label for="status">Status</label>
      <select name="status" id="status" class="form-control">
        <option value="Aktif" <?= strtolower($data['status']) === 'aktif' ? 'selected' : '' ?>>Aktif</option>
        <option value="Nonaktif" <?= strtolower($data['status']) === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
      </select>
      <div class="form-group mt-3">
        <a href="index.php?page=dokter" class="btn btn-secondary">
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
