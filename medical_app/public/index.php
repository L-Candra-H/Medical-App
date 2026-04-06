<?php
// ✅ Inisialisasi Session + Database
session_start();
require_once __DIR__ . '/../config/database.php';

// ✅ Routing AJAX Pencarian Pasien (harus paling atas!)
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'pasien' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/input/PasienController.php';
  PasienController::ajax_search();
  exit;
}

// ✅ Routing AJAX Pencarian Kasir (harus paling atas!)
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'kasir' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/input/KasirController.php';
  KasirController::ajaxSearch();
  exit;
}

// ✅ Routing AJAX Pencarian Dokter (harus paling atas!)
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'dokter' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/input/DokterController.php';
  DokterController::ajaxSearch();
  exit;
}

// ✅ Routing AJAX Pencarian Asisten (harus paling atas!)
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'asisten' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/input/AsistenController.php';
  AsistenController::ajaxSearch();
  exit;
}

// ✅ Routing AJAX Pencarian Tiket Rawat Inap (paling atas sebelum router utama)
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'tiket_rawat_inap' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/TiketRawatInapController.php';
  // Asumsikan koneksi mysqli ada di $conn
  $controller = new TiketRawatInapController($conn);
  $controller->ajaxSearch();
  exit;
}

// ✅ Routing AJAX Pencarian Uang Muka
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'uang_muka' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/UangMukaController.php';
  UangMukaController::ajaxSearch($conn); // panggil method ajaxSearch
  exit;
}

// ✅ Routing AJAX Pencarian Potongan
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'potongan' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/PotonganController.php';
  PotonganController::ajaxSearch($conn); // panggil method ajaxSearch
  exit;
}

// ✅ Routing AJAX Pencarian Rincian Rawat Inap
if (
  isset($_GET['page'], $_GET['action']) &&
  $_GET['page'] === 'rincian_rawat_inap' &&
  $_GET['action'] === 'ajax_search'
) {
  require_once __DIR__ . '/../app/controllers/RincianRawatInapController.php';
  RincianRawatInapController::ajaxSearch($conn);
  exit;
}

// ✅ Error log aktif
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Routing utama aplikasi
$page   = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($page) {
  case 'home':
    include __DIR__ . '/../app/views/home/landing.php';
    break;

  case 'login':
    include __DIR__ . '/../app/views/auth/login.php';
    break;

  case 'reset_password':
    include __DIR__ . '/../app/views/auth/reset_password.php';
    break;

  case 'auth':
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    if ($action === 'login') {
      ($_SERVER['REQUEST_METHOD'] === 'POST')
        ? AuthController::login()
        : include __DIR__ . '/../app/views/auth/login.php';
    }
    elseif ($action === 'register') {
      ($_SERVER['REQUEST_METHOD'] === 'POST')
        ? AuthController::register()
        : include __DIR__ . '/../app/views/auth/register.php';
    }
    elseif ($action === 'reset') {
      ($_SERVER['REQUEST_METHOD'] === 'POST')
        ? AuthController::reset()
        : include __DIR__ . '/../app/views/auth/reset_password.php';
    }

    else {
      echo "<h2>404 - Aksi otentikasi tidak dikenali</h2>";
    }
    break;

  case 'institusi':
    require_once __DIR__ . '/../app/controllers/InstitusiController.php';
    if ($action === 'create') InstitusiController::create($conn);
    elseif ($action === 'store') InstitusiController::store($conn, $_POST);
    elseif ($action === 'update') InstitusiController::update($conn, $_GET['id'], $_POST);
    elseif ($action === 'edit') InstitusiController::edit($conn, $_GET['id']);
    elseif ($action === 'delete') InstitusiController::delete($conn, $_GET['id']);
    else InstitusiController::index($conn);
    break;

  case 'profil':
    require_once __DIR__ . '/../app/controllers/ProfilController.php';
    global $conn;
    if ($action === 'update') {
      Profil::update($conn);
    } else {
      Profil::index($conn); // ✅ ini aja cukup
    }
    break;

  case 'dashboard':
    include __DIR__ . '/../app/views/dashboard/dashboard.php';
    break;

  case 'jenis_bayar':
    require_once __DIR__ . '/../app/controllers/master/JenisBayarController.php';
    if     ($action === 'create')   JenisBayarController::create();
    elseif ($action === 'store')    JenisBayarController::store();
    elseif ($action === 'edit')     JenisBayarController::edit();
    elseif ($action === 'update')   JenisBayarController::update();
    elseif ($action === 'destroy')  JenisBayarController::destroy();
    else                             JenisBayarController::index();
    break;

  case 'kelas_kamar':
    require_once __DIR__ . '/../app/controllers/master/KelasKamarController.php';
    if     ($action === 'create')   KelasKamarController::create();
    elseif ($action === 'store')    KelasKamarController::store();
    elseif ($action === 'edit')     KelasKamarController::edit();
    elseif ($action === 'update')   KelasKamarController::update();
    elseif ($action === 'destroy')  KelasKamarController::destroy();
    else                            KelasKamarController::index();
    break;

  case 'ambulance':
    require_once __DIR__ . '/../app/controllers/master/AmbulanceController.php';
    if     ($action === 'create')   AmbulanceController::create();
    elseif ($action === 'store')    AmbulanceController::store();
    elseif ($action === 'edit')     AmbulanceController::edit();
    elseif ($action === 'update')   AmbulanceController::update();
    elseif ($action === 'destroy')  AmbulanceController::destroy();
    else                             AmbulanceController::index();
    break;

  case 'laboratorium':
    require_once __DIR__ . '/../app/controllers/master/LaboratoriumController.php';
    if     ($action === 'create')   LaboratoriumController::create();
    elseif ($action === 'store')    LaboratoriumController::store();
    elseif ($action === 'edit')     LaboratoriumController::edit();
    elseif ($action === 'update')   LaboratoriumController::update();
    elseif ($action === 'destroy')  LaboratoriumController::destroy();
    else                             LaboratoriumController::index();
    break;

  case 'kasir':
    require_once __DIR__ . '/../app/controllers/input/KasirController.php';
    if     ($action === 'create')   KasirController::create();
    elseif ($action === 'store')    KasirController::store();
    elseif ($action === 'edit')     KasirController::edit();
    elseif ($action === 'update')   KasirController::update();
    elseif ($action === 'destroy')  KasirController::destroy();
    elseif ($action === 'toggle_status') KasirController::toggleStatus($conn); // ✅ Tambah ini!
    else                             KasirController::index();
    break;

  case 'dokter':
    require_once __DIR__ . '/../app/controllers/input/DokterController.php';
    if     ($action === 'create')   DokterController::create();
    elseif ($action === 'store')    DokterController::store();
    elseif ($action === 'edit')     DokterController::edit();
    elseif ($action === 'update')   DokterController::update();
    elseif ($action === 'destroy')  DokterController::destroy();
    else                            DokterController::index();
    break;

  case 'asisten':
    require_once __DIR__ . '/../app/controllers/input/AsistenController.php';
    if     ($action === 'create')   AsistenController::create();
    elseif ($action === 'store')    AsistenController::store();
    elseif ($action === 'edit')     AsistenController::edit();
    elseif ($action === 'update')   AsistenController::update();
    elseif ($action === 'destroy')  AsistenController::destroy();
    else                            AsistenController::index();
    break;

  case 'pasien':
    require_once __DIR__ . '/../app/controllers/input/PasienController.php';
    if     ($action === 'create')   PasienController::create();
    elseif ($action === 'store')    PasienController::store();
    elseif ($action === 'edit')     PasienController::edit($_GET['id'] ?? null);
    elseif ($action === 'update')   PasienController::update();
    elseif ($action === 'destroy')  PasienController::destroy();
    else                            PasienController::index();
    break;

  case 'jabatan':
    require_once __DIR__ . '/../app/controllers/master/JabatanController.php';
    if     ($action === 'create')   JabatanController::create();
    elseif ($action === 'store')    JabatanController::store();
    elseif ($action === 'edit')     JabatanController::edit($_GET['id']);
    elseif ($action === 'update')   JabatanController::update($_GET['id']);
    elseif ($action === 'destroy')  JabatanController::destroy($_GET['id']);
    else                            JabatanController::index();
    break;

  case 'tiket_rawat_inap':
    require_once __DIR__ . '/../app/controllers/TiketRawatInapController.php';
    $controller = new TiketRawatInapController($conn);

    switch ($action) {
      case 'create':
        $pasien_id = isset($_GET['pasien_id']) ? (int) $_GET['pasien_id'] : null;

        if ($pasien_id) {
          // Mode otomatis dari menu pasien
          $stmt = $conn->prepare("SELECT * FROM pasien WHERE id = ?");
          $stmt->bind_param("i", $pasien_id);
          $stmt->execute();
          $result = $stmt->get_result();
          $pasien = $result->fetch_assoc();
        } else {
          // Mode manual dari menu tiket
         $pasienTanpaTiket = $controller->getPasienTanpaTiket();
        }

        $statusOptions = $controller->getStatusOptions();
        include __DIR__ . '/../app/views/tiket_rawat_inap/create.php';
        break;

      case 'edit':
        $id = (int) ($_GET['id'] ?? 0);
        $result = $controller->show($id);
        $tiket = $controller->show($id);
        include __DIR__ . '/../app/views/tiket_rawat_inap/edit.php';
        break;

      case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $controller->store($_POST);
          header('Location: index.php?page=tiket_rawat_inap');
          exit;
        }
        break;

      case 'update':
        $id = (int) ($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $controller->update($id, $_POST);
          header('Location: index.php?page=tiket_rawat_inap');
          exit;
        }
        break;

      case 'delete':
        $id = (int) ($_GET['id'] ?? 0);
        $controller->destroy($id);
        header('Location: index.php?page=tiket_rawat_inap');
        exit;
        break;

      default:
        $controller->index(); // sudah include view
        break;
    }
    break;

  case 'uang_muka':
    require_once __DIR__ . '/../app/controllers/UangMukaController.php';
    switch ($action) {
      case 'index':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
          UangMukaController::index($conn); // tampilkan riwayat uang muka berdasarkan tiket
        } else {
          $_SESSION['error'] = 'Tiket tidak ditemukan.';
          header('Location: index.php?page=pasien');
          exit;
        }
        break;
      case 'admin':
        UangMukaController::adminView($conn);
        break;
      case 'input':
        UangMukaController::inputForm($conn, $_GET['id'] ?? null);
        break;
      case 'store':
        UangMukaController::store($conn); // simpan data
        break;
      default:
        UangMukaController::index($conn); // akses via pasien (butuh ?id=)
        break;
      case 'edit':
        UangMukaController::editForm($conn, $_GET['id']); // tampil form edit dengan ID tertentu
        break;
      case 'update':
        UangMukaController::update($conn, $_GET['id']);
        break;
      case 'cetak':
        UangMukaController::cetak($conn, $_GET['id']);
        break;
    }
    break;

  case 'potongan':
    require_once __DIR__ . '/../app/controllers/PotonganController.php';

    switch ($action) {
      case 'listAll':
        PotonganController::listAll($conn); // Semua potongan lintas pasien
        break;

      case 'create':
        PotonganController::inputForm($conn); // Form input potongan baru
        break;

      case 'inputForm':
        PotonganController::inputForm($conn);
        break;

      case 'store':
        PotonganController::store($conn); // Simpan potongan baru
        break;

      case 'edit':
        PotonganController::editForm($conn, $_GET['id']); // Form edit potongan
        break;

      case 'update':
        PotonganController::update($conn, $_GET['id']); // Simpan perubahan
        break;

      case 'cetak':
        PotonganController::cetak($conn, $_GET['id']); // Cetak kuitansi
        break;

      default:
        PotonganController::index($conn); // Daftar potongan untuk 1 pasien
        break;
    }
    break;

  case 'rincian_rawat_inap':
    require_once __DIR__ . '/../app/controllers/RincianRawatInapController.php';

    switch ($action) {
      case 'create':
        RincianRawatInapController::create($conn);
        break;

      case 'store':
        RincianRawatInapController::store($conn, $_GET['id']);
        break;

      case 'edit':
        RincianRawatInapController::edit($conn, $_GET['id']);
        break;

      case 'update':
        RincianRawatInapController::update($conn);
        break;  

      case 'selesaikan_ajax':
        $id = $_GET['id'] ?? 0;
        RincianRawatInapController::selesaikan_ajax($conn, (int)$id);
        break;

      case 'cetak_kuitansi':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
          RincianRawatInapController::cetak_kuitansi($conn, $id);
        } else {
          http_response_code(400);
         echo "<h1>400 - ID rawat inap tidak valid</h1>";
        }
        break;

      case 'cetak_rincian':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
          RincianRawatInapController::cetakRincian($conn, $id);
        } else {
          http_response_code(400);
          echo "<h1>400 - ID rawat inap tidak valid</h1>";
        }
        break;

      default:
        RincianRawatInapController::index($conn);
        break;
    }
    break;

  case 'rekap_dokter':
    // Include model
    include_once __DIR__ . '/../app/models/input/DokterModel.php';
    include_once __DIR__ . '/../app/models/laporan/RekapDokterModel.php';

    // Ambil koneksi database
    $conn = include(__DIR__ . '/../config/database.php');

    // Inisialisasi model dengan koneksi
    $dokterModel = new DokterModel($conn);
    $rekapModel  = new RekapDokterModel($conn);

    // Ambil daftar dokter untuk dropdown
    $conn = include(__DIR__ . '/../config/database.php');
    $list_dokter = DokterModel::getAll($conn); // ✅ kirim koneksi

    // Ambil filter dari GET
    $filter = [
      'dokter_id' => $_GET['dokter_id'] ?? '',
      'bulan'     => $_GET['bulan'] ?? '',
      'tahun'     => $_GET['tahun'] ?? ''
    ];

    // Siapkan data rekap jika filter lengkap
    $rekap = [];
    if ($filter['dokter_id'] && $filter['bulan'] && $filter['tahun']) {
      $rekap = $rekapModel->getRekapDokter($filter['dokter_id'], $filter['bulan'], $filter['tahun']);
    }

    // Kirim ke view
    include __DIR__ . '/../app/views/laporan/rekap_dokter.php';
    break;

  case 'rekap_asisten':
    // Include model
    include_once __DIR__ . '/../app/models/input/AsistenModel.php';
    include_once __DIR__ . '/../app/models/laporan/RekapAsistenModel.php';

    // Ambil koneksi database
    $conn = include(__DIR__ . '/../config/database.php');

    // Inisialisasi model
    $asistenModel = new AsistenModel($conn);
    $rekapModel   = new RekapAsistenModel($conn);

    // Ambil daftar asisten
    $list_asisten = AsistenModel::getAll($conn);

    // Ambil filter dari GET
    $filter = [
      'asisten_id' => $_GET['asisten_id'] ?? '',
      'bulan'      => $_GET['bulan'] ?? '',
      'tahun'      => $_GET['tahun'] ?? ''
    ];

    // Siapkan data rekap jika filter lengkap
    $rekap = [];
    if ($filter['asisten_id'] && $filter['bulan'] && $filter['tahun']) {
      $rekap = $rekapModel->getRekapAsisten($filter['asisten_id'], $filter['bulan'], $filter['tahun']);
    }

    // Kirim ke view
    include __DIR__ . '/../app/views/laporan/rekap_asisten.php';
    break;

  case 'jurnal_input':
    include_once __DIR__ . '/../app/controllers/LaporanController.php';
    $controller = new LaporanController($conn);
    $controller->jurnalInput();
    break;

  case 'update_jurnal':
    include_once __DIR__ . '/../app/controllers/LaporanController.php';
    $controller = new LaporanController($conn);
    $controller->updateJurnal();
    break;

  case 'edit_jurnal':
    include_once __DIR__ . '/../app/views/laporan/edit_jurnal.php';
    break;

  case 'jurnal_history':
    include_once __DIR__ . '/../app/controllers/LaporanController.php';
    $controller = new LaporanController($conn);
    $controller->jurnalHistory();
    break;  

  case 'cetak_rekap_dokter':
    include __DIR__ . '/../app/views/laporan/cetak_rekap_dokter.php';
    break;

  case 'cetak_rekap_asisten':
    include __DIR__ . '/../app/views/laporan/cetak_rekap_asisten.php';
    break;

  case 'cetak_jurnal_harian':
    include __DIR__ . '/../app/views/laporan/cetak_jurnal_harian.php';
    break;    

  default:
    echo "<h2>404 - Halaman tidak ditemukan</h2>";
    break;
}
