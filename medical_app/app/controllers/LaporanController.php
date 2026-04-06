<?php
include_once __DIR__ . '/../models/input/DokterModel.php';
include_once __DIR__ . '/../models/laporan/RekapDokterModel.php';

class LaporanController {
  private $conn;

  public function __construct($conn) {
    $this->conn = $conn;
  }

  public function rekapDokter() {
    $dokterModel = new DokterModel();
    $rekapModel = new RekapDokterModel();

    $list_dokter = $dokterModel->getAll();

    $dokter_id = $_GET['dokter_id'] ?? '';
    $bulan     = $_GET['bulan'] ?? '';
    $tahun     = $_GET['tahun'] ?? '';

    $rekap = [];
    if ($dokter_id !== '' && $bulan !== '' && $tahun !== '') {
      $rekap = $rekapModel->getRekapDokter($dokter_id, $bulan, $tahun);
    }

    include 'app/views/laporan/rekap_dokter.php';
  }

  public function cetakRekapDokter() {
      // Load model
      $dokterModel = new DokterModel();
      $rekapModel  = new RekapDokterModel();

      // Ambil parameter
      $page = trim($_GET['page'] ?? '');
      $dokter_id = $_GET['dokter_id'] ?? '';
      $bulan     = $_GET['bulan'] ?? '';
      $tahun     = $_GET['tahun'] ?? '';
   
      // Validasi parameter
      if (!$dokter_id || !$bulan || !$tahun) {
          echo "<p class='text-center'>Parameter tidak lengkap.</p>";
          exit;
      }

      // Ambil data dokter
      $namaDokter = trim($_GET['nama_dokter'] ?? '-');

      // Ambil data rekap
      $rekap = $rekapModel->getRekapDokter($dokter_id, $bulan, $tahun);

      // Ambil data petugas dari rekap
      $petugas_id  = $rekap[0]['dibuat_oleh'] ?? 0;
      $nipPetugas  = $rekap[0]['nip'] ?? '-';
      $qrPath      = $rekap[0]['qr_path'] ?? null;
      $tglRekap    = !empty($rekap[0]['tanggal_pemeriksaan']) ? date('d-m-Y', strtotime($rekap[0]['tanggal_pemeriksaan'])) : '-';

      // Ambil nama petugas
      $namaPetugas = $rekap[0]['nama_petugas'] ?? '-';
      $nipPetugas  = $rekap[0]['nip'] ?? '-';
      $qrPath      = $rekap[0]['qr_path'] ?? null;

      // Hitung total honor
      $total = $rekapModel->getTotalDokter($dokter_id, $bulan, $tahun);

      // Kirim ke view
      $data = [
        'nama_dokter'    => $namaDokter,
        'nama_petugas'   => $namaPetugas,
        'nip'            => $nipPetugas,
        'qr_path'        => $qrPath,
        'tanggal_rekap'  => $tglRekap,
        'rekap'          => $rekap,
        'total_honor'    => $total
      ];

      include 'app/views/laporan/cetak_rekap_dokter.php';
  }

  public function rekapAsisten() {
    include_once 'app/models/input/AsistenModel.php';
    include_once 'app/models/laporan/RekapAsistenModel.php';

    $asistenModel = new AsistenModel($this->conn);
    $rekapModel   = new RekapAsistenModel($this->conn);

    $list_asisten = $asistenModel->getAll();

    $asisten_id = $_GET['asisten_id'] ?? '';
    $bulan      = $_GET['bulan'] ?? '';
    $tahun      = $_GET['tahun'] ?? '';

    $rekap = [];
    if ($asisten_id !== '' && $bulan !== '' && $tahun !== '') {
      $rekap = $rekapModel->getRekapAsisten($asisten_id, $bulan, $tahun);
    }

    $data = [
      'list_asisten' => $list_asisten,
      'rekap'        => $rekap,
      'asisten_id'   => $asisten_id,
      'bulan'        => $bulan,
      'tahun'        => $tahun
    ];

    extract($data);
    include 'app/views/laporan/rekap_asisten.php';
  }  

  public function cetakRekapAsisten() {
      include_once 'app/models/laporan/RekapAsistenModel.php';
      $rekapModel = new RekapAsistenModel($this->conn);

      // Ambil parameter
      $page        = trim($_GET['page'] ?? '');
      $asisten_id  = $_GET['asisten_id'] ?? '';
      $bulan       = $_GET['bulan'] ?? '';
      $tahun       = $_GET['tahun'] ?? '';
      $namaAsisten = trim($_GET['nama_asisten'] ?? '-');

      // Validasi parameter
      if (!$asisten_id || !$bulan || !$tahun) {
          echo "<p class='text-center'>Parameter tidak lengkap.</p>";
          exit;
      }

      // Ambil data rekap
      $rekap = $rekapModel->getRekapAsisten($asisten_id, $bulan, $tahun);

      // Ambil data petugas dari rekap
      $nipPetugas  = $rekap[0]['nip'] ?? '-';
      $qrPath      = !empty($rekap[0]['qr_filename']) ? '/medical_app/public/assets/qr/' . $rekap[0]['qr_filename'] : null;
      $namaPetugas = $rekap[0]['nama_petugas'] ?? '-';
      $tglRekap    = !empty($rekap[0]['tanggal_pemeriksaan']) ? date('d-m-Y', strtotime($rekap[0]['tanggal_pemeriksaan'])) : '-';

      // Hitung total honor
      $total = $rekapModel->getTotalAsisten($asisten_id, $bulan, $tahun);

      // Kirim ke view
      $data = [
        'nama_asisten'   => $namaAsisten,
        'nama_petugas'   => $namaPetugas,
        'nip'            => $nipPetugas,
        'qr_path'        => $qrPath,
        'tanggal_rekap'  => $tglRekap,
        'rekap'          => $rekap,
        'total_honor'    => $total
      ];

      include 'app/views/laporan/cetak_rekap_asisten.php';
  }

  public function jurnalInput() {
    include_once __DIR__ . '/../models/laporan/JurnalModel.php';
    $model = new JurnalModel($this->conn);

    // Handle input jurnal baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['aksi'] ?? '') === 'tambah') {
      $tanggal    = $_POST['tanggal'] ?? '';
      $shift_id   = $_POST['shift_id'] ?? '';
      $keterangan = trim($_POST['keterangan'] ?? '');
      $debet      = (float) ($_POST['debet'] ?? 0);
      $kredit     = (float) ($_POST['kredit'] ?? 0);
      $kasir_id   = $_POST['kasir_id'] ?? '';

      if ($tanggal && $shift_id && $keterangan !== '' && $kasir_id) {
        $model->insert($tanggal, $shift_id, $keterangan, $debet, $kredit, $kasir_id);
        header("Location: index.php?page=jurnal_input&shift_id=$shift_id&tanggal=$tanggal&sukses=1");
        exit;
      }
    }

    // Ambil filter
    $tanggal   = $_GET['tanggal'] ?? date('Y-m-d');
    $shift_id  = $_GET['shift_id'] ?? '';

    // Ambil data
    $list_shift = $model->getAllShift();
    $list_kasir = $model->getAllKasir();
    $jurnal     = ($shift_id !== '') ? $model->getByTanggalShift($tanggal, $shift_id) : [];
    $total      = ($shift_id !== '') ? $model->getTotalByTanggalShift($tanggal, $shift_id) : [];
    $kasir_id   = $_SESSION['user_id'] ?? '';
    $nama_petugas = $_SESSION['nama'] ?? '-';

    $jurnal = ($shift_id !== '') ? $model->getByTanggalShift($tanggal, $shift_id) : [];

    // Kirim ke view
    $data = [
      'list_shift' => $list_shift,
      'list_kasir' => $list_kasir,
      'jurnal'     => $jurnal,
      'total'      => $total,
      'tanggal'    => $tanggal,
      'shift_id'   => $shift_id,
      'kasir_id'   => $kasir_id,
      'nama_petugas' => $nama_petugas
    ];

    extract($data);
    include __DIR__ . '/../views/laporan/jurnal_input.php';
  }

  public function jurnalHistory() {
    include_once __DIR__ . '/../models/laporan/JurnalModel.php';
    $model = new JurnalModel($this->conn);

    // Ambil tanggal filter
    $tanggal = $_GET['tanggal'] ?? '';
    if (empty($tanggal)) {
      $list_shift = [];
      $saldo_per_shift = [];
      $petugas_per_shift = [];
      $petugas_qr_per_shift = [];
    }

    // Ambil semua shift
    $list_shift = $model->getAllShift();
    $saldo_per_shift = [];
    $petugas_per_shift = [];
    $petugas_qr_per_shift = [];

    foreach ($list_shift as $shift) {
      $shift_id = $shift['id'];

      // Ambil saldo akhir per shift
      $total = $model->getTotalByTanggalShift($tanggal, $shift_id);
      $saldo_per_shift[$shift_id] = $total['saldo'] ?? 0;

      // Ambil list nama petugas (array nama)
      $petugas_per_shift[$shift_id] = $model->getListPetugasByTanggalShift($tanggal, $shift_id);

      // Ambil detail petugas (nama + NIP + QR)
      $petugas_detail = $model->getPetugasDetailByTanggalShift($tanggal, $shift_id);
      foreach ($petugas_detail as &$p) {
        $p['qrPath'] = "/medical_app/public/qr_generator.php?text=" . urlencode($p['nama_petugas']);
      }
      unset($p);
      $petugas_qr_per_shift[$shift_id] = $petugas_detail;
    }

    // Kirim ke view
    $data = [
      'list_shift'            => $list_shift,
      'saldo_per_shift'       => $saldo_per_shift,
      'petugas_per_shift'     => $petugas_per_shift,
      'petugas_qr_per_shift'  => $petugas_qr_per_shift,
      'tanggal'               => $tanggal
    ];

    extract($data);
    include __DIR__ . '/../views/laporan/jurnal_history.php';
  }
  public function updateJurnal() {
    include_once(__DIR__ . '/../models/laporan/JurnalModel.php');
    $model = new JurnalModel($this->conn);

    $id         = $_POST['id'] ?? '';
    $tanggal    = $_POST['tanggal'] ?? '';
    $shift_id   = $_POST['shift_id'] ?? '';
    $kasir_id   = $_POST['kasir_id'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $debet      = (float) ($_POST['debet'] ?? 0);
    $kredit     = (float) ($_POST['kredit'] ?? 0);

    // Validasi entri
    $jurnal = $model->getById($id);
    if (!$jurnal || $jurnal['kasir_id'] != $kasir_id) {
      echo "<div class='alert alert-danger'>Gagal update: Anda tidak berhak mengedit entri ini.</div>";
      return;
    }

    // Update ke DB
    $model->update($id, $keterangan, $debet, $kredit);

    // Redirect kembali ke jurnal_input
    header("Location: index.php?page=jurnal_input&tanggal=$tanggal&shift_id=$shift_id");
    exit;
  }

}