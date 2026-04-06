<?php
$isDashboard = false;
$isCetak = false;

require_once $_SERVER['DOCUMENT_ROOT'] . '/medical_app/config/database.php';
require_once __DIR__ . '/../../models/RincianRawatInapModel.php';

$rincian_list = [];

// Query ambil rincian terakhir tiap rawat inap
$query = "
  SELECT 
    r.rawat_inap_id,
    r.id,
    p.nama_pasien,
    p.nomor_register,
    jb.nama_jenis AS jenis_bayar,   -- ✅ ambil nama jenis bayar dari tabel referensi
    LEAST(
      COALESCE(r.ibu_mulai, '9999-12-31'),
      COALESCE(r.anak_mulai, '9999-12-31')
    ) AS tanggal_masuk,
    COALESCE(um.total_uang_muka, 0) AS uang_muka,
    COALESCE(pot.total_potongan, 0) AS potongan,
    r.total_semua_bagian,
    r.total_semua_bagian - COALESCE(um.total_uang_muka, 0) - COALESCE(pot.total_potongan, 0) AS sisa_tagihan,
    t.status
  FROM rawat_inap_rincian r
  JOIN (
    SELECT rawat_inap_id, MAX(id) AS max_id
    FROM rawat_inap_rincian
    GROUP BY rawat_inap_id
  ) latest ON r.rawat_inap_id = latest.rawat_inap_id AND r.id = latest.max_id
  JOIN tiket_rawat_inap t ON r.rawat_inap_id = t.id
  JOIN pasien p ON t.pasien_id = p.id
  LEFT JOIN jenis_bayar jb ON r.jenis_bayar_id = jb.id   -- ✅ join tabel jenis_bayar
  LEFT JOIN (
    SELECT rawat_inap_id, SUM(jumlah) AS total_uang_muka
    FROM uang_muka_rawat_inap
    GROUP BY rawat_inap_id
  ) um ON um.rawat_inap_id = t.id
  LEFT JOIN (
    SELECT rawat_inap_id, SUM(jumlah) AS total_potongan
    FROM potongan_rawat_inap
    GROUP BY rawat_inap_id
  ) pot ON pot.rawat_inap_id = t.id
";

$stmt = $conn->prepare($query);
if ($stmt && $stmt->execute()) {
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $rincian_list[] = $row;
  }
} else {
  error_log("Query gagal: " . $conn->error);
}

include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center">
    <h3>Daftar Rincian Rawat Inap</h3>

    <!-- 🔎 Form Cari -->
    <form method="GET" action="index.php" class="d-flex align-items-stretch me-2" id="searchForm" autocomplete="off">
      <input type="hidden" name="page" value="rincian_rawat_inap">
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
  </section>

  <section class="content">
    <div class="card card-outline card-primary">
      <div class="card-header"><strong>Riwayat Rawat Inap</strong></div>
      <div class="card-body">
        <?php if (!empty($rincian_list)): ?>
        <!-- ✅ Bungkus tabel dengan table-responsive agar bisa scroll horizontal -->
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Pasien</th>
                <th>Jenis Bayar</th> <!-- ✅ kolom baru -->
                <th>Tanggal Masuk</th>
                <th>Total Biaya</th>
                <th>Total Uang Muka</th>
                <th>Potongan</th>
                <th>Sisa Biaya</th>
                <th>Status</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="tabelRincian">
              <?php foreach ($rincian_list as $no => $row): ?>
                <tr>
                  <td><?= $no + 1 ?></td>
                  <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?> / <?= htmlspecialchars($row['nomor_register'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['jenis_bayar'] ?? '-') ?></td> <!-- ✅ isi kolom -->
                  <td><?= htmlspecialchars($row['tanggal_masuk'] ?? '-') ?></td>
                  <td>Rp <?= number_format(floatval($row['total_semua_bagian'] ?? 0), 0, ',', '.') ?></td>
                  <td>Rp <?= number_format(floatval($row['uang_muka'] ?? 0), 0, ',', '.') ?></td>
                  <td>Rp <?= number_format(floatval($row['potongan'] ?? 0), 0, ',', '.') ?></td>
                  <td>Rp <?= number_format(floatval($row['sisa_tagihan'] ?? 0), 0, ',', '.') ?></td>
                  <td>
                    <span class="badge status-toggle <?= $row['status'] === 'AKTIF' ? 'bg-success' : 'bg-secondary' ?>"
                          data-id="<?= $row['rawat_inap_id'] ?>"
                          data-status="<?= $row['status'] ?>"
                          style="cursor:pointer"
                          title="Klik untuk ubah status">
                      <?= $row['status'] ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <?php if ($row['status'] === 'SELESAI'): ?>
                        <button class="btn btn-sm btn-secondary me-1" disabled title="Rawat inap sudah selesai">
                          <i class="fas fa-edit"></i>
                        </button>
                      <?php else: ?>
                        <a href="index.php?page=rincian_rawat_inap&action=edit&id=<?= intval($row['rawat_inap_id']) ?>"
                           class="btn btn-sm btn-warning me-1" title="Edit">
                          <i class="fas fa-edit"></i>
                        </a>
                      <?php endif; ?>

                      <a href="index.php?page=rincian_rawat_inap&action=cetak_kuitansi&id=<?= intval($row['rawat_inap_id']) ?>"
                         class="btn btn-sm btn-secondary me-1" title="Cetak Kuitansi" target="_blank">
                        <i class="fas fa-file-invoice"></i>
                      </a>

                      <a href="index.php?page=rincian_rawat_inap&action=cetak_rincian&id=<?= intval($row['rawat_inap_id']) ?>"
                         class="btn btn-sm btn-info" title="Cetak Rincian" target="_blank">
                        <i class="fas fa-print"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="alert alert-info">Belum ada rincian rawat inap.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination -->
    <?php $pagination = $pagination ?? ['page'=>1,'totalPage'=>1,'search'=>'']; ?>
    <nav aria-label="Page navigation" class="mt-3">
      <ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=rincian_rawat_inap&hal=<?= max(1, $pagination['page'] - 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
        <?php for ($p = 1; $p <= $pagination['totalPage']; $p++): ?>
          <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="index.php?page=rincian_rawat_inap&hal=<?= $p ?>&q=<?= urlencode($pagination['search']) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['page'] >= $pagination['totalPage'] ? 'disabled' : '' ?>">
          <a class="page-link"
             href="index.php?page=rincian_rawat_inap&hal=<?= min($pagination['totalPage'], $pagination['page'] + 1) ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      </ul>
    </nav>
  </section>
</div>

<script>
// ✅ Toggle status rawat inap
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.status-toggle').forEach(el => {
    el.style.cursor = 'pointer';
    el.addEventListener('click', function () {
      const id = this.dataset.id;
      const current = this.dataset.status;
      const next = current === 'AKTIF' ? 'SELESAI' : 'AKTIF';

      fetch(`index.php?page=rincian_rawat_inap&action=selesaikan_ajax&id=${id}`, {
        method: 'POST'
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Gagal menyelesaikan rawat inap');
        }
      })
      .catch(err => {
        console.error('Fetch error:', err);
        alert('Terjadi kesalahan jaringan');
      });
    });
  });
});

// ✅ Script Cari
const input   = document.getElementById('liveSearch');
const hidden  = document.getElementById('hiddenQ');
const tbody   = document.getElementById('tabelRincian');
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
  const params = new URLSearchParams({ page: 'rincian_rawat_inap', action: 'listAll', q: q || '' });
  window.location.href = 'index.php?' + params.toString();
});

// AJAX live search minimal 2 huruf
let xhr;
input.addEventListener('keyup', function(){
  const keyword = this.value.trim();
  if (keyword.length >= 2) {
    if (xhr && xhr.readyState !== 4) xhr.abort();
    xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?page=rincian_rawat_inap&action=ajax_search&q=' + encodeURIComponent(keyword), true);
    xhr.onload = function(){
      if (this.status === 200) tbody.innerHTML = this.responseText;
    };
    xhr.send();
  }
});
</script>

<?php include(__DIR__ . '/../layout/footer.php'); ?>