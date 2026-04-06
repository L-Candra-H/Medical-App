<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Edit Data Jabatan</h3>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-warning">
        <div class="card-header">
          <h5 class="card-title">Form Edit Jabatan</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="index.php?page=jabatan&action=edit&id=<?= $jabatan['id'] ?>">
            <div class="form-group">
              <label for="nama_jabatan">Nama Jabatan</label>
              <input type="text" name="nama_jabatan" id="nama_jabatan" class="form-control" value="<?= $jabatan['nama_jabatan'] ?>" required>
            </div>

            <div class="form-group">
              <label for="keterangan">Keterangan</label>
              <input type="text" name="keterangan" id="keterangan" class="form-control" value="<?= $jabatan['keterangan'] ?>">
            </div>

            <div class="form-group">
              <label for="role">Role</label>
              <select name="role" id="role" class="form-control" required>
                <option value="">-- Pilih Role --</option>
                <option value="User" <?= $jabatan['role'] === 'User' ? 'selected' : '' ?>>User</option>
                <option value="Admin" <?= $jabatan['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="Superadmin" <?= $jabatan['role'] === 'Superadmin' ? 'selected' : '' ?>>Superadmin</option>
              </select>
            </div>

            <div class="form-group mt-3">
              <a href="index.php?page=jabatan" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
              </a>
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Update
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
