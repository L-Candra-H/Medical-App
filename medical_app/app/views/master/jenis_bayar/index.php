<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h3 class="mb-2">Master Jenis Bayar</h3>
      <!-- Tombol Tambah -->
      <a href="index.php?page=jenis_bayar&action=create" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Tambah Jenis Bayar
      </a>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <!-- Tabel Data -->
      <div class="card card-outline card-secondary">
        <div class="card-header">
          <h5 class="card-title">Data Jenis Bayar</h5>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover text-center">
            <thead class="thead-light">
              <tr>
                <th style="width: 10%;">No</th>
                <th>Nama Jenis Bayar</th>
                <th style="width: 15%;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data)): ?>
                <?php foreach ($data as $i => $row): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['nama_jenis']) ?></td>
                    <td>
                      <a href="index.php?page=jenis_bayar&action=edit&id=<?= $row['id'] ?>" 
                         class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="text-center text-muted">Belum ada data jenis bayar</td>
                </tr>
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