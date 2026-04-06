<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../../layout/header.php');
include(__DIR__ . '/../../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Data Pasien</h3>

      <!-- Tombol Cari -->
      <form method="GET" action="index.php" class="d-flex align-items-stretch" id="searchForm" autocomplete="off">
        <input type="hidden" name="page" value="pasien">
        <!-- hidden q untuk submit manual -->
        <input type="hidden" id="hiddenQ" name="q" value="<?= htmlspecialchars($data['pagination']['search'] ?? '') ?>">
        <div class="input-group">
          <!-- input AJAX tanpa name -->
          <input type="text" id="liveSearch"
                 value="<?= htmlspecialchars($data['pagination']['search'] ?? '') ?>"
                 class="form-control"
                 placeholder="Cari nama / register..."
                 autocapitalize="off" autocorrect="off" spellcheck="false">
          <!-- tombol Cari manual -->
          <button type="button" id="btnCari" class="btn btn-primary">
            <i class="fas fa-search"></i> Cari
          </button>
        </div>
      </form>
    </div>
  </section>

  <section class="content">
    <!-- Tombol tambah pasien -->
    <a href="index.php?page=pasien&action=create" class="btn btn-success mb-3">
      <i class="fas fa-plus-circle"></i> Tambah Pasien
    </a>

    <table class="table table-bordered table-striped text-center">
      <thead class="thead-light">
        <tr>
          <th>No</th>
          <th>Nomor Register</th>
          <th>Nama Pasien</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tabelPasien">
        <?php $no = $offset + 1; ?>
        <?php foreach ($data['pasien'] as $row): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nomor_register']) ?></td>
            <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
            <td class="text-end">
              <a href="index.php?page=pasien&action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-1">
                <i class="fas fa-edit"></i> Edit
              </a>

              <?php if (empty($row['rawat_inap_id'])): ?>
                <a href="index.php?page=tiket_rawat_inap&action=create&pasien_id=<?= $row['id'] ?>" class="btn btn-info btn-sm me-1">
                  <i class="fas fa-ticket-alt"></i> Tiket Inap
                </a>
              <?php else: ?>
                <a href="index.php?page=tiket_rawat_inap&action=edit&id=<?= $row['rawat_inap_id'] ?>" class="btn btn-primary btn-sm me-1">
                  <i class="fas fa-file-medical-alt"></i> Edit Tiket Inap
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($data['pasien'])): ?>
          <tr><td colspan="4" class="text-center text-muted">Belum ada data pasien</td></tr>
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
             href="index.php?page=pasien&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>

        <!-- Nomor halaman -->
        <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
          <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="index.php?page=pasien&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=pasien&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      </ul>
    </nav>
  </section>
</div>

<?php include(__DIR__ . '/../../layout/footer.php'); ?>

<!-- ✅ AJAX Live Search minimal 2 huruf, adopsi pola Pegawai -->
<script>
const input   = document.getElementById('liveSearch');
const hidden  = document.getElementById('hiddenQ');
const tbody   = document.getElementById('tabelPasien');
const form    = document.getElementById('searchForm');
const btnCari = document.getElementById('btnCari');

// Blokir submit default
form.addEventListener('submit', function(e){ e.preventDefault(); });

// Blokir Enter di input
input.addEventListener('keydown', function(e){
  if (e.key === 'Enter') e.preventDefault();
});

// Klik tombol Cari: redirect manual GET
btnCari.addEventListener('click', function(){
  const q = input.value.trim();
  hidden.value = q;
  const params = new URLSearchParams({
    page: 'pasien',
    q: q || ''
  });
  window.location.href = 'index.php?' + params.toString();
});

// AJAX live search minimal 2 huruf
let xhr;
input.addEventListener('keyup', function(){
  const keyword = this.value.trim();
  if (keyword.length >= 2) {
    if (xhr && xhr.readyState !== 4) xhr.abort();
    xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?page=pasien&action=ajax_search&q=' + encodeURIComponent(keyword), true);
    xhr.onload = function(){
      if (this.status === 200) {
        tbody.innerHTML = this.responseText;
      }
    };
    xhr.send();
  }
  // < 2 huruf: tidak ada aksi, tidak reload
});
</script>