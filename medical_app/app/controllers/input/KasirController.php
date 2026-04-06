<?php
require_once __DIR__ . '/../../models/input/KasirModel.php';
require_once __DIR__ . '/../../helpers/QRCodeHelper.php';

class KasirController {
  public static function index() {
      global $conn;

      // Ambil halaman aktif dari URL (default 1)
      $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
      if ($page < 1) $page = 1;

      $limit  = 10; // jumlah baris per halaman
      $offset = ($page - 1) * $limit;
      $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

      // Hitung total data sesuai pencarian
      $totalData = KasirModel::countAll($conn, $search);
      $totalPage = ceil($totalData / $limit);
      if ($totalPage < 1) $totalPage = 1;

      // Ambil data sesuai halaman + pencarian
      $kasir = KasirModel::getPaginated($conn, $limit, $offset, $search);

      // Siapkan variabel untuk view
      $pagination = [
          'page'      => $page,
          'totalPage' => $totalPage,
          'search'    => $search
      ];

      $data = [
          'kasir'      => $kasir,
          'pagination' => $pagination,
          'offset'     => $offset
      ];

      include __DIR__ . '/../../views/input/kasir/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/input/kasir/create.php';
  }

  public static function store() {
    global $conn;

    $nama       = trim($_POST['nama_petugas'] ?? '');
    $nip        = trim($_POST['nip'] ?? '');
    $jabatan_id = $_POST['jabatan_id'] ?? null;
    $user_id    = $_SESSION['user_id'] ?? null;

    if (empty($nama) || empty($nip) || empty($jabatan_id)) {
      $_SESSION['error'] = 'Nama, NIP, dan Jabatan wajib diisi.';
      header('Location: index.php?page=kasir&action=create');
      exit;
    }

    if (KasirModel::createKasir($user_id, $nama, $nip, $jabatan_id, $conn)) {
      $id_kasir = KasirModel::getLastInsertedId($conn);
      self::generateQRKasir($conn, $id_kasir);
      $_SESSION['success'] = 'Data petugas berhasil ditambahkan!';
    } else {
      $_SESSION['error'] = 'Gagal menambahkan data petugas.';
    }

    header('Location: index.php?page=kasir');
    exit;
  }

  public static function edit() {
    global $conn;
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
      echo 'ID tidak valid';
      exit;
    }

    $data = KasirModel::find((int)$id, $conn);
    if (!$data) {
      echo 'Data tidak ditemukan';
      exit;
    }

    $role = strtolower(trim($_SESSION['role'] ?? ''));
    $userId = $_SESSION['user_id'] ?? null;
    $isSuperadmin = $role === 'superadmin';
    $canEdit = $isSuperadmin || ($userId == $data['user_id']);

    if (!$canEdit) {
      echo 'Akses ditolak';
      exit;
    }

    include __DIR__ . '/../../views/input/kasir/edit.php';
  }

  public static function update() {
    global $conn;
    $id         = $_GET['id'] ?? null;
    $nama       = trim($_POST['nama_petugas'] ?? '');
    $nip        = trim($_POST['nip'] ?? '');
    $jabatan_id = $_POST['jabatan_id'] ?? null;
    $status     = $_POST['status'] ?? null;

    if (!$id || empty($nama) || empty($nip) || empty($jabatan_id)) {
      $_SESSION['error'] = 'Data tidak lengkap.';
      header('Location: index.php?page=kasir');
      exit;
    }

    if (KasirModel::update((int)$id, $nama, $nip, $jabatan_id, $status, $conn)) {
      self::generateQRKasir($conn, $id);
      $_SESSION['success'] = 'Data petugas berhasil diperbarui!';
    } else {
      $_SESSION['error'] = 'Gagal memperbarui data.';
    }

    header('Location: index.php?page=kasir');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
      $_SESSION['error'] = 'ID tidak valid.';
      header('Location: index.php?page=kasir');
      exit;
    }

    if (KasirModel::delete((int)$id, $conn)) {
      $_SESSION['success'] = 'Data petugas berhasil dihapus!';
    } else {
      $_SESSION['error'] = 'Gagal menghapus data.';
    }

    header('Location: index.php?page=kasir');
    exit;
  }

  public static function toggleStatus() {
    global $conn;
    $id = $_POST['id'] ?? 0;

    $kasir = KasirModel::find((int)$id, $conn);
    if ($kasir) {
      $newStatus = (strtolower($kasir['status']) === 'aktif') ? 'Nonaktif' : 'Aktif';
      KasirModel::updateStatus($id, $newStatus, $conn);
    }

    header("Location: index.php?page=kasir");
    exit;
  }

  public static function generateQRKasir($conn, $id_kasir) {
    $kasir = KasirModel::find((int)$id_kasir, $conn);
    if (!$kasir) {
      error_log("❌ QR gagal: Kasir dengan ID $id_kasir tidak ditemukan.");
      return;
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $kasir['user_id']) {
      error_log("⛔ QR gagal: User login tidak cocok dengan kasir ID $id_kasir.");
      return;
    }

    if (empty($kasir['nama_petugas']) || empty($kasir['nip'])) {
      error_log("⚠️ QR gagal: Data tidak lengkap untuk kasir ID $id_kasir.");
      return;
    }

    $filename = QRCodeHelper::generate(trim($kasir['nip']), trim($kasir['nama_petugas']));
    KasirModel::updateQR($id_kasir, $filename, $conn);
    error_log("🔄 QR diperbarui untuk kasir ID $id_kasir → Filename: $filename");
  }

  public static function ajaxSearch() {
      global $conn;

      $keyword = $_GET['q'] ?? '';
      // Ambil hasil pencarian tanpa pagination (atau bisa pakai limit kecil)
      $results = KasirModel::getPaginated($conn, 10, 0, $keyword);

      $no = 1;
      foreach ($results as $row) {
          $rowStatus = strtolower(trim($row['status'] ?? 'nonaktif'));
          $btnClass  = $rowStatus === 'aktif' ? 'success' : 'secondary';

          echo "<tr>
                  <td>{$no}</td>
                  <td>".htmlspecialchars($row['nama_petugas'])."</td>
                  <td>".htmlspecialchars($row['nip'])."</td>
                  <td>".htmlspecialchars($row['nama_jabatan'])."</td>
                  <td><span class='badge badge-{$btnClass}'>".ucfirst($rowStatus)."</span></td>
                  <td>".(!empty($row['qr_filename']) 
                          ? "<img src='/medical_app/public/qrcode/".htmlspecialchars($row['qr_filename'])."' width='60'>" 
                          : "<span class='text-muted'>(belum tersedia)</span>")."</td>
                  <td><a href='index.php?page=kasir&action=edit&id={$row['id']}' class='btn btn-sm btn-info'><i class='fas fa-edit'></i></a></td>
                </tr>";
          $no++;
      }

      if (empty($results)) {
          echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada hasil</td></tr>";
      }

      exit; // ⛔ hentikan agar header/footer tidak ikut
  }
  
}