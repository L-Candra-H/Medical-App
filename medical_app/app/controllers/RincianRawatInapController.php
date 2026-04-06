<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/RincianRawatInapModel.php';
require_once __DIR__ . '/../models/UangMukaModel.php';
require_once __DIR__ . '/../models/PotonganModel.php';
require_once __DIR__ . '/../models/input/DokterModel.php';
require_once __DIR__ . '/../models/input/AsistenModel.php';
require_once __DIR__ . '/../models/master/LaboratoriumModel.php';
require_once __DIR__ . '/../models/master/AmbulanceModel.php';
require_once __DIR__ . '/../models/InstitusiModel.php';
require_once __DIR__ . '/../helpers/terbilang.php';
require_once __DIR__ . '/../helpers/QRCodeHelper.php';
require_once __DIR__ . '/../models/input/KasirModel.php';

function formatRegister($val): string {
    $val = intval($val);
    return $val > 0 ? str_pad((string) $val, 6, '0', STR_PAD_LEFT) : '000001';
}

class RincianRawatInapController {

  public static function index(mysqli $conn): void {
    // Ambil halaman aktif dari URL (default 1)
    $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
    if ($page < 1) $page = 1;

    $limit  = 6; // jumlah baris per halaman
    $offset = ($page - 1) * $limit;
    $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

    // Hitung total data sesuai pencarian
    $totalData = RincianRawatInapModel::countAll($conn, $search);
    $totalPage = ceil($totalData / $limit);
    if ($totalPage < 1) $totalPage = 1;

    // Ambil data sesuai halaman + pencarian
    $rincian_list = RincianRawatInapModel::getPaginated($conn, $limit, $offset, $search);

    // Siapkan variabel untuk view
    $pagination = [
      'page'      => $page,
      'totalPage' => $totalPage,
      'search'    => $search
    ];

    $data = [
      'rincian'    => $rincian_list,
      'pagination' => $pagination,
      'offset'     => $offset
    ];

    include __DIR__ . '/../views/rincian_rawat_inap/index.php';
  }

  public static function create(mysqli $conn): void {
    $rawat_inap_id  = $_GET['id'] ?? null;
    $kategori_aktif = $_GET['kategori'] ?? 'biasa';
    $mode_manual    = empty($rawat_inap_id);

    $data_pasien = null;
    $pasien = null;
    $list_pasien_bertiket = [];

    require_once __DIR__ . '/../models/input/PasienModel.php';

    if (!$mode_manual) {
      require_once __DIR__ . '/../models/TiketRawatInapModel.php';

      $tiketModel  = new TiketRawatInapModel($conn);
      $rawat_inap  = $tiketModel->getById($rawat_inap_id);
      $pasien      = PasienModel::findById($conn, $rawat_inap['pasien_id'] ?? 0);

      // ✅ Tambahkan validasi di sini
      if ($tiketModel->isSelesai($rawat_inap_id)) {
        echo '<div class="alert alert-warning mx-3 mt-3">Rawat inap ini sudah selesai. Tidak bisa buat rincian baru.</div>';
        return;
      }

      $data_pasien = [
        'pasien_id'       => $pasien['id'] ?? '',
        'nama_pasien'     => $pasien['nama_pasien'] ?? '',
        'nomor_register'  => $pasien['nomor_register'] ?? '',
        'rawat_inap_id'   => $rawat_inap_id,
        'tanggal_masuk'   => $rawat_inap['tanggal_masuk'] ?? '',
        'jenis_rincian'   => $rawat_inap['jenis_rincian'] ?? ''   // 🔹 tambahan
      ];
    } else {
      $list_pasien_bertiket = PasienModel::getPasienBertiket($conn);
    }

    $dokter_list       = DokterModel::getAll($conn);
    $asisten_list_all  = AsistenModel::getAll($conn);
    $list_laboratorium = LaboratoriumModel::getAll($conn);
    $ambulance_list    = AmbulanceModel::getAll($conn);

    $asisten_list = array_filter($asisten_list_all, fn($row) =>
      isset($row['keterangan']) && (
        $kategori_aktif === 'Instrumen/Onlop'
          ? $row['keterangan'] === 'Instrumen/Onlop'
          : $row['keterangan'] !== 'Instrumen/Onlop'
      )
    );

    $asisten_instrumen_list = array_filter($asisten_list_all, fn($row) =>
      isset($row['keterangan']) && $row['keterangan'] === 'Instrumen/Onlop'
    );

    require_once __DIR__ . '/../models/master/JenisBayarModel.php';
    require_once __DIR__ . '/../models/master/KelasKamarModel.php';

    $jenis_bayar_list = JenisBayarModel::getAll($conn);
    $kelas_kamar_list = KelasKamarModel::getAll($conn);

    require_once __DIR__ . '/../models/UangMukaModel.php';
    require_once __DIR__ . '/../models/PotonganModel.php';

    $uang_muka = formatAngka($rawat_inap_id ? UangMukaModel::getTotalByRawatInap($conn, $rawat_inap_id) : 0);
    $potongan  = formatAngka($rawat_inap_id ? PotonganModel::getTotalByRawatInap($conn, $rawat_inap_id) : 0);

    require_once __DIR__ . '/../models/input/KasirModel.php';

    $user_id = $_SESSION['user_id'] ?? 0;
    $kasir = KasirModel::getByUserId($conn, $user_id);

    $qr_filename = $kasir['qr_filename'] ?? null;
    $qr_validasi_path = $qr_filename ? '/medical_app/public/qrcode/' . $qr_filename : null;
    $nama_petugas = $kasir['nama_petugas'] ?? '-';
    $nip = $kasir['nip'] ?? '-';

    echo "<script>
      window.rawatInapData = {
        uang_muka_rawat_inap: " . formatAngka($uang_muka) . ",
        potongan_rawat_inap: " . formatAngka($potongan) . "
      };

    </script>";

    extract(compact(
      'dokter_list', 'asisten_list', 'asisten_instrumen_list',
      'list_laboratorium', 'ambulance_list', 'kategori_aktif',
      'pasien', 'rawat_inap_id', 'data_pasien', 'mode_manual',
      'list_pasien_bertiket', 'jenis_bayar_list', 'kelas_kamar_list',
      'uang_muka', 'potongan', 'tiketModel', 'qr_validasi_path', 'nama_petugas', 'nip'
    ));

    include __DIR__ . '/../views/rincian_rawat_inap/create.php';
  }

  public static function store(mysqli $conn): void {
    $rawat_inap_id = $_POST['rawat_inap_id'] ?? null;
    if (!$rawat_inap_id) {
      self::redirectWithAlert('ID rawat inap tidak ditemukan!', 'javascript:history.back()');
    }

    // 🔹 Ambil dan normalisasi data
    $data = $_POST;
    $data['rawat_inap_id'] = $rawat_inap_id;
    $data['nama_pasien'] = $_POST['nama_pasien'] ?? '';
    $data['nomor_register'] = $_POST['nomor_register'] ?? '';
    $data['jenis_rincian'] = $_POST['jenis_rincian'] ?? '';
    
    // 🔹 Resolusi label
    $data = self::resolveLabels($conn, $data);

    // 🔧 Normalisasi dan resolusi ID
    $data = RincianRawatInapModel::normalizeData($data);
    if (empty($data)) {
      self::redirectWithAlert('Data tidak valid (kamar ibu dan anak tidak boleh terisi bersamaan)', 'javascript:history.back()');
    }

    $data = RincianRawatInapModel::prepareAutoFields($data);
    $data = RincianRawatInapModel::resolveIdFields($conn, $data);

    // 🔹 Generate dan simpan path QR
    $petugas = QRCodeHelper::getPetugas();
    $qr_filename = QRCodeHelper::generate($petugas['nip'], $petugas['nama_petugas']);
    $data['qr_validasi_path'] = '/medical_app/public/qrcode/' . $qr_filename;
    $data['dibuat_oleh'] = $_SESSION['user_id'] ?? null;
    $data['nip'] = $petugas['nip'] ?? null;

    // 🔄 Hitung total dan sisa sebelum simpan
    $total     = (int) formatAngka(RincianRawatInapModel::getTotalBiaya($conn, $rawat_inap_id));
    $uang_muka = (int) formatAngka(UangMukaModel::getTotalByRawatInap($conn, $rawat_inap_id));
    $potongan  = (int) formatAngka(PotonganModel::getTotalByRawatInap($conn, $rawat_inap_id));
    $sisa      = $total - $uang_muka - $potongan;

    $data['uang_muka']    = $uang_muka;
    $data['potongan']     = $potongan;
    $data['sisa_tagihan'] = $sisa;

    // 🚀 Simpan ke database
    RincianRawatInapModel::save($conn, $data);

    // 🔄 Sinkronisasi rekap (jika perlu)
    RincianRawatInapModel::syncRekap($conn, $rawat_inap_id);

    self::redirectWithAlert('Rincian berhasil disimpan!', "index.php?page=rincian_rawat_inap");
  }

  public static function edit(mysqli $conn, ?int $rawat_inap_id = null): void {
    if (!$rawat_inap_id) {
      self::redirectWithAlert('ID episode tidak ditemukan!', 'index.php?page=pasien');
    }

    // 🔹 Ambil rincian dan validasi status
    $rincian = RincianRawatInapModel::getByRawatInapId($conn, $rawat_inap_id);
    if (!$rincian) {
      self::redirectWithAlert('Data rincian belum tersedia!', 'index.php?page=pasien');
    }
    if ($rincian['status'] === 'SELESAI') {
      self::redirectWithAlert('Rincian sudah selesai dan tidak bisa diedit.', 'index.php?page=rincian_rawat_inap');
    }

    require_once __DIR__ . '/../models/input/KasirModel.php';

    $dibuat_oleh = $rincian['dibuat_oleh'] ?? null;
    $qr_validasi_path = $rincian['qr_validasi_path'] ?? null;
    $nama_petugas = '-';
    $nip = $rincian['nip'] ?? '-';

    if ($dibuat_oleh && is_numeric($dibuat_oleh)) {
      $kasir = KasirModel::getByUserId($conn, $dibuat_oleh);
      $nama_petugas = $kasir['nama_petugas'] ?? '-';
      $nip = $kasir['nip'] ?? $nip;
    }

    // 🔹 Ambil data pasien dari tiket → pasien
    $stmtTiket = $conn->prepare("SELECT pasien_id FROM tiket_rawat_inap WHERE id = ?");
    $stmtTiket->bind_param("i", $rawat_inap_id);
    $stmtTiket->execute();
    $pasien_id = $stmtTiket->get_result()->fetch_assoc()['pasien_id'] ?? 0;

    $data_pasien = [];
    if ($pasien_id > 0) {
      $stmtPasien = $conn->prepare("SELECT id AS pasien_id, nama_pasien, nomor_register FROM pasien WHERE id = ?");
      $stmtPasien->bind_param("i", $pasien_id);
      $stmtPasien->execute();
      $data_pasien = $stmtPasien->get_result()->fetch_assoc() ?? [];
    }

    // 🔹 Ambil preset tarif berdasarkan kelas kamar
    $stmt = $conn->prepare("
      SELECT kk.nama_kelas, kk.jenis_pasien
      FROM rawat_inap_rincian r
      JOIN kelas_kamar kk ON r.kelas_kamar_id = kk.id
      WHERE r.rawat_inap_id = ?
      ORDER BY r.id DESC
      LIMIT 1
    ");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $nama_kelas = $row['nama_kelas'] ?? null;
    $jenis_pasien = $row['jenis_pasien'] ?? null;
    $tarif = RincianRawatInapModel::getTarifKelasKamar($conn, $nama_kelas, $jenis_pasien);

    // 🔹 Ambil semua list dropdown
    $list_dokter       = DokterModel::getAll($conn);
    $list_ambulance    = AmbulanceModel::getAll($conn);
    $list_laboratorium = LaboratoriumModel::getAll($conn);
    $list_asisten_all  = AsistenModel::getAll($conn);

    // 🔹 Pisahkan asisten tindakan dan instrumen/onlop
    $list_asisten = array_filter($list_asisten_all, fn($row) =>
      isset($row['keterangan']) && $row['keterangan'] !== 'Instrumen/Onlop'
    );
    $list_instrumen = array_filter($list_asisten_all, fn($row) =>
      isset($row['keterangan']) && $row['keterangan'] === 'Instrumen/Onlop'
    );

    // 🔹 Ambil rekap biaya
    $rekap_biaya = RincianRawatInapModel::getRekapBiaya($conn, $rawat_inap_id);
    $uang_muka   = (int) formatAngka(UangMukaModel::getTotalByRawatInap($conn, $rawat_inap_id));
    $potongan    = (int) formatAngka(PotonganModel::getTotalByRawatInap($conn, $rawat_inap_id));
    $tanggal_pemeriksaan = $rincian['tanggal_pemeriksaan'] ?? '';

    // 🔹 Preset ID untuk dropdown
    $preset = [
      'laboratorium_1_id' => $rincian['laboratorium_1_id'] ?? '',
      'laboratorium_2_id' => $rincian['laboratorium_2_id'] ?? '',
      'ambulance_1_id' => $rincian['ambulance_1_id'] ?? '',
      'ambulance_2_id' => $rincian['ambulance_2_id'] ?? '',
      'dokter_visite_1_id' => $rincian['dokter_visite_1_id'] ?? '',
      'dokter_visite_2_id' => $rincian['dokter_visite_2_id'] ?? '',
      'dokter_visite_3_id' => $rincian['dokter_visite_3_id'] ?? '',
      'dokter_tindakan_1_id' => $rincian['dokter_tindakan_1_id'] ?? '',
      'dokter_tindakan_2_id' => $rincian['dokter_tindakan_2_id'] ?? '',
      'dokter_tindakan_3_id' => $rincian['dokter_tindakan_3_id'] ?? '',
      'asisten_tindakan_1_id' => $rincian['asisten_tindakan_1_id'] ?? '',
      'asisten_tindakan_2_id' => $rincian['asisten_tindakan_2_id'] ?? '',
      'asisten_tindakan_3_id' => $rincian['asisten_tindakan_3_id'] ?? '',
      'instrumen_onlop_id' => $rincian['instrumen_onlop_id'] ?? '',
      'dokter_konsultasi_1_id' => $rincian['dokter_konsultasi_1_id'] ?? '',
      'dokter_konsultasi_2_id' => $rincian['dokter_konsultasi_2_id'] ?? '',
      'dokter_konsultasi_3_id' => $rincian['dokter_konsultasi_3_id'] ?? '',
    ];

    $isEdit = true;

      extract(array_merge(
      $rekap_biaya,
      $preset,
      compact(
        'rincian', 'uang_muka', 'potongan',
        'list_dokter', 'list_ambulance', 'list_laboratorium',
        'list_asisten', 'list_instrumen',
        'tanggal_pemeriksaan', 'isEdit', 'data_pasien', 'nama_petugas', 'nip', 'qr_validasi_path'
      )
    ));

    include __DIR__ . '/../views/rincian_rawat_inap/edit.php';
  }

  public static function update(mysqli $conn): void {
    error_log("🚀 Masuk ke update() dari index.php");
    error_log("POST nomor_register: " . ($_POST['nomor_register'] ?? 'NULL'));
    
    $rawat_inap_id = $_POST['rawat_inap_id'] ?? null;
    if (!$rawat_inap_id) {
      self::redirectWithAlert('ID rawat inap tidak ditemukan!', 'javascript:history.back()');
    }

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
      self::redirectWithAlert('ID rincian tidak valid!', 'javascript:history.back()');
    }

    // ✅ Ambil pasien_id dari tiket_rawat_inap
    $stmtTiket = $conn->prepare("SELECT pasien_id FROM tiket_rawat_inap WHERE id = ?");
    $stmtTiket->bind_param("i", $rawat_inap_id);
    $stmtTiket->execute();
    $resultTiket = $stmtTiket->get_result();
    $rowTiket = $resultTiket->fetch_assoc();
    $pasien_id = $rowTiket['pasien_id'] ?? 0;

    // ✅ Ambil nama_pasien dan nomor_register dari pasien
    $data_pasien = [];
    if ($pasien_id > 0) {
      $stmtPasien = $conn->prepare("SELECT nama_pasien, nomor_register FROM pasien WHERE id = ?");
      $stmtPasien->bind_param("i", $pasien_id);
      $stmtPasien->execute();
      $resultPasien = $stmtPasien->get_result();
      $data_pasien = $resultPasien->fetch_assoc() ?? [];
    }

    // 🔹 Ambil dan normalisasi data
    $data = $_POST;
    $data['rawat_inap_id'] = $rawat_inap_id;
    $data['nama_pasien'] = $data_pasien['nama_pasien'] ?? '';
    $data['nomor_register'] = $data_pasien['nomor_register'] ?? '';

    // ✅ Tambahkan validasi di sini
    if (empty($data['nomor_register'])) {
      error_log("❗ nomor_register kosong saat update");
      self::redirectWithAlert('Nomor register tidak boleh kosong!', 'javascript:history.back()');
    }

    if (!ctype_digit((string) $data['nomor_register']) || intval($data['nomor_register']) <= 0) {
      error_log("❗ nomor_register tidak valid: " . $data['nomor_register']);
      self::redirectWithAlert('Nomor register harus berupa angka positif!', 'javascript:history.back()');
    }

    // 🔹 Resolusi label
    $data = self::resolveLabels($conn, $data);

    // 🔧 Normalisasi dan resolusi ID
    $data = RincianRawatInapModel::normalizeData($data);
    if (empty($data)) {
      self::redirectWithAlert('Data tidak valid (kamar ibu dan anak tidak boleh terisi bersamaan)', 'javascript:history.back()');
    }

    $data = RincianRawatInapModel::prepareAutoFields($data);
    $data = RincianRawatInapModel::resolveIdFields($conn, $data);

    $petugas = QRCodeHelper::getPetugas();
    $qr_filename = QRCodeHelper::generate($petugas['nip'], $petugas['nama_petugas']);
    $data['qr_validasi_path'] = '/medical_app/public/qrcode/' . $qr_filename;
    $data['dibuat_oleh'] = $_SESSION['user_id'] ?? null;
    $data['nip'] = $petugas['nip'] ?? null;

    // 🔄 Hitung total dan sisa sebelum update
    $total     = RincianRawatInapModel::getTotalBiaya($conn, $rawat_inap_id);
    $uang_muka = UangMukaModel::getTotalByRawatInap($conn, $rawat_inap_id);
    $potongan  = PotonganModel::getTotalByRawatInap($conn, $rawat_inap_id);
    $sisa      = $total - $uang_muka - $potongan;

    $data['uang_muka']    = $uang_muka;
    $data['potongan']     = $potongan;
    $data['sisa_tagihan'] = $sisa;

    // 🚀 Update ke database
    $rawatInapId = $_POST['rawat_inap_id'] ?? null;

    $success = RincianRawatInapModel::updateById($conn, $id, $data);
    if (!$success) {
      self::redirectWithAlert('❌ Gagal menyimpan versi baru!', 'javascript:history.back()');
    }

    // 🔄 Sinkronisasi rekap (jika perlu)
    RincianRawatInapModel::syncRekap($conn, $rawat_inap_id);

    self::redirectWithAlert('Rincian berhasil diperbarui!', "index.php?page=rincian_rawat_inap");
  }

  public static function selesaikan_ajax(mysqli $conn, int $rawat_inap_id): void {
    $tanggal_keluar = self::getTanggalSelesaiRawat($conn, $rawat_inap_id);

    $sqlTiket = "UPDATE tiket_rawat_inap SET status = 'SELESAI', tanggal_keluar = ? WHERE id = ?";
    $stmtTiket = $conn->prepare($sqlTiket);
    $stmtTiket->bind_param('si', $tanggal_keluar, $rawat_inap_id);
    $stmtTiket->execute();
    $stmtTiket->close();

    $sqlRincian = "UPDATE rawat_inap_rincian SET status = 'SELESAI', tanggal_selesai = ? WHERE rawat_inap_id = ?";
    $stmtRincian = $conn->prepare($sqlRincian);
    $stmtRincian->bind_param('si', $tanggal_keluar, $rawat_inap_id);
    $stmtRincian->execute();
    $stmtRincian->close();

    echo json_encode(['success' => true]);
    exit;
  }

  private static function getTanggalSelesaiRawat(mysqli $conn, int $rawat_inap_id): string {
    $sql = "
      SELECT 
        GREATEST(
          COALESCE(MAX(ibu_selesai), '0000-00-00'),
          COALESCE(MAX(anak_selesai), '0000-00-00')
        ) AS tanggal_selesai
      FROM rawat_inap_rincian
      WHERE rawat_inap_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $rawat_inap_id);
    $stmt->execute();
    $stmt->bind_result($tanggal_selesai);
    $stmt->fetch();
    $stmt->close();

    return $tanggal_selesai ?: date('Y-m-d');
  }

  public static function cetak_kuitansi(mysqli $conn, int $rawat_inap_id): void {
    // Ambil rincian utama
    $rincian = RincianRawatInapModel::getByRawatInapId($conn, $rawat_inap_id);

    if (!$rincian) {
      self::redirectWithAlert('Data rincian belum tersedia!', 'index.php?page=pasien');
    }

    // Ambil data institusi
    $institusi = self::getInstitusi($conn);

    // Ambil data pasien
    $pasien = self::getPasienByRawatInap($conn, $rawat_inap_id);

    // Ambil data petugas/kasir
    $dibuat_oleh = $rincian['dibuat_oleh'] ?? null;
    $nip = $rincian['nip'] ?? '-';
    $nama_petugas = '-';

    if ($dibuat_oleh && is_numeric($dibuat_oleh)) {
      $kasir = KasirModel::getByUserId($conn, $dibuat_oleh);
      $nama_petugas = $kasir['nama_petugas'] ?? '-';
    }

    // Ambil data petugas dari kasir
    $petugas = self::getPetugasKasir($conn, $rawat_inap_id);

    // Gabungkan semua data
    $data = array_merge(
      $rincian,
      $institusi,
      $pasien,
      [
        'terbilang' => ucwords(terbilang($rincian['total_semua_bagian'] ?? 0)),
        'qr_path' => $rincian['qr_validasi_path'] ?? null,
        'nama_petugas' => $nama_petugas,
        'nip' => $nip,
        'jenis_bayar' => $rincian['nama_jenis_bayar'] ?? ''
      ]
    );
    include __DIR__ . '/../views/rincian_rawat_inap/cetak_kuitansi.php';
  }

  public static function cetakRincian(mysqli $conn, int $rawat_inap_id): void {
    if (!$rawat_inap_id) {
      header("Location: index.php?page=landing");
      exit;
    }

    // Ambil rincian utama
    $rincian = RincianRawatInapModel::getByRawatInapId($conn, $rawat_inap_id);
    if (!$rincian) {
      echo "❌ Data tidak ditemukan.";
      return;
    }

    // Ambil data institusi
    $institusi = self::getInstitusi($conn);

    // Ambil data pasien
    $pasien = self::getPasienByRawatInap($conn, $rawat_inap_id);

    // Ambil data petugas/kasir
    $dibuat_oleh = $rincian['dibuat_oleh'] ?? null;
    $nip = $rincian['nip'] ?? '-';
    $nama_petugas = '-';

    if ($dibuat_oleh && is_numeric($dibuat_oleh)) {
      $kasir = KasirModel::getByUserId($conn, $dibuat_oleh);
      $nama_petugas = $kasir['nama_petugas'] ?? '-';
    }

    // Gabungkan semua data
    $data = array_merge(
      $rincian,
      $institusi,
      $pasien,
      [
        'terbilang' => ucwords(terbilang($rincian['total_semua_bagian'] ?? 0)),
        'qr_path' => $rincian['qr_validasi_path'] ?? null,
        'nama_petugas' => $nama_petugas,
        'nip' => $nip,
        'jenis_bayar' => $rincian['nama_jenis_bayar'] ?? ''
      ]
    );

    // Inject nama-nama berdasarkan ID
    $data['dokter_visite_1_nama'] = DokterModel::getNamaById($data['dokter_visite_1_id'] ?? null, $conn);
    $data['dokter_visite_2_nama'] = DokterModel::getNamaById($data['dokter_visite_2_id'] ?? null, $conn);
    $data['dokter_visite_3_nama'] = DokterModel::getNamaById($data['dokter_visite_3_id'] ?? null, $conn);

    $data['dokter_tindakan_1_nama'] = DokterModel::getNamaById($data['dokter_tindakan_1_id'] ?? null, $conn);
    $data['dokter_tindakan_2_nama'] = DokterModel::getNamaById($data['dokter_tindakan_2_id'] ?? null, $conn);
    $data['dokter_tindakan_3_nama'] = DokterModel::getNamaById($data['dokter_tindakan_3_id'] ?? null, $conn);

    $data['asisten_tindakan_1_nama'] = AsistenModel::getNamaById($data['asisten_tindakan_1_id'] ?? null, $conn);
    $data['asisten_tindakan_2_nama'] = AsistenModel::getNamaById($data['asisten_tindakan_2_id'] ?? null, $conn);
    $data['asisten_tindakan_3_nama'] = AsistenModel::getNamaById($data['asisten_tindakan_3_id'] ?? null, $conn);

    $data['instrumen_onlop_nama'] = AsistenModel::getNamaById($data['instrumen_onlop_id'] ?? null, $conn); // atau InstrumenModel

    $data['laboratorium_1_nama'] = LaboratoriumModel::getNamaById($data['laboratorium_1_id'] ?? null, $conn);
    $data['laboratorium_2_nama'] = LaboratoriumModel::getNamaById($data['laboratorium_2_id'] ?? null, $conn);

    $data['ambulance_1_nama'] = AmbulanceModel::getNamaById($data['ambulance_1_id'] ?? null, $conn);
    $data['ambulance_2_nama'] = AmbulanceModel::getNamaById($data['ambulance_2_id'] ?? null, $conn);

    $data['dokter_konsultasi_1_nama'] = DokterModel::getNamaById($data['dokter_konsultasi_1_id'] ?? null, $conn);
    $data['dokter_konsultasi_2_nama'] = DokterModel::getNamaById($data['dokter_konsultasi_2_id'] ?? null, $conn);
    $data['dokter_konsultasi_3_nama'] = DokterModel::getNamaById($data['dokter_konsultasi_3_id'] ?? null, $conn);

    include __DIR__ . '/../views/rincian_rawat_inap/cetak_rincian.php';
  }

  private static function redirectWithAlert(string $message, string $location): void {
    echo "<script>alert('$message'); window.location='$location';</script>";
    exit;
  }

  private static function getInstitusi(mysqli $conn): array {
    return $conn->query("SELECT nama_institusi, sub_institusi, alamat, telepon FROM institusi LIMIT 1")->fetch_assoc() ?? [];
  }

  private static function getPasienByRawatInap(mysqli $conn, int $rawat_inap_id): array {
    $stmt = $conn->prepare("
      SELECT p.nama_pasien, p.nomor_register
      FROM tiket_rawat_inap t
      JOIN pasien p ON p.id = t.pasien_id
      WHERE t.id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?? [];
  }

  private static function getPetugasKasir(mysqli $conn, int $rawat_inap_id): array {
    // Ambil data petugas dari kasir melalui rawat_inap_rincian
    $stmt1 = $conn->prepare("
      SELECT nama_petugas, nip, qr_filename
      FROM kasir
      ORDER BY id DESC
      LIMIT 1
    ");
    $stmt1->execute();
    $petugas = $stmt1->get_result()->fetch_assoc() ?? [];

    // Ambil tanggal pemeriksaan dari rincian
    $stmt2 = $conn->prepare("
      SELECT tanggal_pemeriksaan
      FROM rawat_inap_rincian
      WHERE rawat_inap_id = ?
      ORDER BY id ASC
      LIMIT 1
    ");
    $stmt2->bind_param("i", $rawat_inap_id);
    $stmt2->execute();
    $tanggal = $stmt2->get_result()->fetch_assoc() ?? [];

    return [
      'nama_petugas' => $petugas['nama_petugas'] ?? '-',
      'nip' => $petugas['nip'] ?? '-',
      'tanggal_pemeriksaan' => date('d-m-Y', strtotime($tanggal['tanggal_pemeriksaan'] ?? 'now')),
      'qr_filename' => $petugas['qr_filename'] ?? null
    ];
  }

  private static function resolveLabels(mysqli $conn, array $data): array {
    require_once __DIR__ . '/../models/master/JenisBayarModel.php';
    require_once __DIR__ . '/../models/master/KelasKamarModel.php';

    $jenis_bayar_list = JenisBayarModel::getAll($conn);
    $kelas_kamar_list = KelasKamarModel::getAll($conn);

    $jenis_bayar_id = $data['jenis_bayar_id'] ?? null;
    $kelas_kamar_id = $data['kelas_kamar_id'] ?? null;

    foreach ($jenis_bayar_list as $jb) {
      if ($jb['id'] == $jenis_bayar_id) {
        $data['nama_jenis_bayar'] = $jb['nama_jenis'];
        break;
      }
    }

    foreach ($kelas_kamar_list as $kk) {
      if ($kk['id'] == $kelas_kamar_id) {
        $data['nama_kelas_perawatan'] = $kk['nama_kelas'] . ' - ' . $kk['jenis_pasien'];
        break;
      }
    }

    return $data;
  }

  public static function toggle_status() {
    global $conn;
    $id = $_POST['id'] ?? null; // ini harus tiket_rawat_inap.id
    $status = $_POST['status'] ?? null;

    if ($id && in_array($status, ['AKTIF', 'SELESAI'])) {
      $stmt = $conn->prepare("UPDATE tiket_rawat_inap SET status = ? WHERE id = ?");
      $stmt->bind_param("si", $status, $id);
      $stmt->execute();
    }

    exit;
  }

  public static function ajaxSearch(mysqli $conn): void {
      $keyword = $_GET['q'] ?? '';
      $keyword = trim($keyword);

      // Ambil data sesuai keyword, misalnya 10 baris pertama
      $results = RincianRawatInapModel::getPaginated($conn, 10, 0, $keyword);

      if (!empty($results)) {
          foreach ($results as $no => $row) {
              echo "<tr>
                      <td>".($no+1)."</td>
                      <td>".htmlspecialchars($row['nama_pasien'] ?? '-')." / ".htmlspecialchars($row['nomor_register'] ?? '-')."</td>
                      <td>".htmlspecialchars($row['tanggal_masuk'] ?? '-')."</td>
                      <td>Rp ".number_format(floatval($row['total_semua_bagian'] ?? 0),0,',','.')."</td>
                      <td>Rp ".number_format(floatval($row['uang_muka'] ?? 0),0,',','.')."</td>
                      <td>Rp ".number_format(floatval($row['potongan'] ?? 0),0,',','.')."</td>
                      <td>Rp ".number_format(floatval($row['sisa_tagihan'] ?? 0),0,',','.')."</td>
                      <td>{$row['status']}</td>
                      <td class='text-center'>
                        <a href='index.php?page=rincian_rawat_inap&action=edit&id={$row['rawat_inap_id']}' class='btn btn-sm btn-warning me-1'>
                          <i class='fas fa-edit'></i>
                        </a>
                        <a href='index.php?page=rincian_rawat_inap&action=cetak_rincian&id={$row['rawat_inap_id']}' class='btn btn-sm btn-info' target='_blank'>
                          <i class='fas fa-print'></i>
                        </a>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='9' class='text-center text-muted'>Tidak ada hasil</td></tr>";
      }
      exit;
  }

}
?>