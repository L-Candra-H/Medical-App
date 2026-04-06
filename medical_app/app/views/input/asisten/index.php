<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Data Asisten Dokter</h1>
  </section>

  <section class="content">
    <!-- Tombol tambah asisten -->
    <a href="index.php?page=asisten&action=create" class="btn btn-primary mb-3">
      <i class="fas fa-plus-circle"></i> Tambah Asisten
    </a>

    <!-- Form pencarian -->
    <form method="get" action="index.php" class="mb-3 d-flex" id="searchForm">
      <input type="hidden" name="page" value="asisten">
      <input type="text" id="searchInput" name="q"
             value="<?= htmlspecialchars($pagination['search']) ?>"
             class="form-control me-2" placeholder="Cari asisten...">
      <button type="submit" class="btn btn-secondary">Cari</button>
    </form>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>Keterangan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tabelAsisten">
        <?php $no = $offset + 1; ?>
        <?php foreach ($data['asisten'] as $row): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_asisten'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
          <?php $status = $row['status'] ?? 'Aktif'; ?>
          <td>
            <span class="badge <?= strtolower($status) === 'aktif' ? 'badge-success' : 'badge-secondary' ?>">
              <?= ucfirst($status) ?>
            </span>
          </td>
          <td>
            <a href="index.php?page=asisten&action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
              <i class="fas fa-edit"></i>Edit
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($data['asisten'])): ?>
        <tr><td colspan="5" class="text-center text-muted">Belum ada data asisten</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination SELALU MUNCUL -->
    <?php $pagination = $data['pagination']; ?>
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <!-- Tombol Previous -->
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=asisten&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>

        <!-- Nomor halaman -->
        <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
          <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="index.php?page=asisten&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=asisten&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      </ul>
    </nav>

  </section>
</div>

<?php 
include(__DIR__ . '/../../layout/footer.php');
?>

<!-- ✅ AJAX Live Search minimal 2 huruf -->
<script>
const input = document.getElementById('searchInput');
const tbody = document.getElementById('tabelAsisten');

let xhr;
input.addEventListener('keyup', function() {
  const keyword = this.value.trim();
  if (keyword.length >= 2) {
    if (xhr && xhr.readyState !== 4) xhr.abort();
    xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?page=asisten&action=ajax_search&q=' + encodeURIComponent(keyword), true);
    xhr.onload = function () {
      if (this.status === 200) {
        tbody.innerHTML = this.responseText; // response harus hanya <tr>
      }
    };
    xhr.send();
  }
});
</script>