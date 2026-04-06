<?php
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

// ✅ Validasi status SELESAI
require_once __DIR__ . '/../../../app/models/RincianRawatInapModel.php';
$rawat_inap_id = intval($data_pasien['rawat_inap_id'] ?? 0);

if ($tiketModel->isSelesai($rawat_inap_id)) {
  echo '<div class="alert alert-warning mx-3 mt-3">Rawat inap ini sudah selesai. Tidak bisa buat rincian baru.</div>';
  return;
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Rekening Rawat Inap</h1>
  </section>

  <section class="content">
    <form method="POST" action="index.php?page=rincian_rawat_inap&action=store&id=<?= intval($data_pasien['rawat_inap_id'] ?? 0) ?>">
    <input type="hidden" name="rawat_inap_id" value="<?= intval($data_pasien['rawat_inap_id']) ?>">
      <div class="card card-info">
        <div class="card-header"><strong>Data Pasien</strong></div>
        <div class="card-body">

          <?php if (!empty($data_pasien)): ?>
            <!-- Mode otomatis: pasien sudah dipilih -->
            <input type="hidden" name="pasien_id" value="<?= intval($data_pasien['pasien_id'] ?? $data_pasien['id'] ?? 0) ?>">
            <input type="hidden" name="rawat_inap_id" value="<?= intval($data_pasien['rawat_inap_id'] ?? 0) ?>">
            <input type="hidden" name="nama_pasien" value="<?= htmlspecialchars($data_pasien['nama_pasien'] ?? '') ?>">
            <input type="hidden" name="nomor_register" value="<?= htmlspecialchars($data_pasien['nomor_register'] ?? '') ?>">

            <div class="row mb-3">
              <div class="col-md-6">
                <label>Nama Pasien</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($data_pasien['nama_pasien'] ?? '') ?>" readonly>
              </div>
              <div class="col-md-6">
                <label>Nomor Register</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($data_pasien['nomor_register'] ?? '') ?>" readonly>
              </div>
            </div>

          <?php elseif (!empty($list_pasien_bertiket)): ?>
            <!-- Mode manual: pilih pasien dari dropdown -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label>Pilih Pasien</label>
                <select name="pasien_id" id="pasien-select" class="form-control" required>
                  <option value="">-- Pilih Pasien --</option>
                  <?php foreach ($list_pasien_bertiket as $pasien): ?>
                    <option value="<?= intval($pasien['id']) ?>"
                            data-register="<?= htmlspecialchars($pasien['nomor_register']) ?>">
                      <?= htmlspecialchars($pasien['nama_pasien']) ?> (<?= htmlspecialchars($pasien['nomor_register']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label>Nomor Register</label>
                <input type="text" id="nomor-register" class="form-control" readonly>
              </div>
            </div>

            <script>
              document.getElementById('pasien-select').addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                const nomorRegister = selected.getAttribute('data-register') || '';
                document.getElementById('nomor-register').value = nomorRegister;
              });
            </script>

          <?php else: ?>
            <!-- Tidak ada data -->
            <div class="alert alert-warning">
              Data pasien tidak ditemukan dan tidak ada tiket aktif. Silakan buat tiket rawat inap terlebih dahulu.
            </div>
          <?php endif; ?>

          <!-- Hidden fields for rawat_inap_id and tanggal_masuk -->
          <input type="hidden" name="rawat_inap_id" value="<?= $data_pasien['rawat_inap_id'] ?? $data_pasien['id'] ?? '' ?>">
          <input type="hidden" name="tanggal_masuk" value="<?= $data_pasien['tanggal_masuk'] ?? '' ?>">

          <div class="form-row mt-3">
            <div class="col-md-4">
              <label>Jenis Bayar</label>
              <select name="jenis_bayar_id" id="jenis_bayar_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($jenis_bayar_list as $jb): ?>
                  <option value="<?= $jb['id'] ?>"><?= $jb['nama_jenis'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label>Jenis Rincian</label>
              <select name="jenis_rincian" id="jenis_rincian" class="form-control" required disabled>
                <option value="">-- Pilih --</option>
                <option value="Sendiri" <?= ($data_pasien['jenis_rincian'] ?? '') === 'Sendiri' ? 'selected' : '' ?>>Sendiri</option>
                <option value="Gabung" <?= ($data_pasien['jenis_rincian'] ?? '') === 'Gabung' ? 'selected' : '' ?>>Gabung (Ibu + Bayi)</option>
              </select>
            </div>

            <div class="col-md-4">
              <label>Kelas Perawatan</label>
              <select name="kelas_kamar_id" id="kelas_kamar_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($kelas_kamar_list as $kk): ?>
                  <option value="<?= $kk['id'] ?>" data-jenis="<?= strtolower($kk['jenis_pasien']) ?>">
                    <?= $kk['nama_kelas']?> - <?=$kk['jenis_pasien'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

<div class="card card-outline card-primary mt-3">
  <div class="card-header"><strong>1. KAMAR / AKOMODASI</strong></div>
  <div class="card-body">

    <!-- A. KAMAR PERAWATAN -->
    <h5>A. Kamar Perawatan</h5>

    <div class="form-row mb-2">
      <!-- Perawatan Ibu -->
      <div class="col-md-6">
        <label>1. Perawatan Ibu</label>
        <div class="form-row">
          <div class="col-md-4">
            <input type="date" name="ibu_mulai" id="ibu_mulai" class="form-control mb-1" placeholder="Mulai">
          </div>
          <div class="col-md-4">
            <input type="date" name="ibu_selesai" id="ibu_selesai" class="form-control mb-1" placeholder="Sampai">
          </div>
          <div class="col-md-4">
            <input type="number" name="ibu_hari" id="ibu_hari" class="form-control mb-1" placeholder="Lama Hari" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="col-md-6">
            <input type="number" name="ibu_tarif" id="ibu_tarif" class="form-control mb-1" value="<?= $preset['ibu_tarif'] ?? 0 ?>" placeholder="Biaya / Hari" readonly>
          </div>
          <div class="col-md-6">
            <input type="number" name="ibu_total" id="ibu_total" class="form-control mb-1" placeholder="Total" readonly>
          </div>
        </div>
      </div>

      <!-- Perawatan Bayi -->
      <div class="col-md-6">
        <label>2. Perawatan Bayi</label>
        <div class="form-row">
          <div class="col-md-4">
            <input type="date" name="bayi_mulai" id="bayi_mulai" class="form-control mb-1" placeholder="Mulai">
          </div>
          <div class="col-md-4">
            <input type="date" name="bayi_selesai" id="bayi_selesai" class="form-control mb-1" placeholder="Sampai">
          </div>
          <div class="col-md-4">
            <input type="number" name="bayi_hari" id="bayi_hari" class="form-control mb-1" placeholder="Lama Hari" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="col-md-6">
            <input type="number" name="bayi_tarif" id="bayi_tarif" class="form-control mb-1" value="<?= $preset['bayi_tarif'] ?? 0 ?>" placeholder="Biaya / Hari">
          </div>
          <div class="col-md-6">
            <input type="number" name="bayi_total" id="bayi_total" class="form-control mb-1" placeholder="Total" readonly>
          </div>
        </div>
      </div>
    </div>

  <div class="form-row mb-2">
    <!-- Perawatan Anak -->
    <div class="col-md-6">
      <label>3. Perawatan Anak</label>
      <div class="form-row">
        <div class="col-md-4">
          <input type="date" name="anak_mulai" id="anak_mulai" class="form-control mb-1" placeholder="Mulai">
        </div>
        <div class="col-md-4">
          <input type="date" name="anak_selesai" id="anak_selesai" class="form-control mb-1" placeholder="Sampai">
        </div>
        <div class="col-md-4">
          <input type="number" name="anak_hari" id="anak_hari" class="form-control mb-1" placeholder="Lama Hari" readonly>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md-6">
          <input type="number" name="anak_tarif" id="anak_tarif" class="form-control mb-1" value="<?= $preset['anak_tarif'] ?? 0 ?>" placeholder="Biaya / Hari" readonly>
        </div>
        <div class="col-md-6">
          <input type="number" name="anak_total" id="anak_total" class="form-control mb-1" placeholder="Total" readonly>
        </div>
      </div>
    </div>
  </div>

  <div class="form-row mt-2">
    <div class="col-md-4 offset-md-8">
      <label>Total Kamar Perawatan</label>
      <input type="number" name="total_kamar_perawatan" id="total_kamar_perawatan" class="form-control" readonly>
    </div>
  </div>

    <!-- B. KAMAR BERSALIN -->
    <h5 class="mt-4">B. Kamar Bersalin</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="total_bersalin" id="total_bersalin" class="form-control" placeholder="Total Kamar Bersalin">
      </div>
    </div>

    <!-- C. AMBULANCE -->
    <h5 class="mt-4">C. Ambulance</h5>
    <div class="form-row">
      <div class="col-md-6">
        <select name="ambulance_1_id" class="form-control mb-1">
          <option value="">-- Ambulance 1 --</option>
          <?php foreach ($ambulance_list as $a): ?>
            <option value="<?= $a['id'] ?>"><?= $a['asal_ambulance'] ?></option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="jumlah_ambulance_1" id="jumlah_ambulance_1" class="form-control mb-2" placeholder="Jumlah">
      </div>
      <div class="col-md-6">
        <select name="ambulance_2_id" class="form-control mb-1">
          <option value="">-- Ambulance 2 --</option>
          <?php foreach ($ambulance_list as $a): ?>
            <option value="<?= $a['id'] ?>"><?= $a['asal_ambulance'] ?></option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="jumlah_ambulance_2" id="jumlah_ambulance_2" class="form-control mb-2" placeholder="Jumlah">
      </div>
    </div>
    <div class="form-row mb-2">
      <div class="col-md-4 offset-md-8">
        <label>Total Ambulance</label>
        <input type="number" name="total_ambulance" id="total_ambulance" class="form-control" readonly>
      </div>
    </div>

    <!-- D. JASA RS -->
    <h5 class="mt-4">D. Jasa Rumah Sakit</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="total_jasa_rs" id="total_jasa_rs" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- E. KARCIS -->
    <h5 class="mt-4">E. Karcis</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="total_karcis" id="total_karcis" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- F. MATERAI -->
    <h5 class="mt-4">F. Materai</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="total_materai" id="total_materai" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- TOTAL Bagian 1 -->
    <h4 class="mt-4 text-center">TOTAL KAMAR / AKOMODASI</h4>
    <div class="form-row justify-content-center mb-2">
      <div class="col-md-4">
        <input type="number" name="total_kamar_akomodasi" id="total_kamar_akomodasi" class="form-control text-center" placeholder="Jumlah Total Bagian 1" readonly>
      </div>
    </div>

  </div>
</div>

<div class="card card-outline card-success mt-4">
  <div class="card-header"><strong>2. JASA TINDAKAN KEPERAWATAN</strong></div>
  <div class="card-body">

<!-- 2. Jasa Tindakan Keperawatan -->
<h5 class="mb-2">2. Jasa Tindakan Keperawatan </h5>
<div class="form-row mb-2">
  <div class="col-md-6">
    <input type="number" name="jasa_tindakan" id="jasa_tindakan" class="form-control" placeholder="Jumlah Jasa Tindakan">
  </div>
</div>

<!-- TOTAL Bagian 2 -->
<h4 class="text-center mt-4">TOTAL JASA TINDAKAN</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_jasa_tindakan" id="total_jasa_tindakan" class="form-control text-center" placeholder="Jumlah Total Bagian 2" readonly>
  </div>
</div>

<div class="card card-outline card-success mt-4">
  <div class="card-header"><strong>3. TENAGA AHLI</strong></div>
  <div class="card-body">

    <!-- A. Visite Dokter -->
    <h5 class="mb-2">A. Visite Dokter</h5>
    <?php for ($i = 1; $i <= 3; $i++): ?>
    <div class="form-row mb-2">
      <div class="col-md-4">
        <select name="dokter_visite_<?= $i ?>_id" class="form-control">
          <option value="">-- Dokter <?= $i ?> --</option>
          <?php foreach ($dokter_list as $dok): ?>
             <option value="<?= $dok['id'] ?>">
               <?= $dok['nama_dokter'] ?><?= isset($dok['spesialisasi']) && $dok['spesialisasi'] ? ' - ' . $dok['spesialisasi'] : '' ?>
             </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <input type="number" name="lama_visite_<?= $i ?>" id="lama_visite_<?= $i ?>" class="form-control" placeholder="Lama">
      </div>
      <div class="col-md-2">
        <input type="number" name="biaya_visite_<?= $i ?>" id="biaya_visite_<?= $i ?>" class="form-control" placeholder="Biaya/Visite">
      </div>
      <div class="col-md-2">
        <input type="number" name="total_visite_<?= $i ?>" id="total_visite_<?= $i ?>" class="form-control" placeholder="Total" readonly>
      </div>
    </div>
    <?php endfor; ?>

    <div class="form-group row mt-2">
      <label class="col-md-4 col-form-label text-right">Total Visite Dokter</label>
      <div class="col-md-3">
        <input type="number" name="total_visite_dokter" id="total_visite_dokter" class="form-control" readonly>
      </div>
    </div>

    <!-- B. Tindakan Medis -->
    <h5 class="mt-4">B. Tindakan Medis</h5>

    <?php for ($i = 1; $i <= 3; $i++): ?>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <select name="dokter_tindakan_<?= $i ?>_id" class="form-control">
          <option value="">-- Dokter <?= $i ?> --</option>
          <?php foreach ($dokter_list as $dok): ?>
             <option value="<?= $dok['id'] ?>">
               <?= $dok['nama_dokter'] ?><?= isset($dok['spesialisasi']) && $dok['spesialisasi'] ? ' - ' . $dok['spesialisasi'] : '' ?>
             </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <input type="number" name="biaya_tindakan_dokter_<?= $i ?>" id="biaya_tindakan_dokter" class="form-control" placeholder="Biaya">
      </div>
    </div>
    <?php endfor; ?>

    <?php for ($i = 1; $i <= 3; $i++): ?>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <select name="asisten_tindakan_<?= $i ?>_id" class="form-control">
          <option value="">-- Asisten <?= $i ?> --</option>
          <?php foreach ($asisten_list as $ast): ?>
             <option value="<?= $ast['id'] ?>">
               <?= isset($ast['nama_asisten']) ? $ast['nama_asisten'] : '(Tanpa Nama)' ?>
               <?= isset($ast['keterangan']) && $ast['keterangan'] ? ' - ' . $ast['keterangan'] : '' ?>
             </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <input type="number" name="biaya_asisten_<?= $i ?>" id="biaya_asisten" class="form-control" placeholder="Biaya">
      </div>
    </div>
    <?php endfor; ?>

    <div class="form-row mb-3">
      <div class="col-md-6">
        <select name="instrumen_onlop_id" class="form-control">
          <option value="">-- Instrumen/Onlop --</option>
          <?php foreach ($asisten_instrumen_list as $instrumen): ?>
            <option value="<?= $instrumen['id'] ?>"><?= $instrumen['nama_asisten'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <input type="number" name="biaya_instrumen_onlop" id="biaya_instrumen_onlop" class="form-control" placeholder="Biaya">
      </div>
    </div>

    <div class="form-group row mt-2">
      <label class="col-md-4 col-form-label text-right">Total Tindakan Medis</label>
      <div class="col-md-3">
        <input type="number" name="total_tindakan_medis" id="total_tindakan_medis" class="form-control" readonly>
      </div>
    </div>

    <!-- TOTAL Bagian 3 -->
    <h4 class="mt-4 text-center">TOTAL TENAGA AHLI</h4>
    <div class="form-group row justify-content-center">
      <div class="col-md-4">
        <input type="number" name="total_tenaga_ahli" id="total_tenaga_ahli" class="form-control text-center" placeholder="Jumlah Total Bagian 3" readonly>
      </div>
    </div>

  </div>
</div>

<div class="card card-outline card-warning mt-4">
  <div class="card-header">
    <strong>4. KONSULTASI DOKTER</strong>
  </div>
  <div class="card-body">

    <?php for ($i = 1; $i <= 3; $i++): ?>
      <div class="form-row mb-2">
        <div class="col-md-6">
          <label>Dokter Konsultasi <?= chr(96 + $i) ?></label>
          <select name="dokter_konsultasi_<?= $i ?>_id" class="form-control">
            <option value="">-- Dokter <?= $i ?> --</option>
            <?php foreach ($dokter_list as $dok): ?>
               <option value="<?= $dok['id'] ?>">
                 <?= $dok['nama_dokter'] ?><?= isset($dok['spesialisasi']) && $dok['spesialisasi'] ? ' - ' . $dok['spesialisasi'] : '' ?>
               </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label>Berapa Kali</label>
          <input type="number" name="jumlah_konsul_<?= $i ?>" id="jumlah_konsul_<?= $i ?>" class="form-control konsultasi-kali" placeholder="Kali Konsul">
        </div>
        <div class="col-md-3">
          <label>Biaya</label>
          <input type="number" name="biaya_konsul_<?= $i ?>" id="biaya_konsul_<?= $i ?>" class="form-control konsultasi-biaya" placeholder="Biaya Konsul">
        </div>
      </div>
    <?php endfor; ?>

    <div class="form-group row mt-3">
      <label class="col-md-4 col-form-label text-right">Total Konsultasi</label>
      <div class="col-md-4">
        <input type="number" name="total_konsultasi" id="total_konsultasi" class="form-control text-center" readonly placeholder="Jumlah Total bagian 4">
      </div>
    </div>

  </div>
</div>

<div class="card card-outline card-danger mt-4">
  <div class="card-header">
    <strong>5. LABORATORIUM</strong>
  </div>
  <div class="card-body">

    <!-- A. LABORATORIUM -->
    <h5 class="mb-2">A. Laboratorium</h5>
    <?php for ($i = 1; $i <= 2; $i++): ?>
      <div class="form-row mb-2">
        <div class="col-md-6">
          <select name="laboratorium_<?= $i ?>_id" class="form-control">
            <option value="">-- Laboratorium <?= $i ?> --</option>
            <?php foreach ($list_laboratorium as $lab): ?>
              <option value="<?= $lab['id'] ?>"><?= $lab['asal_laboratorium'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <input type="number" name="jumlah_lab_<?= $i ?>" id="jumlah_lab_<?= $i ?>"class="form-control jumlah-input" min="0" placeholder="Jumlah Laboratorium">
        </div>
      </div>
    <?php endfor; ?>

    <div class="form-group row mt-3">
      <label class="col-md-4 col-form-label text-right">Total Laboratorium</label>
      <div class="col-md-4">
        <input type="number" name="total_laboratorium" id="total_laboratorium" class="form-control text-center" readonly placeholder="Jumlah Total bagian 5">
      </div>
    </div>


<div class="card card-outline card-danger mt-4">
  <div class="card-header">
    <strong>6. RADIOLOGI</strong>
  </div>
  <div class="card-body">

    <!-- A. RADIOLOGI -->
    <h5 class="mt-4 mb-2">A. Radiologi</h5>

    <!-- USG -->
    <div class="form-row mb-2">
      <div class="col-md-6">
        <label>USG - Berapa Kali</label>
        <input type="number" name="usg_kali" id="usg_kali" class="form-control jumlah-input" min="0" placeholder="Kali Pemeriksaan">
      </div>
      <div class="col-md-6">
        <label>Jumlah USG</label>
        <input type="number" name="jumlah_usg" id="jumlah_usg" class="form-control jumlah-input" min="0" placeholder="Jumlah">
      </div>
    </div>

    <!-- Radiologi Tambahan -->
    <?php for ($i = 1; $i <= 2; $i++): ?>
      <div class="form-row mb-2">
        <div class="col-md-6">
          <label>Radiologi Tambahan <?= $i ?></label>
          <input type="text" name="radiologi_tambahan_<?= $i ?>" class="form-control" placeholder="Jenis Pemeriksaan">
        </div>
        <div class="col-md-6">
          <label>Jumlah</label>
          <input type="number" name="jumlah_radiologi_tambahan_<?= $i ?>" id="jumlah_radiologi_tambahan_<?= $i ?>" class="form-control jumlah-input" min="0" placeholder="Jumlah">
        </div>
      </div>
    <?php endfor; ?>

    <!-- Total Radiologi -->
    <h4 class="mt-4 text-center">TOTAL RADIOLOGI</h4>
    <div class="form-row justify-content-center mb-2">
      <div class="col-md-4">
        <input type="text" name="total_radiologi" id="total_radiologi" class="form-control text-center" readonly placeholder="Jumlah Total Bagian 6">
      </div>
    </div>

  </div>
</div>

<div class="card card-outline card-secondary mt-4">
  <div class="card-header">
    <strong>7. TINDAKAN</strong>
  </div>
  <div class="card-body">

    <!-- A. Phototerapi -->
    <h5 class="mb-2">A. Phototerapi</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="phototerapi_seri" class="form-control" placeholder="Berapa Seri">
      </div>
      <div class="col-md-6">
        <input type="number" name="phototerapi_biaya" id="phototerapi_biaya" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- B. Suction -->
    <h5 class="mb-2">B. Suction</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="biaya_suction" id="biaya_suction" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- C. Syringe Pump -->
    <h5 class="mb-2">C. Syringe Pump</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="biaya_syringe" id="biaya_syringe" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- D. Incubator -->
    <h5 class="mb-2">D. Incubator</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="incubator_kali" class="form-control" placeholder="Berapa Kali">
      </div>
      <div class="col-md-6">
        <input type="number" name="incubator_biaya" id="incubator_biaya" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- E. Nebulizer -->
    <h5 class="mb-2">E. Nebulizer</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="nebulizer_kali" class="form-control" placeholder="Berapa Kali">
      </div>
      <div class="col-md-6">
        <input type="number" name="nebulizer_biaya" id="nebulizer_biaya" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- F. Tindakan Lain 1 -->
    <h5 class="mb-2">F. Tindakan Lain 1</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="text" name="nama_tindakan_1" id="nama_tindakan_1" class="form-control" placeholder="Nama Tindakan Lain 1">
      </div>
      <div class="col-md-6">
        <input type="number" name="tindakan_lain_1" id="tindakan_lain_1" class="form-control" placeholder="Biaya Tindakan Lain 1">
      </div>
    </div>

    <!-- G. Tindakan Lain 2 -->
    <h5 class="mb-2">G. Tindakan Lain 2</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="text" name="nama_tindakan_2" id="nama_tindakan_2" class="form-control" placeholder="Nama Tindakan Lain 2">
      </div>
      <div class="col-md-6">
        <input type="number" name="tindakan_lain_2" id="tindakan_lain_2" class="form-control" placeholder="Biaya Tindakan Lain 2">
      </div>
    </div>

    <!-- Total Tindakan -->
    <h4 class="mt-4 text-center">TOTAL TINDAKAN</h4>
    <div class="form-row justify-content-center mb-2">
      <div class="col-md-4">
        <input type="number" name="total_tindakan" id="total_tindakan" class="form-control text-center" readonly placeholder="Jumlah Total Bagian 7">
      </div>
    </div>

  </div>
</div>

<div class="card card-outline card-info mt-4">
  <div class="card-header">
    <strong>8. PENUNJANG</strong>
  </div>
  <div class="card-body">

    <!-- A. NST -->
    <h5 class="mb-2">A. NST</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="nst_kali" class="form-control" placeholder="Berapa Kali Pemeriksaan NST">
      </div>
      <div class="col-md-6">
        <input type="number" name="nst_biaya" id="nst_biaya" class="form-control" placeholder="Jumlah">
      </div>
    </div>

    <!-- B. ECG -->
    <h5 class="mb-2">B. ECG</h5>
    <div class="form-row mb-2">
      <div class="col-md-6">
        <input type="number" name="ecg_biaya" id="ecg_biaya" class="form-control" placeholder="Jumlah Biaya ECG">
      </div>
    </div>

    <!-- TOTAL PENUNJANG -->
    <h4 class="mt-4 text-center">TOTAL PENUNJANG</h4>
    <div class="form-row justify-content-center mb-2">
      <div class="col-md-4">
        <input type="number" name="total_penunjang" id="total_penunjang" class="form-control text-center" placeholder="Jumlah Total Bagian 8" readonly>
      </div>
    </div>

  </div>
</div>

<div class="card card-outline card-danger mt-4">
  <div class="card-header"><strong>9. TRANSFUSI DARAH</strong>
  </div><div class="card-body">

<!-- A. Transfusi Darah -->
<h5 class="mb-2">A. Transfusi Darah</h5>
<div class="form-row mb-3">
  <div class="col-md-6">
    <input type="number" name="jumlah_transfusi" id="jumlah_transfusi" class="form-control" placeholder="Jumlah Transfusi">
  </div>
</div>

<!-- TOTAL Bagian 9 -->
<h4 class="text-center">TOTAL TRANSFUSI DARAH</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_transfusi_darah" id="total_transfusi_darah" class="form-control text-center" placeholder="Jumlah Total Bagian 9" readonly>
  </div>
</div>

<div class="card card-outline card-secondary mt-4">
  <div class="card-header"><strong>10. PROSEDUR NON BEDAH</strong>
  </div><div class="card-body">

<!-- A. Biaya Persalinan -->
<h5 class="mb-2">A. Biaya Persalinan</h5>
<div class="form-row mb-2">
  <div class="col-md-6">
    <input type="number" name="biaya_persalinan" id="biaya_persalinan" class="form-control" placeholder="Jumlah Biaya Persalinan">
  </div>
</div>

<!-- TOTAL Bagian 10 -->
<h4 class="text-center mt-4">TOTAL PROSEDUR NON BEDAH</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_prosedur_non_bedah" id="total_prosedur_non_bedah" class="form-control text-center" placeholder="Jumlah Total Bagian 10" readonly>
  </div>
</div>

<div class="card card-outline card-success mt-4">
  <div class="card-header"><strong>11. OBAT</strong>
  </div><div class="card-body">

<!-- A. Obat-obatan -->
<h5 class="mb-2">A. Obat-obatan</h5>
<div class="form-row mb-2">
  <div class="col-md-6">
    <input type="number" name="biaya_obat" id="biaya_obat" class="form-control" placeholder="Jumlah Biaya Obat-obatan">
  </div>
</div>

<!-- TOTAL Bagian 11 -->
<h4 class="text-center mt-4">TOTAL OBAT</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_obat" id="total_obat" class="form-control text-center" placeholder="Jumlah Total Bagian 11" readonly>
  </div>
</div>

<div class="card card-outline card-dark mt-4">
  <div class="card-header"><strong>12. PROSEDUR BEDAH</strong>
  </div><div class="card-body">

<!-- A. Kamar Operasi -->
<h5 class="mb-2">A. Kamar Operasi</h5>
<div class="form-row mb-2">
  <div class="col-md-6">
    <input type="number" name="biaya_kamar_operasi" id="biaya_kamar_operasi" class="form-control" placeholder="Biaya Kamar Operasi">
  </div>
</div>

<!-- TOTAL Bagian 12 -->
<h4 class="text-center">TOTAL PROSEDUR BEDAH</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_prosedur_bedah" id="total_prosedur_bedah" class="form-control text-center" placeholder="Jumlah Total Bagian 12" readonly>
  </div>
</div>

<div class="card card-outline card-dark mt-4">
  <div class="card-header"><strong>13. ALKES</strong></div>
  <div class="card-body">

    <!-- A. Peralatan Tindakan -->
    <h5 class="mb-2">A. Peralatan Tindakan</h5>
    <div class="form-row mb-3">
      <div class="col-md-6">
        <input type="number" name="biaya_alkes" id="biaya_alkes" class="form-control" placeholder="Jumlah Biaya Alkes">
      </div>
    </div>

    <!-- B. Alkes Tambahan 1 -->
    <h5 class="mb-2">B. Alkes Tambahan 1</h5>
    <div class="form-row mb-3">
      <div class="col-md-6">
        <input type="text" name="nama_alkes_1" id="nama_alkes_1" class="form-control" placeholder="Nama Alkes Tambahan 1">
      </div>
      <div class="col-md-6">
        <input type="number" name="alkes_tambahan_1" id="alkes_tambahan_1" class="form-control" placeholder="Biaya Alkes Tambahan 1">
      </div>
    </div>

    <!-- C. Alkes Tambahan 2 -->
    <h5 class="mb-2">C. Alkes Tambahan 2</h5>
    <div class="form-row mb-3">
      <div class="col-md-6">
        <input type="text" name="nama_alkes_2" id="nama_alkes_2" class="form-control" placeholder="Nama Alkes Tambahan 2">
      </div>
      <div class="col-md-6">
        <input type="number" name="alkes_tambahan_2" id="alkes_tambahan_2" class="form-control" placeholder="Biaya Alkes Tambahan 2">
      </div>
    </div>

    <!-- D. Alkes Tambahan 3 -->
    <h5 class="mb-2">D. Alkes Tambahan 3</h5>
    <div class="form-row mb-3">
      <div class="col-md-6">
        <input type="text" name="nama_alkes_3" id="nama_alkes_3" class="form-control" placeholder="Nama Alkes Tambahan 3">
      </div>
      <div class="col-md-6">
        <input type="number" name="alkes_tambahan_3" id="alkes_tambahan_3" class="form-control" placeholder="Biaya Alkes Tambahan 3">
      </div>
    </div>

    <!-- TOTAL Bagian 13 -->
    <h4 class="text-center">TOTAL ALKES</h4>
    <div class="form-row justify-content-center mb-2">
      <div class="col-md-4">
        <input type="number" name="total_alkes" id="total_alkes" class="form-control text-center" placeholder="Jumlah Total Bagian 13" readonly>
      </div>
    </div>

  </div>
</div>

<div class="card card-outline card-info mt-4">
  <div class="card-header"><strong>14. REHABILITASI</strong></div>
  <div class="card-body">

<!-- A. Rehabilitasi -->
<h5 class="mb-2">A. Rehabilitasi</h5>
<div class="form-row mb-3">
  <div class="col-md-6">
    <input type="number" name="biaya_rehabilitasi" id="biaya_rehabilitasi" class="form-control" placeholder="Jumlah Biaya Rehabilitasi">
  </div>
</div>

<!-- TOTAL Bagian 14 -->
<h4 class="text-center">TOTAL REHABILITASI</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_rehabilitasi" id="total_rehabilitasi" class="form-control text-center" placeholder="Jumlah Total Bagian 14" readonly>
  </div>
</div>

<div class="card card-outline card-danger mt-4">
  <div class="card-header"><strong>15. RAWAT INTENSIF</strong></div>
  <div class="card-body">

<!-- A. Rawat Intensif -->
<h5 class="mb-2">A. Rawat Intensif</h5>
<div class="form-row mb-3">
  <div class="col-md-6">
    <input type="number" name="biaya_rawat_intensif" id="biaya_rawat_intensif" class="form-control" placeholder="Jumlah Biaya Rawat Intensif">
  </div>
</div>

<!-- TOTAL Bagian 15 -->
<h4 class="text-center">TOTAL RAWAT INTENSIF</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_rawat_intensif" id="total_rawat_intensif" class="form-control text-center" placeholder="Jumlah Total Bagian 14" readonly>
  </div>
</div>

<div class="card card-outline card-secondary mt-4">
  <div class="card-header"><strong>16. BMHP</strong></div>
  <div class="card-body">

<!-- A. BMHP -->
<h5 class="mb-2">A. BMHP</h5>
<div class="form-row mb-3">
  <div class="col-md-6">
    <input type="number" name="biaya_bmhp" id="biaya_bmhp" class="form-control" placeholder="Jumlah Biaya BMHP">
  </div>
</div>

<!-- TOTAL Bagian 16 -->
<h4 class="text-center">TOTAL BMHP</h4>
<div class="form-row justify-content-center mb-2">
  <div class="col-md-4">
    <input type="number" name="total_bmhp" id="total_bmhp" class="form-control text-center" placeholder="Jumlah Total Bagian 16" readonly>
  </div>
</div>

<div class="card card-outline card-secondary mt-4">
  <div class="card-header"><strong>17. TOTAL BIAYA RAWAT INAP</strong></div>
  <div class="card-body">

    <div class="form-row mb-2">
      <div class="col-md-4"><label>Total Semua Bagian</label></div>
      <div class="col-md-4"><input type="number" name="total_semua_bagian" id="total_semua_bagian" class="form-control" placeholder="Total Semua Bagian" readonly></div>
    </div>

    <div class="form-row mb-2">
      <div class="col-md-4"><label>Uang Muka</label></div>
      <div class="col-md-4"><input type="number" name="uang_muka_rawat_inap" id="uang_muka_rawat_inap" class="form-control" placeholder="Total Uang Muka" readonly></div>
    </div>

    <div class="form-row mb-2">
      <div class="col-md-4"><label>Potongan</label></div>
      <div class="col-md-4"><input type="number" name="potongan_rawat_inap" id="potongan_rawat_inap" class="form-control" placeholder="Total Potongan" readonly></div>
    </div>

    <div class="form-row mb-2">
      <div class="col-md-4"><label><strong>Total Tagihan Sisa</strong></label></div>
      <div class="col-md-4"><input type="number" name="sisa_tagihan" id="sisa_tagihan" class="form-control text-danger font-weight-bold" placeholder="Sisa Tagihan" readonly></div>
    </div>

  </div>
</div>

<div class="card card-outline card-warning mt-4">
  <div class="card-header"><strong>18. CATATAN TAMBAHAN</strong></div>
  <div class="card-body">

    <div class="form-group">
      <label>Catatan</label>
      <textarea name="catatan" class="form-control" rows="3" placeholder="Isi Catatan Disini"></textarea>
    </div>

    <div class="form-group">
      <label for="tanggal_pemeriksaan">Tanggal Pemeriksaan</label>
      <input type="date" name="tanggal_pemeriksaan" id="tanggal_pemeriksaan" class="form-control" value="<?= date('Y-m-d') ?>">
    </div>

    <div class="form-row mb-2">
      <div class="col-md-4">
        <label for="qr-validasi">QR / Validasi</label>
      </div>
      <div class="col-md-4">
       <?php
        require_once $_SERVER['DOCUMENT_ROOT'] . '/medical_app/app/models/input/KasirModel.php';

        // 🔹 Ambil petugas login aktif
        $user_id = $_SESSION['user_id'] ?? 0;
        $kasir = KasirModel::getByUserId($conn, $user_id);

        $nama_petugas = $kasir['nama_petugas'] ?? 'Petugas Tidak Diketahui';
        $nip = $kasir['nip'] ?? '-';

        // 🔹 Tampilkan QR validasi jika tersedia
        if (!empty($qr_validasi_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . $qr_validasi_path)) {
          $src = htmlspecialchars($qr_validasi_path);
          $alt = 'QR Validasi oleh ' . htmlspecialchars($nama_petugas);

          echo <<<HTML
            <div class="qr-validasi-box text-center border p-2 rounded bg-light">
              <img src="{$src}" alt="{$alt}" height="100" class="mb-2" loading="lazy" />
              <p class="mb-0"><strong>{$nama_petugas}</strong></p>
              <p class="text-muted">NIP: {$nip}</p>
            </div>
          HTML;
        } else {
          echo '<span class="text-warning">QR belum divalidasi.</span>';
        }
        ?>
      </div>
    </div>

  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button type="submit" class="btn btn-success">Simpan</button>

  <!-- Tombol Kembali -->
  <a href="index.php?page=rincian_rawat_inap" class="btn btn-warning">
    Kembali
  </a>

  <!-- Keterangan tambahan -->
  <div class="mt-2 text-muted">
    <span class="fw-bold fs-6">
      <i class="fas fa-info-circle"></i> Untuk cetak, silakan buka menu <b>Daftar Rincian Rawat Inap</b>.
    </span>
  </div>

</div>

<script>
  window.rawatInapData = {
    uang_muka_rawat_inap: <?= $uang_muka ?? 0 ?>,
    potongan_rawat_inap: <?= $potongan ?? 0 ?>
  };
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const jenisRincian = document.getElementById('jenis_rincian');
  const kelasSelect  = document.getElementById('kelas_kamar_id');

  function setDisabled(sel, disabled) {
    document.querySelectorAll(sel).forEach(el => { if (el) el.disabled = disabled; });
  }

  function selectedKelasText() {
    const opt = kelasSelect.selectedOptions && kelasSelect.selectedOptions[0];
    return (opt ? opt.text : '').toLowerCase(); // gunakan teks, bukan value ID
  }

  function filterKelasSendiri() {
    [...kelasSelect.options].forEach(opt => opt.hidden = false);
  }

  function filterKelasGabung() {
    [...kelasSelect.options].forEach(opt => {
      const txt = (opt.text || '').toLowerCase();
      opt.hidden = !(txt.includes('dewasa') || txt.includes('ibu'));
    });
    // Jika selection tersembunyi setelah filter, kosongkan
    const sel = kelasSelect.selectedOptions[0];
    if (sel && sel.hidden) kelasSelect.value = '';
  }

  function resetAll() {
    // Kelas dan semua perawatan disable
    kelasSelect.disabled = true;
    setDisabled('#ibu_mulai,#ibu_selesai,#ibu_tarif,#ibu_hari,#ibu_total', true);
    setDisabled('#bayi_mulai,#bayi_selesai,#bayi_tarif,#bayi_hari,#bayi_total', true);
    setDisabled('#anak_mulai,#anak_selesai,#anak_tarif,#anak_hari,#anak_total', true);
  }

  function toggleFields() {
    // 1) Reset awal
    resetAll();

    // 2) Belum pilih jenis → selesai
    if (!jenisRincian.value) return;

    // 3) Aktifkan dropdown kelas sesuai mode
    kelasSelect.disabled = false;

    if (jenisRincian.value === 'Sendiri') {
      filterKelasSendiri();

      const ktxt = selectedKelasText();
      // Enable sesuai kelas yang dipilih
      if (ktxt.includes('dewasa') || ktxt.includes('ibu')) {
        // Perawatan Ibu: enable tanggal & tarif
        setDisabled('#ibu_mulai,#ibu_selesai,#ibu_tarif,#ibu_hari,#ibu_total', false);
        // Pastikan lainnya tetap disable
        setDisabled('#bayi_mulai,#bayi_selesai,#bayi_tarif,#bayi_hari,#bayi_total', true);
        setDisabled('#anak_mulai,#anak_selesai,#anak_tarif,#anak_hari,#anak_total', true);
      } else if (ktxt.includes('anak')) {
        // Perawatan Anak: enable tanggal & tarif
        setDisabled('#anak_mulai,#anak_selesai,#anak_tarif,#anak_hari,#anak_total', false);
        // Lainnya disable
        setDisabled('#ibu_mulai,#ibu_selesai,#ibu_tarif,#ibu_hari,#ibu_total', true);
        setDisabled('#bayi_mulai,#bayi_selesai,#bayi_tarif,#bayi_hari,#bayi_total', true);
      }
    }

    if (jenisRincian.value === 'Gabung') {
      filterKelasGabung();

      const ktxt = selectedKelasText();
      if (ktxt.includes('dewasa') || ktxt.includes('ibu')) {
        // Ibu enable penuh
        setDisabled('#ibu_mulai,#ibu_selesai,#ibu_tarif,#ibu_hari,#ibu_total', false);
        // Bayi: enable tanggal & tarif per hari, tapi hari & total tetap auto (disable)
        setDisabled('#bayi_mulai,#bayi_selesai,#bayi_tarif', false);
        setDisabled('#bayi_hari,#bayi_total', true);
        // Anak tetap disable
        setDisabled('#anak_mulai,#anak_selesai,#anak_tarif,#anak_hari,#anak_total', true);
      }
    }
  }

  // Event bindings
  jenisRincian.addEventListener('change', toggleFields);
  kelasSelect.addEventListener('change', toggleFields);

  // Init awal
  toggleFields();
});
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const bayarSelect = document.getElementById('jenis_bayar_id');
    const rincianSelect = document.getElementById('jenis_rincian');

    bayarSelect.addEventListener('change', function () {
      if (this.value !== '') {
        rincianSelect.disabled = false;   // aktifkan jika ada pilihan
      } else {
        rincianSelect.disabled = true;    // disable kalau kosong
        rincianSelect.value = '';         // reset pilihan
      }
    });
  });
</script>

<script src="assets/js/rincian_rawat_inap.js"></script>

<?php
include(__DIR__ . '/../layout/footer.php');
?>