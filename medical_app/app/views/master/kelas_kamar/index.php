<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Master Kelas Kamar</h3>
      <a href="index.php?page=kelas_kamar&action=create" class="btn btn-success mb-3">
        <i class="fas fa-plus-circle"></i> Tambah Kelas
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
          <table class="table table-bordered table-hover">
            <thead class="thead-light text-center">
              <tr>
                <th>No</th>
                <th>Nama Kelas</th>
                <th>Jenis Pasien</th>
                <th>Tarif / Hari</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = $data['offset'] + 1; ?>
              <?php foreach ($data['kelas'] as $row): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                <td class="text-center">
                  <?= strtolower($row['jenis_pasien']) === 'dewasa' ? 'Dewasa (Ibu)' : 'Anak' ?>
                </td>
                <td class="text-end">Rp <?= number_format($row['tarif'], 0, ',', '.') ?></td>
                <td class="text-center">
                  <a href="index.php?page=kelas_kamar&action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>Edit
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($data['kelas'])): ?>
              <tr><td colspan="5" class="text-center text-muted">Belum ada data kelas kamar</td></tr>
              <?php endif; ?>
            </tbody>
          </table>

          <!-- Pagination ala Asisten -->
          <?php $pagination = $data['pagination']; ?>
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-3">
              <!-- Tombol Prev -->
              <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="index.php?page=kelas_kamar&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
                  « Prev
                </a>
              </li>

              <!-- Nomor Halaman -->
              <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
                <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
                  <a class="page-link"
                     href="index.php?page=kelas_kamar&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
                    <?= $p ?>
                  </a>
                </li>
              <?php endfor; ?>

              <!-- Tombol Next -->
              <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="index.php?page=kelas_kamar&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
                  Next »
                </a>
              </li>
            </ul>
          </nav>

        </div>
      </div>
    </div>
  </section>
</div>

<?php
include(__DIR__ . '/../../layout/footer.php');
?>