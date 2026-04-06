<?php
$isDashboard = false;
$isCetak = false;

$role = strtolower(trim($_SESSION['role'] ?? ''));
$userId = $_SESSION['user_id'] ?? null;
$isSuperadmin = $role === 'superadmin';

// ⛔️ AJAX SEARCH: potong layout, kirim hanya <tr>
if ($_GET['action'] ?? '' === 'ajax_search') {
  $keyword = $_GET['q'] ?? '';
  $data['kasir'] = searchKasir($keyword); // fungsi pencarian

  $no = 1;
  foreach ($data['kasir'] as $row) {
    $rowStatus = strtolower(trim($row['status'] ?? 'nonaktif'));
    $btnClass  = $rowStatus === 'aktif' ? 'success' : 'secondary';
    $canEdit   = $isSuperadmin || ($userId == ($row['user_id'] ?? null));

    echo "<tr>
            <td>{$no}</td>
            <td>".htmlspecialchars($row['nama_petugas'])."</td>
            <td>".htmlspecialchars($row['nip'])."</td>
            <td>".htmlspecialchars($row['nama_jabatan'])."</td>
            <td><span class='badge badge-{$btnClass}'>".ucfirst($rowStatus)."</span></td>
            <td>".(!empty($row['qr_filename']) 
                    ? "<img src='/medical_app/public/qrcode/".htmlspecialchars($row['qr_filename'])."' width='60'>"
                    : "<span class='text-muted'>(belum tersedia)</span>")."</td>
            <td>".($canEdit 
                    ? "<a href='index.php?page=kasir&action=edit&id={$row['id']}' class='btn btn-sm btn-info'><i class='fas fa-edit'></i></a>" 
                    : "<button class='btn btn-sm btn-light text-muted' disabled><i class='fas fa-lock'></i></button>")."</td>
          </tr>";
    $no++;
  }

  if (empty($data['kasir'])) {
    echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada hasil</td></tr>";
  }

  exit; // ⛔️ hentikan render layout
}

include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Data Petugas / Kasir</h1>
  </section>

  <section class="content">
    <!-- Form pencarian -->
    <form method="get" action="index.php" id="searchForm" class="mb-3 d-flex">
      <input type="hidden" name="page" value="kasir">
      <input type="text" name="q" id="searchInput"
             value="<?= htmlspecialchars($pagination['search']) ?>"
             class="form-control me-2" placeholder="Cari petugas...">
      <button type="submit" class="btn btn-secondary">Cari</button>
    </form>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th style="width: 5%;">No</th>
          <th style="width: 25%;">Nama</th>
          <th style="width: 15%;">NIP</th>
          <th style="width: 20%;">Jabatan</th>
          <th style="width: 10%;">Status</th>
          <th style="width: 10%;">QR Code</th>
          <th style="width: 10%;">Aksi</th>
        </tr>
      </thead>
      <tbody id="tabelKasir">
        <?php $no = $offset + 1; ?>
        <?php foreach ($data['kasir'] as $row): ?>
        <?php
          $rowStatus = strtolower(trim($row['status'] ?? 'nonaktif'));
          $btnClass  = $rowStatus === 'aktif' ? 'success' : 'secondary';
          $canEdit   = $isSuperadmin || ($userId == ($row['user_id'] ?? null));
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_petugas'] ?? '') ?></td>
          <td><?= htmlspecialchars($row['nip'] ?? '') ?></td>
          <td><?= htmlspecialchars($row['nama_jabatan'] ?? '-') ?></td>
          <td>
            <?php if ($isSuperadmin): ?>
              <form method="POST" action="index.php?page=kasir&action=toggle_status" style="margin:0;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn btn-sm btn-<?= $btnClass ?>">
                  <?= ucfirst($rowStatus) ?>
                </button>
              </form>
            <?php else: ?>
              <span class="badge badge-<?= $btnClass ?>">
                <?= ucfirst($rowStatus) ?>
              </span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($row['qr_filename'])): ?>
              <img src="/medical_app/public/qrcode/<?= htmlspecialchars($row['qr_filename']) ?>" width="60" alt="QR Petugas">
            <?php else: ?>
              <span class="text-muted">(belum tersedia)</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($canEdit): ?>
              <a href="index.php?page=kasir&action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i>Edit
              </a>
            <?php else: ?>
              <button class="btn btn-sm btn-light text-muted" disabled>
                <i class="fas fa-lock"></i>
              </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($data['kasir'])): ?>
        <tr>
          <td colspan="7" class="text-center text-muted">Belum ada data petugas</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php $pagination = $data['pagination']; ?>
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=kasir&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
        <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
          <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="index.php?page=kasir&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=kasir&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      </ul>
    </nav>
  </section>
</div>

<?php include(__DIR__ . '/../../layout/footer.php'); ?>

<!-- ✅ AJAX Live Search minimal 2 huruf -->
<script>
const input = document.getElementById('searchInput');
const tbody = document.getElementById('tabelKasir');

let xhr;
input.addEventListener('keyup', function() {
  const keyword = this.value.trim();
  if (keyword.length >= 2) {
    if (xhr && xhr.readyState !== 4) xhr.abort();
    xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?page=kasir&action=ajax_search&q=' + encodeURIComponent(keyword), true);
    xhr.onload = function () {
      if (this.status === 200) {
        tbody.innerHTML = this.responseText;
      }
    };
    xhr.send();
  }
});
</script>
