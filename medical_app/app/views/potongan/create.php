<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// Ambil data session
$nama_session = $_SESSION['nama'] ?? '';
$kasir_nip = '-';

// Ambil NIP kasir jika tersedia
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

// Cek apakah potongan sudah pernah diinput
$sudah_ada = false;
if (!empty($pasien)) {
  $sudah_ada = PotonganModel::existsForRawatInap($conn, $pasien['rawat_inap_id']);
}
?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center mb-2">
    <h3>Input Potongan</h3>
    <a href="index.php?page=potongan&action=listAll" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </section>

  <section class="content">
    <div class="card card-outline card-info">
      <div class="card-header"><strong>Form Input Potongan Rawat Inap</strong></div>
      <div class="card-body">

        <?php if (isset($_SESSION['warning'])): ?>
          <div class="alert alert-warning">
            <?= $_SESSION['warning']; unset($_SESSION['warning']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>

        <?php if ($sudah_ada): ?>
          <div class="alert alert-warning">
            Potongan sudah pernah diinput untuk pasien ini. Jika ingin mengubah, silakan edit setelah menyimpan.
          </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=potongan&action=store">
          <div class="row mb-3">
            <div class="col-md-4">
              <?php if (!empty($pasien)): ?>
                <!-- MODE 1: Nama Pasien Otomatis -->
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
                <input type="hidden" name="rawat_inap_id" value="<?= htmlspecialchars($pasien['rawat_inap_id']) ?>">
              <?php else: ?>
                <!-- MODE 2: Pilih Tiket -->
                <div class="mb-3">
                  <label>Rawat Inap</label>
                  <select name="rawat_inap_id" class="form-control" required>
                    <option value="">-- Pilih Pasien --</option>
                    <?php foreach ($tiket_list ?? [] as $tiket): ?>
                      <option value="<?= $tiket['id'] ?>">
                        <?= htmlspecialchars($tiket['nama_pasien'] ?? '') ?> - <?= htmlspecialchars($tiket['nomor_register'] ?? '') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label>Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-4">
              <label>Jumlah (Rp)</label>
              <input type="number" name="jumlah" class="form-control" min="0" value="" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Tipe Potongan</label>
              <select name="tipe" class="form-control" required>
                <option value="">-- Pilih Tipe --</option>
                <option value="BPJS">BPJS</option>
                <option value="Yayasan">Yayasan</option>
                <option value="Subsidi">Subsidi</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>

            <div class="col-md-6">
              <label>Keterangan</label>
              <input type="text" name="keterangan" class="form-control" placeholder="Isikan untuk jadi Keterangan di Kuitansi Potongan">
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

          <input type="hidden" name="dibuat_oleh_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>">

          <div class="text-end">
            <button class="btn btn-primary">
              <i class="fas fa-plus-circle"></i> Simpan Potongan
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php include(__DIR__ . '/../layout/footer.php'); ?>
