<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Ambil data user dari session
$nama_pembuat = htmlspecialchars($_SESSION['user']['nama_petugas'] ?? '-');
$nip_pembuat  = htmlspecialchars($_SESSION['user']['nip'] ?? '-');
?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center">
    <h3>Input Uang Muka</h3>
    <a href="index.php?page=uang_muka&action=admin" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </section>

  <section class="content">
    <div class="card card-outline card-success">
      <div class="card-header"><strong>Form Tambah Transaksi</strong></div>
      <div class="card-body">
        <form method="POST" action="index.php?page=uang_muka&action=store" id="form-uang-muka">
          <?php if (isset($pasien)): ?>
            <input type="hidden" name="tiket_id" value="<?= intval($pasien['rawat_inap_id']) ?>">
            <div class="row mb-3">
              <div class="col-md-6">
                <label>Nama Pasien</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($pasien['nama_pasien']) ?>" readonly>
              </div>
              <div class="col-md-6">
                <label>Nomor Register</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($pasien['nomor_register']) ?>" readonly>
              </div>
            </div>
          <?php elseif (!empty($tiket_list)): ?>
            <div class="mb-3">
              <label for="tiket_id">Pasien & Tiket</label>
              <select name="tiket_id" id="tiket_id" class="form-control" required>
                <option value="">-- Pilih Pasien Bertiket Aktif --</option>
                <?php foreach ($tiket_list as $t): ?>
                  <option value="<?= intval($t['rawat_inap_id']) ?>">
                    <?= htmlspecialchars($t['nama_pasien'] ?? '-') ?> /
                    <?= htmlspecialchars($t['nomor_register'] ?? '-') ?> /
                    <?= htmlspecialchars($t['tanggal_masuk'] ?? '-') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php else: ?>
            <div class="alert alert-warning">Tidak ada pasien dengan tiket aktif. Silakan input tiket terlebih dahulu.</div>
          <?php endif; ?>

          <div class="row g-2 mb-3">
            <div class="col-md-3">
              <label for="tanggal">Tanggal Transaksi</label>
              <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3">
              <label for="jumlah">Jumlah (Rp)</label>
              <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" required placeholder="Jumlah uang muka">
            </div>
            <div class="col-md-3">
              <label for="metode_pembayaran">Metode Pembayaran</label>
              <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                <option value="">-- Pilih Metode --</option>
                <option value="Tunai">Tunai</option>
                <option value="Transfer">Transfer</option>
                <option value="QRIS">QRIS</option>
                <option value="E-Wallet">E-Wallet</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="keterangan">Keterangan</label>
              <input type="text" name="keterangan" id="keterangan" class="form-control" placeholder="Isikan untuk jadi Keterangan di Kuitansi Uang Muka">
            </div>
          </div>

          <div class="form-group mb-3">
            <label>Nama Pembuat</label>
            <input type="text" class="form-control" value="<?= $nama_pembuat ?>" readonly>
          </div>
          <div class="form-group mb-3">
            <label>NIP Pembuat</label>
            <input type="text" class="form-control" value="<?= $nip_pembuat ?>" readonly>
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-success">Simpan</button>
          </div>
        </form>

        <?php if (isset($pasien) && !empty($riwayat)): ?>
          <div class="text-end mt-3">
            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalRiwayat">
              <i class="fas fa-history"></i> Lihat Riwayat Uang Muka
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if (isset($pasien) && !empty($riwayat)): ?>
    <div class="modal fade" id="modalRiwayat" tabindex="-1" aria-labelledby="modalRiwayatLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="modalRiwayatLabel">Riwayat Uang Muka: <?= htmlspecialchars($pasien['nama_pasien']) ?></h5>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover table-bordered align-middle">
                <thead class="table-success text-center">
                  <tr>
                    <th style="width: 120px;">Tanggal</th>
                    <th style="width: 150px;">Jumlah (Rp)</th>
                    <th style="width: 120px;">Metode</th>
                    <th>Keterangan</th>
                    <th style="width: 180px;">Petugas</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($riwayat as $row): ?>
                    <tr>
                      <td class="text-center"><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                      <td class="text-end">Rp <?= number_format($row['jumlah'] ?? 0, 0, ',', '.') ?></td>
                      <td class="text-center"><?= htmlspecialchars($row['metode_pembayaran'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($row['nama_petugas'] ?? '-') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr class="table-secondary fw-bold">
                    <td class="text-center">Total</td>
                    <td class="text-end" colspan="4">
                      Rp <?= number_format(array_sum(array_column($riwayat, 'jumlah')), 0, ',', '.') ?>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>

<!-- Validasi Form -->
<script>
document.getElementById('form-uang-muka').addEventListener('submit', function(e) {
  const jumlah = parseFloat(document.getElementById('jumlah').value);
  const metode = document.getElementById('metode_pembayaran').value;
  const tiketField = document.getElementById('tiket_id');
  const tiket = tiketField ? tiketField.value : 'OK';

  if (!tiket) {
    alert("Silakan pilih pasien bertiket.");
    e.preventDefault();
    return;
  }

  if (jumlah <= 0 || isNaN(jumlah)) {
    alert("Jumlah harus lebih dari 0.");
    e.preventDefault();
    return;
  }

  if (!metode) {
    alert("Silakan pilih metode pembayaran.");
    e.preventDefault();
    return;
  }
});
</script>
