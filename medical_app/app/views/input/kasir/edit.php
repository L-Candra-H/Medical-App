<?php
$isDashboard = false;
$isCetak = false;

include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');

// Ambil daftar jabatan
require_once(__DIR__ . '/../../../models/master/JabatanModel.php');
$daftarJabatan = JabatanModel::getAll() ?? [];

// Pastikan $data tersedia
$id = $data['id'] ?? null;
$namaPetugas = htmlspecialchars($data['nama_petugas'] ?? '');
$nip = htmlspecialchars($data['nip'] ?? '');
$jabatanId = $data['jabatan_id'] ?? '';
?>

<div class="content-wrapper">
  <section class="content-header">
    <h3 class="mb-2">Edit Petugas Kasir</h3>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=kasir&action=update&id=<?= $id ?>">
      <div class="form-group">
        <label for="nama_petugas">Nama Petugas</label>
        <input type="text" name="nama_petugas" class="form-control"
               value="<?= $namaPetugas ?>" required>
      </div>

      <div class="form-group">
        <label for="nip">NIP</label>
        <input type="text" name="nip" id="nip" class="form-control" value="<?= $nip ?>" required>
      </div>

      <div class="form-group">
        <label for="jabatan_id">Jabatan</label>
        <select name="jabatan_id" id="jabatan_id" class="form-control" required>
          <option value="">-- Pilih Jabatan --</option>
          <?php foreach ($daftarJabatan as $j): ?>
            <?php
              $selected = ($jabatanId == $j['id']) ? 'selected' : '';
              $namaJabatan = htmlspecialchars($j['nama_jabatan'] ?? '');
            ?>
            <option value="<?= $j['id'] ?>" <?= $selected ?>>
              <?= $namaJabatan ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" class="form-control" required>
          <option value="Aktif" <?= ($data['status'] ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="Nonaktif" <?= ($data['status'] ?? '') === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
      </div>

      <div class="form-group mt-3">
        <a href="index.php?page=kasir" class="btn btn-secondary">
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
