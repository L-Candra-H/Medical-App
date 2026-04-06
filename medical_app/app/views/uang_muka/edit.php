<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Validasi data awal
if (!isset($data) || empty($data['id'])) {
  echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
  include(__DIR__ . '/../layout/footer.php');
  return;
}

// Sanitasi data
$id               = intval($data['id']);
$rawat_inap_id    = intval($data['rawat_inap_id']);
$nama_pasien      = htmlspecialchars($data['nama_pasien'] ?? '-');
$nomor_register   = htmlspecialchars($data['nomor_register'] ?? '-');
$tanggal          = htmlspecialchars($data['tanggal'] ?? date('Y-m-d'));
$jumlah           = floatval($data['jumlah'] ?? 0);
$metode           = htmlspecialchars($data['metode_pembayaran'] ?? '');
$keterangan       = htmlspecialchars($data['keterangan'] ?? '');
$nama_pembuat     = htmlspecialchars($data['nama_pembuat'] ?? '-');
$nip_pembuat      = htmlspecialchars($data['nip_pembuat'] ?? '-');
?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center">
    <h3>Edit Transaksi Uang Muka</h3>
    <a href="index.php?page=uang_muka&id=<?= $rawat_inap_id ?>" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </section>

  <section class="content">
    <div class="card card-outline card-warning">
      <div class="card-header"><strong>Form Edit Uang Muka</strong></div>
      <div class="card-body">
        <form method="POST" action="index.php?page=uang_muka&action=update&id=<?= $id ?>" id="form-edit-uang-muka">
          <input type="hidden" name="tiket_id" value="<?= $rawat_inap_id ?>">

          <div class="row mb-3">
            <div class="col-md-3">
              <label>Nama Pasien</label>
              <input type="text" class="form-control" value="<?= $nama_pasien ?>" readonly>
            </div>
            <div class="col-md-3">
              <label>Nomor Register</label>
              <input type="text" class="form-control" value="<?= $nomor_register ?>" readonly>
            </div>
            <div class="col-md-3">
              <label>Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="<?= $tanggal ?>" required>
            </div>
            <div class="col-md-3">
              <label>Jumlah (Rp)</label>
              <input type="number" name="jumlah" class="form-control" min="1" value="<?= $jumlah ?>" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Metode Pembayaran</label>
              <select name="metode_pembayaran" class="form-control" required>
                <option value="">-- Pilih Metode --</option>
                <?php
                $metodeList = ['Tunai', 'Transfer', 'QRIS', 'E-Wallet', 'Lainnya'];
                foreach ($metodeList as $m):
                  $selected = ($metode === $m) ? 'selected' : '';
                ?>
                  <option value="<?= $m ?>" <?= $selected ?>><?= $m ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label>Keterangan</label>
              <input type="text" name="keterangan" class="form-control" value="<?= $keterangan ?>" placeholder="Isikan untuk jadi Keterangan di Kuitansi Potongan">
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
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-save"></i> Update Data
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>

<script>
document.getElementById('form-edit-uang-muka').addEventListener('submit', function(e) {
  const jumlah = parseFloat(document.querySelector('[name="jumlah"]').value);
  const metode = document.querySelector('[name="metode_pembayaran"]').value;

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
