function formatRupiah(n) {
  return parseInt(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

// 🔸 HITUNG TOTAL BAGIAN 1
// 🔸 Hitung selisih hari antara tanggal
function hitungLamaHari(startId, endId, lamaId) {
  const start = new Date(document.getElementById(startId)?.value);
  const end = new Date(document.getElementById(endId)?.value);
  if (!isNaN(start) && !isNaN(end)) {
    const days = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1);
    document.getElementById(lamaId).value = days;
  }
}

// 🔸 Hitung subtotal perawatan
function hitungSubtotal(lamaId, tarifId, totalId) {
  const hari = parseInt(document.getElementById(lamaId)?.value || 0);
  const tarif = parseInt(document.getElementById(tarifId)?.value || 0);
  const total = hari * tarif;
  document.getElementById(totalId).value = total;
  hitungTotalKamar();
  hitungTotalAkomodasi();
}

// 🔸 Total kamar perawatan
function hitungTotalKamar() {
  const t1 = parseInt(document.getElementById('ibu_total')?.value.replace(/\D/g, '') || 0);
  const t2 = parseInt(document.getElementById('bayi_total')?.value.replace(/\D/g, '') || 0);
  const t3 = parseInt(document.getElementById('anak_total')?.value.replace(/\D/g, '') || 0);
  const total = t1 + t2 + t3;
  document.getElementById('total_kamar_perawatan').value = total;
}

// 🔸 Ambil tarif kamar dari server dan hitung
async function ambilTarifKamar() {
  const select = document.querySelector('[name="kelas_kamar_id"]');
  if (!select || !select.value) return;

  try {
    const res = await fetch(`ajax/get_tarif.php?id=${select.value}`);
    const data = await res.json();
    const tarif = parseInt(data.tarif || 0);

    const label = select.options[select.selectedIndex].text.toLowerCase();
    if (label.includes('dewasa') || label.includes('ibu')) {
      document.getElementById('ibu_tarif').value = tarif;
      hitungSubtotal('ibu_hari', 'ibu_tarif', 'ibu_total');
    } else if (label.includes('bayi')) {   // 🔹 tambahan untuk bayi
      document.getElementById('bayi_tarif').value = tarif;
      hitungSubtotal('bayi_hari', 'bayi_tarif', 'bayi_total');
    } else if (label.includes('anak')) {
      document.getElementById('anak_tarif').value = tarif;
      hitungSubtotal('anak_hari', 'anak_tarif', 'anak_total');
    }
  } catch (err) {
    console.error('Gagal ambil tarif kamar:', err);
  }
}

// 🔸 Inisialisasi listener input kamar
function initPerawatanKamar() {
  // Listener Ibu
  ['ibu_mulai', 'ibu_selesai', 'ibu_tarif'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
      hitungLamaHari('ibu_mulai', 'ibu_selesai', 'ibu_hari');
      hitungSubtotal('ibu_hari', 'ibu_tarif', 'ibu_total');
      hitungTotalKamar();
    });
  });

  // Listener Bayi 🔹 tambahan
  ['bayi_mulai', 'bayi_selesai', 'bayi_tarif'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
      hitungLamaHari('bayi_mulai', 'bayi_selesai', 'bayi_hari');
      hitungSubtotal('bayi_hari', 'bayi_tarif', 'bayi_total');
      hitungTotalKamar();
    });
  });

  // Listener Anak
  ['anak_mulai', 'anak_selesai', 'anak_tarif'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
      hitungLamaHari('anak_mulai', 'anak_selesai', 'anak_hari');
      hitungSubtotal('anak_hari', 'anak_tarif', 'anak_total');
      hitungTotalKamar();
    });
  });

  // Reset field saat kelas kamar berubah
  document.getElementById('kelas_kamar_id')?.addEventListener('change', function () {
    const label = this.options[this.selectedIndex].text.toLowerCase();

    let idList;
    if (label.includes('anak')) {
      idList = ['ibu_mulai', 'ibu_selesai', 'ibu_hari', 'ibu_tarif', 'ibu_total', 'ibu_total_hidden',
        'bayi_mulai', 'bayi_selesai', 'bayi_hari', 'bayi_tarif', 'bayi_total', 'bayi_total_hidden'];
    } else if (label.includes('bayi')) {
      idList = ['ibu_mulai', 'ibu_selesai', 'ibu_hari', 'ibu_tarif', 'ibu_total', 'ibu_total_hidden',
        'anak_mulai', 'anak_selesai', 'anak_hari', 'anak_tarif', 'anak_total', 'anak_total_hidden'];
    } else {
      idList = ['anak_mulai', 'anak_selesai', 'anak_hari', 'anak_tarif', 'anak_total', 'anak_total_hidden',
        'bayi_mulai', 'bayi_selesai', 'bayi_hari', 'bayi_tarif', 'bayi_total', 'bayi_total_hidden'];
    }

    idList.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = id.includes('hari') || id.includes('total') || id.includes('tarif') ? 0 : '';
    });

    hitungTotalKamar();
    ambilTarifKamar();
  });
}

// 🔸 Hitung total ambulance
function hitungTotalAmbulance() {
  const amb1 = parseInt(document.querySelector('[name="jumlah_ambulance_1"]')?.value || 0);
  const amb2 = parseInt(document.querySelector('[name="jumlah_ambulance_2"]')?.value || 0);
  const totalAmbulance = amb1 + amb2;
  document.getElementById('total_ambulance').value = totalAmbulance;
  hitungTotalAkomodasi();
}

// 🔸 Hitung total akomodasi akhir
function hitungTotalAkomodasi() {
  const ids = [
    'total_kamar_perawatan', 'total_bersalin',
    'total_ambulance', 'total_jasa_rs', 'total_karcis', 'total_materai',
  ];

  const total = ids.reduce((sum, id) => {
    const val = parseInt(document.getElementById(id)?.value.replace(/\D/g, '') || 0);
    return sum + val;
  }, 0);

  document.getElementById('total_kamar_akomodasi').value = total;
  // ⬅️ Tambahkan ini agar total keseluruhan ikut ter-update
  hitungTotalBiaya();
}

// 🔸 Listener untuk semua komponen akomodasi
function initAkomodasi() {
  ['jumlah_ambulance_1', 'jumlah_ambulance_2'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', hitungTotalAmbulance);
  });

  [
    'total_kamar_perawatan', 'total_bersalin',
    'total_ambulance', 'total_jasa_rs', 'total_karcis', 'total_materai',
  ].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', hitungTotalAkomodasi);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initPerawatanKamar();
  initAkomodasi();

  // Auto-trigger hitung ketika form terbuka
  ['ibu', 'bayi', 'anak'].forEach(prefix => {   // 🔹 tambahkan bayi
    hitungLamaHari(`${prefix}_mulai`, `${prefix}_selesai`, `${prefix}_hari`);
    hitungSubtotal(`${prefix}_hari`, `${prefix}_tarif`, `${prefix}_total`);
  });

  hitungTotalKamar();
  hitungTotalAkomodasi();
});

// 🔸 HITUNG TOTAL BAGIAN 2
// 🔸 Hitung Jasa Tindakan Keperawatan
function hitungJasaTindakan() {
  const val = parseFloat(document.getElementById('jasa_tindakan')?.value || 0);
  document.getElementById('total_jasa_tindakan').value = val;
  hitungTotalBiaya();
}

function initJasaTindakan() {
  const el = document.getElementById('jasa_tindakan');
  if (el) el.addEventListener('input', hitungJasaTindakan);
}

document.addEventListener('DOMContentLoaded', () => {
  initJasaTindakan(); // ✅ WAJIB ADA
});

if (document.getElementById('jasa_tindakan')) {
  hitungJasaTindakan();
}

// 🔸 HITUNG TOTAL BAGIAN 3
// 🔸 Hitung total visite dokter
function hitungVisiteDokter() {
  let total = 0;

  for (let i = 1; i <= 3; i++) {
    const lama = parseInt(document.getElementById(`lama_visite_${i}`)?.value || 0);
    const biaya = parseInt(document.getElementById(`biaya_visite_${i}`)?.value || 0);
    const subtotal = lama * biaya;

    const out = document.getElementById(`total_visite_${i}`);
    if (out) out.value = subtotal;

    total += subtotal;
  }

  const grand = document.getElementById('total_visite_dokter');
  if (grand) grand.value = total;

  hitungTotalTenagaAhli();
}

// 🔸 Hitung jumlah tindakan medis
function hitungTindakanMedis() {
  const dokter = document.getElementsByName('jumlah_tindakan_dokter[]');
  const asisten = document.getElementsByName('jumlah_tindakan_asisten[]');
  const instrumen = parseInt(document.getElementById('jumlah_tindakan_instrumen')?.value || 0);

  let total_dokter = 0;
  let total_asisten = 0;

  dokter.forEach(input => {
    const val = parseInt(input?.value) || 0;
    total_dokter += val;
  });

  asisten.forEach(input => {
    const val = parseInt(input?.value) || 0;
    total_asisten += val;
  });

  const total = total_dokter + total_asisten + instrumen;
  const output = document.querySelector('[name="total_tindakan"]');
  if (output) output.value = total;

  hitungTotalBiayaTindakan();
  hitungTotalTenagaAhli();
}

// 🔸 Hitung biaya tindakan medis (Kelompok B)
function hitungTotalBiayaTindakan() {
  const dokter = document.querySelectorAll('[id^="biaya_tindakan_dokter"]');
  const asisten = document.querySelectorAll('[id^="biaya_asisten"]');
  const instrumen = parseInt(document.getElementById('biaya_instrumen_onlop')?.value.replace(/\D/g, '') || 0);

  let total = instrumen;
  dokter.forEach(input => total += parseInt(input?.value.replace(/\D/g, '') || 0));
  asisten.forEach(input => total += parseInt(input?.value.replace(/\D/g, '') || 0));

  const output = document.querySelector('[name="total_tindakan_medis"]');
  if (output) output.value = total;
}

// 🔸 Hitung total gabungan tenaga ahli
function hitungTotalTenagaAhli() {
  const visite = parseInt(document.getElementById('total_visite_dokter')?.value.replace(/\D/g, '') || 0);
  const tindakan = parseInt(document.getElementById('total_tindakan_medis')?.value.replace(/\D/g, '') || 0);

  const output = document.querySelector('[name="total_tenaga_ahli"]');
  if (output) output.value = visite + tindakan;
  hitungTotalBiaya(); // ⬅️ Tambahkan di sini
}

// 🔸 Pasang semua event listener
function initTenagaAhli() {
  for (let i = 1; i <= 3; i++) {
    document.getElementById(`lama_visite_${i}`)?.addEventListener('input', hitungVisiteDokter);
    document.getElementById(`biaya_visite_${i}`)?.addEventListener('input', hitungVisiteDokter);

    document.getElementById(`biaya_tindakan_dokter`)?.addEventListener('input', () => {
      hitungTotalBiayaTindakan();
      hitungTotalTenagaAhli();
    });

    document.getElementById(`biaya_asisten`)?.addEventListener('input', () => {
      hitungTotalBiayaTindakan();
      hitungTotalTenagaAhli();
    });
  }

  document.getElementById('biaya_instrumen_onlop')?.addEventListener('input', () => {
    hitungTotalBiayaTindakan();
    hitungTotalTenagaAhli();
  });
}

// 🔸 HITUNG TOTAL BAGIAN 4
function hitungKonsultasi() {
  let total = 0;
  const jumlahs = document.querySelectorAll('[name^="jumlah_konsul_"]');
  const biayas = document.querySelectorAll('[name^="biaya_konsul_"]');

  for (let i = 0; i < jumlahs.length; i++) {
    const jml = parseFloat(jumlahs[i].value || 0);
    const biayaRaw = biayas[i].value || '0';
    const biaya = parseFloat(biayaRaw.toString().replace(/[^\d.-]/g, '')) || 0;

    total += biaya;
  }

  const totalField = document.getElementById('total_konsultasi');
  if (totalField) {
    totalField.value = total;
  }
  hitungTotalBiaya(); // ⬅️ Tambahkan ini
}

function initKonsultasi() {
  // Kalkulasi otomatis saat input berubah
  document.querySelectorAll('[name^="jumlah_konsul_"], [name^="biaya_konsul_"]').forEach(input =>
    input.addEventListener('input', hitungKonsultasi)
  );
}

document.addEventListener('DOMContentLoaded', initKonsultasi);

// 🔸 HITUNG TOTAL BAGIAN 5
function hitungLaboratorium() {
  let total_lab = 0;

  document.querySelectorAll('input[name^="jumlah_lab_"]').forEach(input => {
    total_lab += parseInt(input.value || 0);
  });

  const field = document.getElementById('total_laboratorium');
  if (field) field.value = total_lab;

  hitungTotalBiaya();
}

function initLaboratorium() {
  document.querySelectorAll('input[name^="jumlah_lab_"]')
    .forEach(input => input.addEventListener('input', hitungLaboratorium));

  hitungLaboratorium();
}

document.addEventListener('DOMContentLoaded', initLaboratorium);

// 🔸 HITUNG TOTAL BAGIAN 6
function hitungRadiologi() {
  let total_radio = 0;

  document.querySelectorAll('input[name^="jumlah_radiologi_tambahan_"], #jumlah_usg').forEach(input => {
    total_radio += parseInt(input.value || 0);
  });

  const field = document.getElementById('total_radiologi');
  if (field) field.value = total_radio;

  hitungTotalBiaya();
}

function initRadiologi() {
  document.querySelectorAll('input[name^="jumlah_radiologi_tambahan_"], #jumlah_usg')
    .forEach(input => input.addEventListener('input', hitungRadiologi));

  hitungRadiologi();
}

document.addEventListener('DOMContentLoaded', initRadiologi);

// 🔸 HITUNG TOTAL BAGIAN 7
function hitungTindakanKhusus() {
  const photo = parseInt(document.getElementById('phototerapi_biaya')?.value || 0);
  const suction = parseInt(document.getElementById('biaya_suction')?.value || 0);
  const syringe = parseInt(document.getElementById('biaya_syringe')?.value || 0);
  const incubator = parseInt(document.getElementById('incubator_biaya')?.value || 0);
  const nebuli = parseInt(document.getElementById('nebulizer_biaya')?.value || 0);
  const lain1 = parseInt(document.getElementById('tindakan_lain_1')?.value || 0);
  const lain2 = parseInt(document.getElementById('tindakan_lain_2')?.value || 0);

  const total = photo + suction + syringe + incubator + nebuli + lain1 + lain2;
  document.getElementById('total_tindakan').value = total;
  hitungTotalBiaya(); // update grand total
}

function initTindakanKhusus() {
  [
    'phototerapi_biaya',
    'biaya_suction',
    'biaya_syringe',
    'incubator_biaya',
    'nebulizer_biaya',
    'tindakan_lain_1',
    'tindakan_lain_2'
  ].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('input', hitungTindakanKhusus);
    } else {
      console.warn(`❌ Element '${id}' tidak ditemukan (tindakan_khusus.js)`);
    }
  });
}

// 🔸 HITUNG TOTAL BAGIAN 8
function hitungPenunjang() {
  const nst = parseInt(document.getElementById('nst_biaya')?.value || 0);
  const ecg = parseInt(document.getElementById('ecg_biaya')?.value || 0);
  const total = nst + ecg;
  document.getElementById('total_penunjang').value = total;
  hitungTotalBiaya(); // ⬅️ Tambahkan ini
}

function initPenunjang() {
  ['nst_biaya', 'ecg_biaya'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('input', hitungPenunjang);
    } else {
      console.warn(`❌ Element '${id}' tidak ditemukan (penunjang.js)`);
    }
  });
}

// 🔸 HITUNG TOTAL BAGIAN 9 sampai 16
function hitungKomponenBiaya() {
  let totalAlkes = 0; // kumpulkan total alkes di sini

  const panelList = [
    { input: 'jumlah_transfusi', output: 'total_transfusi_darah' },
    { input: 'biaya_persalinan', output: 'total_prosedur_non_bedah' },
    { input: 'biaya_obat', output: 'total_obat' },
    { input: 'biaya_kamar_operasi', output: 'total_prosedur_bedah' },
    { input: 'biaya_alkes', output: 'total_alkes' },
    { input: 'alkes_tambahan_1', output: 'total_alkes' }, // tambahan
    { input: 'alkes_tambahan_2', output: 'total_alkes' }, // tambahan
    { input: 'alkes_tambahan_3', output: 'total_alkes' }, // tambahan
    { input: 'biaya_rehabilitasi', output: 'total_rehabilitasi' },
    { input: 'biaya_rawat_intensif', output: 'total_rawat_intensif' },
    { input: 'biaya_bmhp', output: 'total_bmhp' }
  ];

  panelList.forEach(panel => {
    const inputEl = document.getElementById(panel.input);
    const outputEl = document.getElementById(panel.output);
    if (inputEl && outputEl) {
      const raw = inputEl.value || '0';
      const cleaned = cleanNumber(raw);
      const val = parseInt(cleaned);

      if (panel.output === 'total_alkes') {
        // kumpulkan dulu semua alkes
        totalAlkes += val;
      } else {
        outputEl.value = val;
      }
    }
  });

  // set total alkes sekali saja
  document.getElementById('total_alkes').value = totalAlkes;

  hitungTotalBiaya();
}

function initKomponenBiaya() {
  const inputs = [
    'jumlah_transfusi', 'biaya_persalinan', 'biaya_obat', 'biaya_kamar_operasi',
    'biaya_alkes', 'alkes_tambahan_1', 'alkes_tambahan_2', 'alkes_tambahan_3',
    'biaya_rehabilitasi', 'biaya_rawat_intensif', 'biaya_bmhp'
  ];

  inputs.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('input', hitungKomponenBiaya);
    } else {
      console.warn(`❌ Element '${id}' tidak ditemukan (komponen_biaya.js)`);
    }
  });
}

// 🔸 HITUNG TOTAL BAGIAN 17
function cleanNumber(str) {
  if (!str) return 0;
  str = str.toString()
    .replace(/[^0-9,.-]+/g, '')
    .replace(/\./g, '')
    .replace(',', '.');
  return parseFloat(str) || 0;
}

function safeSetValue(id, value, format = false) {
  const el = document.getElementById(id);
  if (!el) return;

  if (format && el.tagName === 'SPAN') {
    el.textContent = Number(value).toLocaleString('id-ID');
  } else {
    el.value = Number(cleanNumber(value)); // pastikan nilai mentah
  }
}

function hitungTotalBiaya() {
  const totalIds = [
    'total_kamar_akomodasi', 'total_jasa_tindakan', 'total_tenaga_ahli', 'total_konsultasi',
    'total_laboratorium', 'total_radiologi', 'total_tindakan', 'total_penunjang',
    'total_transfusi_darah', 'total_prosedur_non_bedah', 'total_obat',
    'total_prosedur_bedah', 'total_alkes', 'total_rehabilitasi',
    'total_rawat_intensif', 'total_bmhp'
  ];

  let grandTotal = 0;

  totalIds.forEach(id => {
    const el = document.getElementById(id);
    const valRaw = el?.value ?? el?.textContent ?? '0'; // 👈 tangkap input dan text langsung
    const val = cleanNumber(valRaw);
    console.log(`🔍 ${id}: ${valRaw} → ${val}`);
    grandTotal += isNaN(val) ? 0 : val;
  });

  safeSetValue('total_semua_bagian', formatRupiah(grandTotal));
  hitungSisaBayar(grandTotal);
}

function hitungSisaBayar(total) {
  const ambil = id => cleanNumber(document.getElementById(id)?.value);

  const muka = ambil('uang_muka_rawat_inap');
  const potongan = ambil('potongan_rawat_inap');
  const sisa = Math.max(0, total - muka - potongan);

  safeSetValue('sisa_tagihan', formatRupiah(sisa));
  safeSetValue('uang_muka_rawat_inap', formatRupiah(muka));
  safeSetValue('potongan_rawat_inap', formatRupiah(potongan));
}

function initRekapBiaya() {
  const inputIds = [
    'total_kamar_akomodasi', 'total_jasa_tindakan', 'total_tenaga_ahli', 'total_konsultasi',
    'total_laboratorium', 'total_radiologi', 'total_tindakan', 'total_penunjang',
    'total_transfusi_darah', 'total_prosedur_non_bedah', 'total_obat',
    'total_prosedur_bedah', 'total_alkes', 'total_rehabilitasi',
    'total_rawat_intensif', 'total_bmhp', 'uang_muka_rawat_inap', 'potongan_rawat_inap'
  ];

  inputIds.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', hitungTotalBiaya);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initRekapBiaya();

  ['uang_muka_rawat_inap', 'potongan_rawat_inap'].forEach(id => {
    const el = document.getElementById(id);
    if (el && !el.value) el.value = 0;
  });

  setTimeout(() => {
    hitungTotalBiaya();
  }, 150);
});

// 🔸 VALIDASI
function validasiFormSebelumSubmit(e) {
  const form = e.target;
  const idRawatInap = form.querySelector('input[name="rawat_inap_id"]')?.value.trim();
  const tanggalMasuk = form.querySelector('input[name="tanggal_masuk"]')?.value.trim();

  if (!idRawatInap || !tanggalMasuk) {
    alert("ID Rawat Inap dan Tanggal Masuk tidak boleh kosong.");
    e.preventDefault();
  }
}

function initValidasiForm() {
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', validasiFormSebelumSubmit);
  } else {
    console.warn(`❌ Tag <form> tidak ditemukan (validasi_form.js)`);
  }
}

function auditTotalFields() {
  const totalIds = [
    'total_kamar_akomodasi', 'total_jasa_tindakan', 'total_tenaga_ahli', 'total_konsultasi',
    'total_laboratorium', 'total_radiologi', 'total_tindakan', 'total_penunjang',
    'total_transfusi_darah', 'total_prosedur_non_bedah', 'total_obat',
    'total_prosedur_bedah', 'total_alkes', 'total_rehabilitasi',
    'total_rawat_intensif', 'total_bmhp'
  ];

  totalIds.forEach(id => {
    const el = document.getElementById(id);
    if (!el) {
      console.warn(`❌ Element '${id}' tidak ditemukan di DOM`);
    } else {
      console.log(`✅ '${id}' ditemukan:`, el.value);
    }
  });
}

// 🔸 LOAD SEMUA
document.addEventListener('DOMContentLoaded', () => {
  initPerawatanKamar();
  initAkomodasi();
  initJasaTindakan();
  initTenagaAhli();
  initKonsultasi();
  initLaboratorium();
  initRadiologi();
  initTindakanKhusus();
  initPenunjang();
  initKomponenBiaya();
  initRekapBiaya();
  initValidasiForm();

  // ⬇️ Inject data dari controller
  const data = window.rawatInapData || {};
  safeSetValue('uang_muka_rawat_inap', formatRupiah(data.uang_muka_rawat_inap || 0));
  safeSetValue('potongan_rawat_inap', formatRupiah(data.potongan_rawat_inap || 0));
  hitungTotalBiaya(); // agar sisa langsung dihitung
});

