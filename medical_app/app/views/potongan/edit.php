<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');
?>

<?php
$nama_session = $_SESSION['nama'] ?? '';
$kasir_nip = '-';

if ($nama_session !== '') {
  $stmt = $conn->prepare("SELECT nip FROM kasir WHERE nama_petugas = ?");
  $stmt->bind_param("s", $nama_session);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $kasir_nip = $row['nip'];
  }
  $stmt->close();
}
?>

<?php if (!isset($data)) {
  echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
  return;
} ?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center mb-2">
    <h3>Edit Potongan Rawat Inap</h3>
    <a href="index.php?page=potongan&id=<?= $data['rawat_inap_id'] ?>" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </section>

  <section class="content">
    <div class="card card-outline card-info">
      <div class="card-header"><strong>Form Edit Potongan Rawat Inap</strong></div>
      <div class="card-body">
        <form method="POST" action="index.php?page=potongan&action=update&id=<?= $data['id'] ?>">
          <input type="hidden" name="rawat_inap_id" value="<?= $data['rawat_inap_id'] ?>">
          <input type="hidden" name="dibuat_oleh_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>">

          <div class="row mb-3">
            <div class="col-md-4">
              <label>Nama Pasien</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_pasien'] ?? '-') ?>" readonly>
            </div>
            <div class="col-md-4">
              <label>Nomor Register</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($data['nomor_register'] ?? '-') ?>" readonly>
            </div>
            <div class="col-md-4">
              <label>Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Jumlah (Rp)</label>
              <input type="number" name="jumlah" class="form-control" min="0" value="<?= $data['jumlah'] ?>" required>
            </div>
            <div class="col-md-6">
              <label>Tipe Potongan</label>
              <select name="tipe" class="form-control" required>
                <option value="">-- Pilih Tipe --</option>
                <option value="BPJS" <?= $data['tipe'] == 'BPJS' ? 'selected' : '' ?>>BPJS</option>
                <option value="Yayasan" <?= $data['tipe'] == 'Yayasan' ? 'selected' : '' ?>>Yayasan</option>
                <option value="Subsidi" <?= $data['tipe'] == 'Subsidi' ? 'selected' : '' ?>>Subsidi</option>
                <option value="Lainnya" <?= $data['tipe'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label>Keterangan</label>
              <input type="text" name="keterangan" class="form-control" value="<?= $data['keterangan'] ?>" placeholder="Isikan untuk jadi Keterangan di Kuitansi Potongan">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Nama Pembuat</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($nama_session) ?>" readonly>
            </div>
            <div class="col-md-6">
              <label>NIP Pembuat</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($kasir_nip) ?>" readonly>
            </div>
          </div>

          <div class="text-end">
            <button class="btn btn-info">
              <i class="fas fa-save"></i> Update Data
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php
include(__DIR__ . '/../layout/footer.php');
?>
