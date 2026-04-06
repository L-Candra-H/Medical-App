<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Master Ambulance</h3>
      <a href="index.php?page=ambulance&action=create" class="btn btn-success mb-3">
        <i class="fas fa-plus-circle"></i> Tambah Ambulance
      </a>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <div class="card card-outline card-primary">
        <div class="card-body table-responsive">
          <table class="table table-hover table-bordered">
            <thead class="thead-light text-center">
              <tr>
                <th>No</th>
                <th>Tipe Ambulance</th>
                <th>Asal Ambulance</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($data as $i => $row): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($row['tipe']) ?></td>
                <td><?= htmlspecialchars($row['asal_ambulance'] ?? '-') ?></td>
                <td class="text-center">
                  <a href="index.php?page=ambulance&action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>Edit
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($data)): ?>
              <tr><td colspan="3" class="text-center text-muted">Belum ada data ambulance</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php 
include(__DIR__ . '/../../layout/footer.php');
?>
