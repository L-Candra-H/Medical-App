<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center">
    <h3>Daftar Transaksi Uang Muka</h3>

    <!-- 🔎 Form Cari -->
    <form method="GET" action="index.php" class="d-flex align-items-stretch me-2" id="searchForm" autocomplete="off">
      <input type="hidden" name="page" value="uang_muka">
      <input type="hidden" id="hiddenQ" name="q" value="<?= htmlspecialchars($pagination['search'] ?? '') ?>">
      <div class="input-group">
        <input type="text" id="liveSearch"
               value="<?= htmlspecialchars($pagination['search'] ?? '') ?>"
               class="form-control"
               placeholder="Cari pasien / register..."
               autocapitalize="off" autocorrect="off" spellcheck="false">
        <button type="button" id="btnCari" class="btn btn-primary">
          <i class="fas fa-search"></i> Cari
        </button>
      </div>
    </form>

    <a href="index.php?page=uang_muka&action=input" class="btn btn-success btn-sm">
      <i class="fas fa-plus"></i> Tambah Uang Muka
    </a>
  </section>
  
  <section class="content">
    <div class="card card-outline card-primary">
      <div class="card-header"><strong>Riwayat Transaksi</strong></div>
      <div class="card-body table-responsive">
        <?php if (!empty($uang_muka_list)): ?>
        <table class="table table-bordered table-striped">
          <thead class="table-light">
            <tr>
              <th>Tanggal</th>
              <th>Pasien</th>
              <th>Jumlah</th>
              <th>Metode</th>
              <th>Keterangan</th>
              <th>Petugas</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="tabelUangMuka">
            <?php foreach ($uang_muka_list as $row): 
              $id             = intval($row['id'] ?? 0);
              $tanggal        = htmlspecialchars($row['tanggal'] ?? '-');
              $nama_pasien    = htmlspecialchars($row['nama_pasien'] ?? '-');
              $nomor_register = htmlspecialchars($row['nomor_register'] ?? '-');
              $jumlah         = number_format(floatval($row['jumlah'] ?? 0), 0, ',', '.');
              $metode         = htmlspecialchars($row['metode_pembayaran'] ?? '-');
              $keterangan     = htmlspecialchars($row['keterangan'] ?? '-');
              $nama_pembuat   = htmlspecialchars($row['nama_pembuat'] ?? '-');
              $nip_pembuat    = htmlspecialchars($row['nip_pembuat'] ?? '-');
            ?>
            <tr>
              <td><?= $tanggal ?></td>
              <td><?= $nama_pasien ?> / <?= $nomor_register ?></td>
              <td>Rp <?= $jumlah ?></td>
              <td><?= $metode ?></td>
              <td><?= $keterangan ?></td>
              <td><?= $nama_pembuat ?> / <?= $nip_pembuat ?></td>
              <td class="text-center">
                <div class="btn-group" role="group">
                  <?php if (($row['status_tiket'] ?? 'AKTIF') === 'SELESAI'): ?>
                    <button class="btn btn-sm btn-secondary me-1" disabled title="Tiket sudah selesai. Tidak bisa diedit">
                      <i class="fas fa-edit"></i>
                    </button>
                  <?php else: ?>
                    <a href="index.php?page=uang_muka&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                      <i class="fas fa-edit"></i>Edit
                    </a>
                  <?php endif; ?>

                  <a href="index.php?page=uang_muka&action=cetak&id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="Cetak" target="_blank">
                    <i class="fas fa-print"></i>Cetak
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <div class="alert alert-info">Belum ada transaksi uang muka.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination -->
    <?php
    // gunakan langsung variabel $pagination dari controller
    $pagination = $pagination ?? ['page' => 1, 'totalPage' => 1, 'search' => ''];
    ?>

    <nav aria-label="Page navigation" class="mt-3">
      <ul class="pagination justify-content-center mb-0">
        <!-- Tombol Previous -->
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=uang_muka&action=admin&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>

        <!-- Nomor halaman -->
        <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
          <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="index.php?page=uang_muka&action=admin&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=uang_muka&action=admin&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      </ul>
    </nav>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>

<!-- ✅ Script Cari -->
<script>
const input   = document.getElementById('liveSearch');
const hidden  = document.getElementById('hiddenQ');
const tbody   = document.getElementById('tabelUangMuka');
const form    = document.getElementById('searchForm');
const btnCari = document.getElementById('btnCari');

// blokir submit default
form.addEventListener('submit', e => e.preventDefault());
// blokir enter di input
input.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });

// klik tombol Cari: redirect manual GET
btnCari.addEventListener('click', function(){
  const q = input.value.trim();
  hidden.value = q;
  const params = new URLSearchParams({ page: 'uang_muka', q: q || '' });
  window.location.href = 'index.php?' + params.toString();
});

// AJAX live search minimal 2 huruf
let xhr;
input.addEventListener('keyup', function(){
  const keyword = this.value.trim();
  if (keyword.length >= 2) {
    if (xhr && xhr.readyState !== 4) xhr.abort();
    xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?page=uang_muka&action=ajax_search&q=' + encodeURIComponent(keyword), true);
    xhr.onload = function(){
      if (this.status === 200) tbody.innerHTML = this.responseText;
    };
    xhr.send();
  }
});
</script>
