<?php
require_once __DIR__ . '/../../models/input/DokterModel.php';

class DokterController {
  public static function index() {
      global $conn;

      // Ambil halaman aktif dari URL (default 1)
      $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
      if ($page < 1) $page = 1;

      $limit  = 10; // jumlah baris per halaman
      $offset = ($page - 1) * $limit;
      $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

      // Hitung total data sesuai pencarian
      $totalData = DokterModel::countAll($conn, $search);
      $totalPage = ceil($totalData / $limit);
      if ($totalPage < 1) $totalPage = 1;

      // Ambil data sesuai halaman + pencarian
      $dokter = DokterModel::getPaginated($conn, $limit, $offset, $search);

      // Siapkan variabel untuk view
      $pagination = [
          'page'      => $page,
          'totalPage' => $totalPage,
          'search'    => $search
      ];

      // Bungkus semua data ke array $data
      $data = [
          'dokter'     => $dokter,
          'pagination' => $pagination,
          'offset'     => $offset
      ];

      // Kirim ke view
      include __DIR__ . '/../../views/input/dokter/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/input/dokter/create.php';
  }

  public static function store() {
      global $conn;
      $nama       = trim($_POST['nama_dokter'] ?? '');
      $spesialis  = trim($_POST['spesialisasi'] ?? '');

      // Validasi input wajib
      if (empty($nama) || empty($spesialis)) {
          $_SESSION['error'] = 'Nama dan Spesialisasi wajib diisi.';
          header('Location: index.php?page=dokter&action=create');
          exit;
      }

      // Cek duplikasi spesialisasi
      if (DokterModel::existsSpesialisasi($conn, $spesialis)) {
          $_SESSION['error'] = 'Spesialisasi "' . htmlspecialchars($spesialis) . '" sudah ada. Gunakan nama lain.';
          header('Location: index.php?page=dokter&action=create');
          exit;
      }

      // Insert data baru
      if (DokterModel::insert($nama, $spesialis, $conn)) {
          $_SESSION['success'] = 'Data dokter berhasil ditambahkan!';
      } else {
          $_SESSION['error'] = 'Gagal menambahkan data dokter.';
      }

      header('Location: index.php?page=dokter');
      exit;
  }

  public static function edit() {
    global $conn;
    $id = $_GET['id'] ?? null;
    $data = DokterModel::find($id, $conn);
    include __DIR__ . '/../../views/input/dokter/edit.php';
  }

  public static function update() {
    global $conn;
    $id = $_GET['id'] ?? null;
    $nama = $_POST['nama_dokter'] ?? '';
    $spesialis = $_POST['spesialisasi'] ?? '';
    $status = $_POST['status'] ?? 'Aktif'; // default fallback
    DokterModel::update($id, $nama, $spesialis, $status, $conn);
    header('Location: index.php?page=dokter');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;
    DokterModel::delete($id, $conn);
    header('Location: index.php?page=dokter');
    exit;
  }

  public static function ajaxSearch() {
      global $conn;

      $keyword = $_GET['q'] ?? '';
      // Ambil hasil pencarian tanpa pagination (atau bisa pakai limit kecil)
      $results = DokterModel::getPaginated($conn, 10, 0, $keyword);

      $no = 1;
      foreach ($results as $row) {
          $rowStatus = strtolower(trim($row['status'] ?? 'nonaktif'));
          $btnClass  = $rowStatus === 'aktif' ? 'success' : 'secondary';

          echo "<tr>
                  <td>{$no}</td>
                  <td>".htmlspecialchars($row['nama_dokter'])."</td>
                  <td>".htmlspecialchars($row['spesialisasi'])."</td>
                  <td><span class='badge badge-{$btnClass}'>".ucfirst($rowStatus)."</span></td>
                  <td><a href='index.php?page=dokter&action=edit&id={$row['id']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a></td>
                </tr>";
          $no++;
      }

      if (empty($results)) {
          echo "<tr><td colspan='5' class='text-center text-muted'>Tidak ada hasil</td></tr>";
      }

      exit; // hentikan agar header/footer tidak ikut
  }

}
