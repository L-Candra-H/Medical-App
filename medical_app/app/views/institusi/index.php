<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="mb-0">Master Institusi</h3>
        <?php if (count($data) === 0): ?>
          <a href="index.php?page=institusi&action=create" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Tambah Institusi
          </a>
        <?php else: ?>
          <button class="btn btn-secondary" disabled>
            <i class="fas fa-ban"></i> Tambah Tidak Tersedia
          </button>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-outline card-primary">
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-bordered">
            <thead class="thead-light text-center">
              <tr>
                <th>Nama</th>
                <th>Sub</th>
                <th>Jenis</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Logo</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($data as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nama_institusi']) ?></td>
                <td><?= htmlspecialchars($row['sub_institusi']) ?></td>
                <td><?= $row['nama_jenis'] ?></td>
                <td><?= $row['telepon'] ?></td>
                <td><?= $row['email'] ?></td>
                <td class="text-center">
                  <?php
                    $logoPath = __DIR__ . "/../../public/assets/img/" . ($row['logo'] ?? '');
                    $logoFile = (!empty($row['logo']) && file_exists($logoPath)) ? $row['logo'] : 'logo_institusi.png';
                  ?>
                  <img src="/medical_app/public/assets/img/<?= $logoFile ?>" height="30" alt="Logo">
                </td>
                <?php $status = strtoupper(trim($row['status'] ?? '')); ?>
                <td class="text-center">
                  <span class="badge badge-<?= $status === 'AKTIF' ? 'success' : 'secondary' ?>">
                    <?= $status ?>
                  </span>
                </td>
                <td class="text-center">
                  <a href="index.php?page=institusi&action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>Edit
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php
include(__DIR__ . '/../layout/footer.php');
?>
