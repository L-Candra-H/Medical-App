<?php
$isDashboard = false;
$isCetak = true;
include(__DIR__ . '/../layout/header.php');
?>

<div class="kuitansi-wrapper">
    <div class="kuitansi rincian-mode">
      <!-- Header Institusi -->
      <div class="logo-nama">
        <img src="/medical_app/public/assets/img/logo_institusi.png" alt="Logo Institusi" />
        <div class="institusi-info">
          <p><?= htmlspecialchars($data['nama_institusi'] ?? '-') ?></p>
          <p><?= htmlspecialchars($data['sub_institusi'] ?? '-') ?></p>
          <p><?= htmlspecialchars($data['alamat'] ?? '-') ?> | Telp: <?= htmlspecialchars($data['telepon'] ?? '-') ?></p>
        </div>
      </div>

      <!-- Judul Tengah -->
      <h2 class="kuitansi-title">RINCIAN RAWAT INAP PASIEN</h2>

      <!-- Isi Informasi -->
      <pre class="rincian-print">

NAMA : <?= $data['nama_pasien'] ?>	    KELAS : <?= $data['nama_kelas_perawatan'] ?>    	NOMOR REGISTER : <?= $data['nomor_register'] ?>

<?php
printf("%-45s%50s\n", "1.  PROSEDUR NON BEDAH (Biaya Persalinan)", 'Rp. ' . number_format($data['total_prosedur_non_bedah'], 0, ',', '.'));
echo "2.  TENAGA AHLI\n";
printf("%-45s%15s\n", "    " . $data['dokter_visite_1_nama'] . " (" . $data['lama_visite_1'] . " kali)", 'Rp. ' . number_format($data['biaya_visite_1'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['dokter_visite_2_nama'] . " (" . $data['lama_visite_2'] . " kali)", 'Rp. ' . number_format($data['biaya_visite_2'], 0, ',', '.'));
printf("%-45s%22s\n", "    " . $data['dokter_visite_3_nama'] . " (" . $data['lama_visite_3'] . " kali)", '<u>Rp. ' . number_format($data['biaya_visite_3'], 0, ',', '.'). '</u>');
printf("%-45s%30s\n", "    Total Visite", 'Rp. ' . number_format($data['total_visite_dokter'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['dokter_tindakan_1_nama'], 'Rp. ' . number_format($data['biaya_tindakan_dokter_1'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['dokter_tindakan_2_nama'], 'Rp. ' . number_format($data['biaya_tindakan_dokter_2'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['dokter_tindakan_3_nama'], 'Rp. ' . number_format($data['biaya_tindakan_dokter_3'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['asisten_tindakan_1_nama'], 'Rp. ' . number_format($data['biaya_asisten_1'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['asisten_tindakan_2_nama'], 'Rp. ' . number_format($data['biaya_asisten_2'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['asisten_tindakan_3_nama'], 'Rp. ' . number_format($data['biaya_asisten_3'], 0, ',', '.'));
printf("%-45s%22s\n", "    INSTRUMEN / ONLOP " . $data['instrumen_onlop_nama'], '<u>Rp. ' . number_format($data['biaya_instrumen_onlop'], 0, ',', '.'). '</u>');
printf("%-45s%37s\n", "    Total Tindakan Medis ", '<u>Rp. ' . number_format($data['total_tindakan_medis'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n", "    Total TENAGA AHLI ", 'Rp. ' . number_format($data['total_tenaga_ahli'], 0, ',', '.'));
echo "3.  RADIOLOGI\n";
printf("%-45s%15s\n","    a. USG (" . $data['usg_kali'] . " kali)", 'Rp. ' . number_format($data['jumlah_usg'], 0, ',', '.'));
printf("%-45s%15s\n","    b. " . $data['radiologi_tambahan_1'], 'Rp. ' . number_format($data['jumlah_radiologi_tambahan_1'], 0, ',', '.'));
printf("%-45s%22s\n","    c. " . $data['radiologi_tambahan_2'], '<u>Rp. ' . number_format($data['jumlah_radiologi_tambahan_2'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n", "    Total RADIOLOGI", 'Rp. ' . number_format($data['total_radiologi'], 0, ',', '.'));
printf("%-45s%50s\n", "4.  REHABILITASI", 'Rp. ' . number_format($data['total_rehabilitasi'], 0, ',', '.'));
printf("%-45s%50s\n", "5.  OBAT", 'Rp. ' . number_format($data['total_obat'], 0, ',', '.'));
echo "6.  SEWA ALAT\n";
printf("%-45s%15s\n","    a. PHOTOTERAPI (" . $data['phototerapi_seri'] . " Seri)", 'Rp. ' . number_format($data['phototerapi_biaya'], 0, ',', '.'));
printf("%-45s%15s\n","    b. SUCTION", 'Rp. ' . number_format($data['biaya_suction'], 0, ',', '.'));
printf("%-45s%15s\n","    c. SYRINGE PUMP", 'Rp. ' . number_format($data['biaya_syringe'], 0, ',', '.'));
printf("%-45s%15s\n","    d. INCUBATOR (" . $data['incubator_kali'] . "  Kali)", 'Rp. ' . number_format($data['incubator_biaya'], 0, ',', '.'));
printf("%-45s%22s\n","    e. NEBULIZER  (" . $data['nebulizer_kali'] . "  Kali)", '<u>Rp. ' . number_format($data['nebulizer_biaya'], 0, ',', '.'). '</u>');
printf("%-45s%15s\n","    f. " . $data['nama_tindakan_1'], 'Rp. ' . number_format($data['tindakan_lain_1'], 0, ',', '.'));
printf("%-45s%22s\n","    g. " . $data['nama_tindakan_2'], '<u>Rp. ' . number_format($data['tindakan_lain_2'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n", "    Total SEWA ALAT", 'Rp. ' . number_format($data['total_tindakan'], 0, ',', '.'));
printf("%-45s%50s\n", "7.  PROSEDUR BEDAH (Kamar Operasi)", 'Rp. ' . number_format($data['total_prosedur_bedah'], 0, ',', '.'));
printf("%-45s%50s\n", "8.  KEPERAWATAN (Jasa Tindakan)", 'Rp. ' . number_format($data['total_jasa_tindakan'], 0, ',', '.'));
echo "9.  LABORATORIUM\n";
printf("%-45s%15s\n","    a. " . $data['laboratorium_1_nama'], 'Rp. ' . number_format($data['jumlah_lab_1'], 0, ',', '.'));
printf("%-45s%22s\n","    b. " . $data['laboratorium_2_nama'], '<u>Rp. ' . number_format($data['jumlah_lab_2'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n", "    Total LABORATORIUM", 'Rp. ' . number_format($data['total_laboratorium'], 0, ',', '.'));
echo "10. KAMAR / AKOMODASI\n";
printf("%-45s%30s\n","    PERAWATAN IBU (" . $data['ibu_mulai'] . " s/d " . $data['ibu_selesai'] . ")", 'Rp. ' . number_format($data['ibu_total'], 0, ',', '.'));
printf("%-45s%30s\n","    PERAWATAN BAYI (" . $data['bayi_mulai'] . " s/d " . $data['bayi_selesai'] . ")", 'Rp. ' . number_format($data['bayi_total'], 0, ',', '.'));
printf("%-45s%30s\n","    PERAWATAN ANAK (" . $data['anak_mulai'] . " s/d " . $data['anak_selesai'] . ")", 'Rp. ' . number_format($data['anak_total'], 0, ',', '.'));
printf("%-45s%30s\n","    KAMAR BERSALIN", 'Rp. ' . number_format($data['total_bersalin'], 0, ',', '.'));
printf("%-45s%30s\n","    JASA RS & ADMINISTRASI", 'Rp. ' . number_format($data['total_jasa_rs'], 0, ',', '.'));
echo "    AMBULANCE\n";
printf("%-45s%15s\n","    a. " . $data['ambulance_1_nama'], 'Rp. ' . number_format($data['jumlah_ambulance_1'], 0, ',', '.'));
printf("%-45s%22s\n","    b. " . $data['ambulance_2_nama'], '<u>Rp. ' . number_format($data['jumlah_ambulance_2'], 0, ',', '.'). '</u>');
printf("%-45s%30s\n","    Total Ambulance", 'Rp. ' . number_format($data['total_ambulance'], 0, ',', '.'));
printf("%-45s%30s\n","    KARCIS", 'Rp. ' . number_format($data['total_karcis'], 0, ',', '.'));
printf("%-45s%37s\n","    MATERAI", '<u>Rp. ' . number_format($data['total_materai'], 0, ',', '.'). '</u>'); // ⬅️ underline di sini
printf("%-45s%50s\n", "    Total KAMAR / AKOMODASI", 'Rp. ' . number_format($data['total_kamar_akomodasi'], 0, ',', '.'));
?>
</pre>

<p style="text-align:center; font-weight:bold; margin-top:20px;">
  Halaman berikutnya =>>
</p>

<div class="page-break"></div>

<p style="text-align:center; font-weight:bold; margin:20px 0;">
  Lanjutan Halaman
</p>

<pre class="rincian-print">
<?php
echo "11. ALKES\n";
printf("%-45s%15s\n","    a. Peralatan Tindakan", 'Rp. ' . number_format($data['biaya_alkes'], 0, ',', '.'));
printf("%-45s%15s\n","    b. " . $data['nama_alkes_1'], 'Rp. ' . number_format($data['alkes_tambahan_1'], 0, ',', '.'));
printf("%-45s%15s\n","    c. " . $data['nama_alkes_2'], 'Rp. ' . number_format($data['alkes_tambahan_2'], 0, ',', '.'));
printf("%-45s%22s\n","    d. " . $data['nama_alkes_3'], '<u>Rp. ' . number_format($data['alkes_tambahan_3'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n", "    Total ALKES", 'Rp. ' . number_format($data['total_alkes'], 0, ',', '.'));
echo "12. KONSULTASI\n";
printf("%-45s%15s\n", "    " . $data['dokter_konsultasi_1_nama'] . " (" . $data['jumlah_konsul_1'] . " kali)", 'Rp. ' . number_format($data['biaya_konsul_1'], 0, ',', '.'));
printf("%-45s%15s\n", "    " . $data['dokter_konsultasi_2_nama'] . " (" . $data['jumlah_konsul_2'] . " kali)", 'Rp. ' . number_format($data['biaya_konsul_2'], 0, ',', '.'));
printf("%-45s%22s\n", "    " . $data['dokter_konsultasi_3_nama'] . " (" . $data['jumlah_konsul_3'] . " kali)", '<u>Rp. ' . number_format($data['biaya_konsul_3'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n", "    Total KONSULTASI ", 'Rp. ' . number_format($data['total_konsultasi'], 0, ',', '.'));
echo "13. PENUNJANG\n";
printf("%-45s%15s\n","    a. NST (" . $data['nst_kali'] . " kali)", 'Rp. ' . number_format($data['nst_biaya'], 0, ',', '.'));
printf("%-45s%22s\n","    b. ECG ", '<u>Rp. ' . number_format($data['ecg_biaya'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n", "    Total PENUNJANG ", 'Rp. ' . number_format($data['total_penunjang'], 0, ',', '.'));
printf("%-45s%50s\n", "14. TRANSFUSI DARAH", 'Rp. ' . number_format($data['total_transfusi_darah'], 0, ',', '.'));
printf("%-45s%50s\n", "15. RAWAT INTENSIF", 'Rp. ' . number_format($data['total_rawat_intensif'], 0, ',', '.'));
printf("%-45s%57s\n", "16. BMHP", '<u>Rp. ' . number_format($data['total_bmhp'], 0, ',', '.'). '</u>');
printf("%-45s%50s\n",      "TOTAL", 'Rp. ' . number_format($data['total_semua_bagian'], 0, ',', '.'));
?>
</div>

<br><br>
<center>
🙏 Semoga lekas sembuh, diberi kekuatan, kesabaran, dan kesehatan yang berlimpah hingga pulih kembali 🙏
</center>
<br><br>

<div class="footer-block <?= (!empty($data['jenis_bayar']) && $data['jenis_bayar'] === 'BPJS') ? 'bpjs' : '' ?>">
  <p><strong>
    Malang, <?= !empty($data['tanggal_pemeriksaan']) ? date('d-m-Y', strtotime($data['tanggal_pemeriksaan'])) : '-' ?>
  </strong></p>
  Bag. Keuangan<br>

  <?php
    $jenisBayar = strtoupper(trim($data['jenis_bayar'] ?? ''));
    if ($jenisBayar === 'BPJS'):
  ?>
    <div class="ttd-jarak"></div> <!-- Ruang kosong untuk tanda tangan basah -->
  <?php else: ?>
    <img src="<?= htmlspecialchars($data['qr_path'] ?? '#') ?>" class="qr-kuitansi" alt="QR Code" />
  <?php endif; ?>

  <p><strong><?= htmlspecialchars($data['nama_petugas'] ?? '-') ?></strong></p>
  <p class="nip-info">NIP: <?= htmlspecialchars($data['nip'] ?? '-') ?></p>
</div>


<?php include(__DIR__ . '/../layout/footer.php'); ?>