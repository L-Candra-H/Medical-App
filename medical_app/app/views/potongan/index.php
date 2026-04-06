<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center">
    <h3 class="mb-2">Daftar Transaksi Potongan</h3>

    <!-- 🔎 Form Cari -->
    <form method="GET" action="index.php" class="d-flex align-items-stretch me-2" id="searchForm" autocomplete="off">
      <input type="hidden" name="page" value="potongan">
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

    <a href="index.php?page=potongan&action=create" class="btn btn-sm btn-primary">
      <i class="fas fa-plus-circle"></i> Tambah Potongan
    </a>
  </section>

  <section class="content">
    <!-- Riwayat Potongan -->
    <div class="card card-outline card-warning">
      <div class="card-header"><strong>Riwayat Potongan</strong></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover mb-0">
          <thead class="table-light text-center">
            <tr>
              <th>Tanggal</th>
              <th>Pasien</th>
              <th>Jumlah</th>
              <th>Tipe</th>
              <th>Keterangan</th>
              <th>Petugas</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="tabelPotongan">
            <?php if (!empty($potongan_list)): ?>
              <?php foreach ($potongan_list as $pot): ?>
              <tr>
                <td class="text-center"><?= $pot['tanggal'] ?></td>
                <td><?= htmlspecialchars($pot['nama_pasien'] ?? '-') ?> / <?= $pot['nomor_register'] ?? '-' ?></td>
                <td>Rp <?= number_format($pot['jumlah'], 0, ',', '.') ?></td>
                <td><?= $pot['tipe'] ?></td>
                <td><?= $pot['keterangan'] ?></td>
                <td>
                  <?php
                    $petugas = $conn->query("SELECT nama_petugas, nip FROM kasir WHERE user_id = {$pot['dibuat_oleh_id']}")->fetch_assoc();
                    echo $petugas ? "{$petugas['nama_petugas']} / {$petugas['nip']}" : '-';
                  ?>
                </td>
                <td class="text-center">
                  <div class="btn-group" role="group">
                    <?php if (($pot['status_tiket'] ?? 'AKTIF') === 'SELESAI'): ?>
                      <button class="btn btn-sm btn-secondary me-1" disabled title="Tiket sudah selesai. Tidak bisa diedit">
                        <i class="fas fa-edit"></i>
                      </button>
                    <?php else: ?>
                      <a href="index.php?page=potongan&action=edit&id=<?= $pot['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                        <i class="fas fa-edit"></i>Edit
                      </a>
                    <?php endif; ?>

                    <a href="index.php?page=potongan&action=cetak&id=<?= $pot['id'] ?>" class="btn btn-sm btn-info" title="Cetak" target="_blank">
                      <i class="fas fa-print"></i>Cetak
                    </a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">
                  Belum ada potongan untuk pasien ini
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <?php
    $pagination = $pagination ?? ['page'=>1,'totalPage'=>1,'search'=>''];
    ?>
    <nav aria-label="Page navigation" class="mt-3">
      <ul class="pagination justify-content-center mb-0">
        <!-- Tombol Previous -->
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=potongan&action=listAll&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>

        <!-- Nomor halaman -->
        <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
          <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="index.php?page=potongan&action=listAll&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=potongan&action=listAll&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
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
const tbody   = document.getElementById('tabelPotongan');
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
  const params = new URLSearchParams({ page: 'potongan', action: 'listAll', q: q || '' });
  window.location.href = 'index.php?' + params.toString();
});

// AJAX live search minimal 2 huruf
let xhr;
input.addEventListener('keyup', function(){
  const keyword = this.value.trim();
  if (keyword.length >= 2) {
    if (xhr && xhr.readyState !== 4) xhr.abort();
    xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?page=potongan&action=ajax_search&q=' + encodeURIComponent(keyword), true);
    xhr.onload = function(){
      if (this.status === 200) tbody.innerHTML = this.responseText;
    };
    xhr.send();
  }
});
</script>