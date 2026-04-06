<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Daftar Tiket Rawat Inap</h3>

      <!-- Kolom Cari -->
      <form method="GET" action="index.php" class="d-flex align-items-stretch" id="searchForm" autocomplete="off">
        <input type="hidden" name="page" value="tiket_rawat_inap">
        <!-- hidden q untuk submit manual -->
        <input type="hidden" id="hiddenQ" name="q" value="<?= htmlspecialchars($data['pagination']['search'] ?? '') ?>">
        <div class="input-group">
          <!-- input AJAX tanpa name -->
          <input type="text" id="liveSearch"
                 value="<?= htmlspecialchars($data['pagination']['search'] ?? '') ?>"
                 class="form-control"
                 placeholder="Cari register / pasien..."
                 autocapitalize="off" autocorrect="off" spellcheck="false">
          <!-- tombol Cari manual -->
          <button type="button" id="btnCari" class="btn btn-primary">
            <i class="fas fa-search"></i> Cari
          </button>
        </div>
      </form>
    </div>

    <a href="index.php?page=tiket_rawat_inap&action=create" class="btn btn-primary mt-2">
      <i class="fas fa-plus-circle"></i> Tambah Tiket Rawat Inap
    </a>
    <p class="text-muted mt-1">Tiket ini berisi data awal rawat inap. Klik aksi untuk mengelola tiket.</p>
  </section>

  <section class="content mt-3">
    <div class="card card-outline card-secondary">
      <div class="card-header">
        <h5 class="card-title">Histori Tiket Rawat Inap</h5>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-center mb-0">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Register</th>
              <th>Pasien</th>
              <th>Tanggal Masuk</th>
              <th>Status</th>
              <th>Catatan</th>
              <th>Dibuat Oleh</th>
              <th>Dibuat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="tabelTiket">
            <?php foreach ($list ?? [] as $i => $row): ?>
              <?php
                $register     = htmlspecialchars($row['nomor_register'] ?? '-');
                $namaPasien   = htmlspecialchars($row['nama_pasien'] ?? 'Pasien tidak ditemukan');
                $tanggalMasuk = !empty($row['tanggal_masuk']) ? date('d/m/Y', strtotime($row['tanggal_masuk'])) : '-';

                $statusRaw    = $row['status'] ?? null;
                $status       = $statusRaw ? strtoupper($statusRaw) : 'UNKNOWN';
                $badgeClass   = match ($status) {
                  'AKTIF'     => 'success',
                  'SELESAI'   => 'secondary',
                  'MENUNGGU'  => 'warning',
                  'BATAL'     => 'danger',
                  default     => 'dark'
                };

                $catatan      = htmlspecialchars($row['catatan'] ?? '-');
                $dibuatOleh   = htmlspecialchars($row['dibuat_oleh'] ?? '-');
                $tanggalInput = $row['dibuat_pada'] ?? $row['tanggal_input'] ?? null;
                $dibuatPada   = $tanggalInput ? date('d/m/Y H:i', strtotime($tanggalInput)) : '<span class="text-muted">-</span>';

                $rincianCount = $row['rincian_count'] ?? 0;
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $register ?></td>
                <td><?= $namaPasien ?></td>
                <td><?= $tanggalMasuk ?></td>
                <td><span class="badge bg-<?= $badgeClass ?>"><?= $status ?></span></td>
                <td><?= $catatan ?></td>
                <td><?= $dibuatOleh ?></td>
                <td><?= $dibuatPada ?></td>
                <td>
                  <?php if ($row['status'] !== 'SELESAI'): ?>
                    <a href="index.php?page=tiket_rawat_inap&action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-1">
                      <i class="fas fa-edit"></i> Edit Tiket
                    </a>
                  <?php else: ?>
                    <button class="btn btn-sm btn-secondary me-1" disabled title="Tiket sudah selesai">
                      <i class="fas fa-edit"></i> Edit Tiket
                    </button>
                  <?php endif; ?>

                  <?php if ($rincianCount == 0 && $status !== 'SELESAI'): ?>
                    <a href="index.php?page=rincian_rawat_inap&action=create&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                      Rincian
                    </a>
                  <?php else: ?>
                    <button class="btn btn-sm btn-secondary" disabled>
                      Rincian
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($list)): ?>
              <tr><td colspan="9" class="text-muted text-center">Belum ada tiket rawat inap</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <?php $pagination = $data['pagination']; ?>
    <nav aria-label="Page navigation" class="mt-3">
      <ul class="pagination justify-content-center mb-0">
        <!-- Tombol Previous -->
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=tiket_rawat_inap&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>

        <!-- Nomor halaman -->
        <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
          <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="index.php?page=tiket_rawat_inap&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=tiket_rawat_inap&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      </ul>
    </nav>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>

<!-- ✅ Script Cari ala Pasien -->
<script>
const input   = document.getElementById('liveSearch');
const hidden  = document.getElementById('hiddenQ');
const tbody   = document.getElementById('tabelTiket');
const form    = document.getElementById('searchForm');
const btnCari = document.getElementById('btnCari');

// Blokir submit default
form.addEventListener('submit', e => e.preventDefault());
// Blokir Enter di input
input.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });

// Klik tombol Cari: redirect manual GET
btnCari.addEventListener('click', function(){
  const q = input.value.trim();
  hidden.value = q;
  const params = new URLSearchParams({ page: 'tiket_rawat_inap', q: q || '' });
  window.location.href = 'index.php?' + params.toString();
});

// AJAX live search minimal 2 huruf
let xhr;
input.addEventListener('keyup', function(){
  const keyword = this.value.trim();
  if (keyword.length >= 2) {
    if (xhr && xhr.readyState !== 4) xhr.abort();
    xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?page=tiket_rawat_inap&action=ajax_search&q=' + encodeURIComponent(keyword), true);
    xhr.onload = function(){
      if (this.status === 200) {
        tbody.innerHTML = this.responseText; // response hanya <tr>
      }
    };
    xhr.send();
  }
});
</script>