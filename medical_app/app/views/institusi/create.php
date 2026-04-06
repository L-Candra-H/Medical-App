<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Ambil jenis institusi dari database
$jenisList = InstitusiModel::getJenisList($conn);
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Form Tambah Institusi</h3>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-primary">
        <div class="card-header">
          <h5 class="card-title">Input Data Institusi Baru</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="index.php?page=institusi&action=store" enctype="multipart/form-data">

            <!-- Nama Institusi -->
            <div class="form-group">
              <label>Nama Institusi</label>
              <input type="text" name="nama_institusi" class="form-control" required placeholder="Contoh: Rumah Sakit Umum">
            </div>

            <!-- Sub Institusi -->
            <div class="form-group">
              <label>Sub Institusi</label>
              <input type="text" name="sub_institusi" class="form-control" placeholder="Unit/Departemen">
            </div>

            <!-- Jenis Institusi -->
            <div class="form-group">
              <label>Jenis Institusi</label>
              <select name="jenis_id" class="form-control" required>
                <option value="">-- Pilih Jenis --</option>
                <?php foreach ($jenisList as $jenis): ?>
                  <option value="<?= htmlspecialchars($jenis['id']) ?>">
                    <?= htmlspecialchars($jenis['nama_jenis']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Alamat -->
            <div class="form-group">
              <label>Alamat</label>
              <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap institusi"></textarea>
            </div>

            <!-- Telepon -->
            <div class="form-group">
              <label>Telepon</label>
              <input type="text" name="telepon" class="form-control" placeholder="08xx - xxxx - xxxx">
            </div>

            <!-- Email -->
            <div class="form-group">
              <label>Email Institusi</label>
              <input type="email" name="email" class="form-control" placeholder="admin@institusi.com">
            </div>

            <!-- Logo -->
            <div class="form-group">
              <label>Upload Logo Institusi</label>
              <input type="file" name="logo" class="form-control-file" accept=".png,.jpg,.jpeg" required>
              <small class="form-text text-muted">
                Logo akan disimpan sebagai <strong>logo_institusi.png</strong> atau <strong>.jpg/.jpeg</strong> tergantung format upload.<br>
                File lama dengan nama serupa akan otomatis digantikan.
              </small>
            </div>

            <!-- Status -->
            <div class="form-group">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="AKTIF">AKTIF</option>
                <option value="NONAKTIF">NONAKTIF</option>
              </select>
            </div>

            <!-- Tombol Simpan dan Kembali -->
            <div class="form-group mt-3">
              <a href="index.php?page=institusi" class="btn btn-secondary">
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
include(__DIR__ . '/../layout/footer.php');
?>
