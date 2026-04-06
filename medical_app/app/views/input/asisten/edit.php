<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h3 class="mb-2">Edit Asisten Dokter</h3>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=asisten&action=update&id=<?= $data['id'] ?>">
      <div class="form-group">
        <label for="nama_asisten">Nama Asisten</label>
        <input type="text" name="nama_asisten" id="nama_asisten" class="form-control" value="<?= $data['nama_asisten'] ?>" required>
      </div>
      <div class="form-group">
        <label for="keterangan">Keterangan</label>
        <select name="keterangan" id="keterangan" class="form-control" required>
          <option value="">-- Pilih --</option>
          <option <?= $data['keterangan'] === 'Asisten Bedah' ? 'selected' : '' ?>>Asisten Bedah</option>
          <option <?= $data['keterangan'] === 'Asisten Kandungan' ? 'selected' : '' ?>>Asisten Kandungan</option>
          <option <?= $data['keterangan'] === 'Asisten Anastesi' ? 'selected' : '' ?>>Asisten Anastesi</option>
          <option <?= $data['keterangan'] === 'Asisten Anak' ? 'selected' : '' ?>>Asisten Anak</option>
          <option <?= $data['keterangan'] === 'Instrumen/Onlop' ? 'selected' : '' ?>>Instrumen/Onlop</option>
        </select>
      </div>
      <label for="status">Status</label>
      <select name="status" class="form-control">
        <option value="Aktif" <?= strtolower($data['status']) === 'aktif' ? 'selected' : '' ?>>Aktif</option>
        <option value="Nonaktif" <?= strtolower($data['status']) === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
      </select>
      <div class="form-group mt-3">
        <a href="index.php?page=asisten" class="btn btn-secondary">
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
