<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
require_once(__DIR__ . "/../../models/InstitusiModel.php");

// Ambil data jenis institusi dari database
$jenisList = InstitusiModel::getJenisList($conn);
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Edit Data Institusi</h3>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-primary">
        <div class="card-header">
          <h5 class="card-title">Form Perubahan Data Institusi</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="index.php?page=institusi&action=update&id=<?= $institusi['id'] ?>" enctype="multipart/form-data">

            <!-- Nama Institusi -->
            <div class="form-group">
              <label>Nama Institusi</label>
              <input type="text" name="nama_institusi" class="form-control" required value="<?= htmlspecialchars($institusi['nama_institusi'] ?? '') ?>">
            </div>

            <!-- Sub Institusi -->
            <div class="form-group">
              <label>Sub Institusi</label>
              <input type="text" name="sub_institusi" class="form-control" value="<?= htmlspecialchars($institusi['sub_institusi'] ?? '') ?>">
            </div>

            <!-- Jenis Institusi -->
            <div class="form-group">
              <label>Jenis Institusi</label>
              <select name="jenis_id" class="form-control" required>
                <option value="">-- Pilih Jenis --</option>
                <?php foreach ($jenisList as $jenis): ?>
                  <option value="<?= $jenis['id'] ?>" <?= ($institusi['jenis_id'] ?? '') == $jenis['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($jenis['nama_jenis']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Alamat -->
            <div class="form-group">
              <label>Alamat</label>
              <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($institusi['alamat'] ?? '') ?></textarea>
            </div>

            <!-- Telepon -->
            <div class="form-group">
              <label>Telepon</label>
              <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($institusi['telepon'] ?? '') ?>">
            </div>

            <!-- Email -->
            <div class="form-group">
              <label>Email Institusi</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($institusi['email'] ?? '') ?>">
            </div>

            <!-- Logo -->
            <div class="form-group">
              <label>Logo Institusi</label><br>
              <?php
              $logoFile = $institusi['logo'] ?? '';
              $logoDir = __DIR__ . '/../../public/assets/img/';
              $logoPath = $logoDir . $logoFile;

              // Validasi file: nama tidak kosong dan file benar-benar ada
              if (empty($logoFile) || !is_file($logoPath)) {
                  $logoFile = 'logo_institusi.png';
              }
              ?>

              <img src="<?= htmlspecialchars('public/assets/img/' . $logoFile) ?>" alt="Logo Institusi" width="100" class="mb-2">
              <input type="file" name="logo" class="form-control-file" accept=".png,.jpg,.jpeg">
              <input type="hidden" name="logo_lama" value="<?= htmlspecialchars($institusi['logo'] ?? '') ?>">
              <small class="form-text text-muted">
                  Format PNG/JPG. Logo akan disimpan dengan nama tetap <strong>logo_institusi.ext</strong> dan file lama akan otomatis tergantikan.
              </small>

            <!-- Status -->
            <div class="form-group">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="AKTIF" <?= ($institusi['status'] ?? '') === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                <option value="NONAKTIF" <?= ($institusi['status'] ?? '') === 'NONAKTIF' ? 'selected' : '' ?>>NONAKTIF</option>
              </select>
            </div>

            <!-- Tombol -->
            <div class="form-group mt-3 text-end">
              <a href="index.php?page=institusi" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
              </a>
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan Perubahan
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </section>
</div>

<?php 
include(__DIR__ . '/../layout/footer.php');
?>
