<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');

// Validasi 
$role = strtolower($_SESSION['role'] ?? '');
$canManageJabatan = ($role === 'superadmin');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Master Jabatan</h3>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-info">
        <div class="card-header">
          <h5 class="card-title">Tabel Jabatan</h5>
        </div>

        <div class="card-body">
          <?php if ($canManageJabatan): ?>
            <a href="index.php?page=jabatan&action=create" class="btn btn-primary mb-3">
              <i class="fas fa-plus"></i> Tambah Jabatan
            </a>
          <?php endif; ?>

          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Jabatan</th>
                <th>Keterangan</th>
                <?php if ($canManageJabatan): ?>
                  <th>Aksi</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if ($data->num_rows > 0): ?>
                <?php while ($row = $data->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                    <?php if ($canManageJabatan): ?>
                      <td>
                        <a href="index.php?page=jabatan&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                          <i class="fas fa-edit"></i>Edit
                        </a>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="<?= $canManageJabatan ? 4 : 3 ?>" class="text-center">Tidak ada data jabatan.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include(__DIR__ . '/../../layout/footer.php'); ?>
