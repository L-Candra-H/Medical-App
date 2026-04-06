<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Edit Kelas Kamar</h3>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-warning">
        <div class="card-header">
          <h5 class="card-title">Form Edit Kelas Kamar</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="index.php?page=kelas_kamar&action=update&id=<?= $data['id'] ?>">
            <div class="form-group">
              <label>Nama Kelas</label>
              <input type="text" name="nama_kelas" class="form-control" required
                value="<?= htmlspecialchars($data['nama_kelas']) ?>">
            </div>

            <div class="form-group">
              <label>Jenis Pasien</label>
              <select name="jenis_pasien" class="form-control" required>
                <option value="">- Pilih Jenis Pasien -</option>
                <option value="dewasa" <?= $data['jenis_pasien'] === 'Dewasa' ? 'selected' : '' ?>>Dewasa (Ibu)</option>
                <option value="anak" <?= $data['jenis_pasien'] === 'Anak' ? 'selected' : '' ?>>Anak</option>
              </select>
            </div>

            <div class="form-group">
              <label>Tarif Per Hari</label>
              <input type="number" name="tarif" class="form-control" required step="0.01"
                value="<?= $data['tarif'] ?>">
            </div>

            <div class="form-group mt-4">
              <a href="index.php?page=kelas_kamar" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
              </a>
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Update
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
