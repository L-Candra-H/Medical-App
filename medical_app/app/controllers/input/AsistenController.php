<?php
require_once __DIR__ . '/../../models/input/AsistenModel.php';

class AsistenController {
  public static function index() {
      global $conn;

      // Ambil halaman aktif dari URL (default 1)
      $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
      if ($page < 1) $page = 1;

      $limit  = 10; // jumlah baris per halaman
      $offset = ($page - 1) * $limit;
      $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

      // Hitung total data sesuai pencarian
      $totalData = AsistenModel::countAll($conn, $search);
      $totalPage = ceil($totalData / $limit);
      if ($totalPage < 1) $totalPage = 1;

      // Ambil data sesuai halaman + pencarian
      $asisten = AsistenModel::getPaginated($conn, $limit, $offset, $search);

      // Siapkan variabel untuk view
      $pagination = [
          'page'      => $page,
          'totalPage' => $totalPage,
          'search'    => $search
      ];

      $data = [
          'asisten'    => $asisten,
          'pagination' => $pagination,
          'offset'     => $offset
      ];

      include __DIR__ . '/../../views/input/asisten/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/input/asisten/create.php';
  }

  public static function store() {
    global $conn;
    $nama       = $_POST['nama_asisten'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $status     = 'Aktif'; // default otomatis
    AsistenModel::insert($nama, $keterangan, $status, $conn);
    header('Location: index.php?page=asisten');
    exit;
  }

  public static function edit() {
    global $conn;
    $id   = $_GET['id'] ?? null;
    $data = AsistenModel::find($id, $conn);
    include __DIR__ . '/../../views/input/asisten/edit.php';
  }

  public static function update() {
    global $conn;
    $id        = $_GET['id'] ?? null;
    $nama      = $_POST['nama_asisten'] ?? '';
    $keterangan= $_POST['keterangan'] ?? '';
    $status    = $_POST['status'] ?? 'Aktif';
    AsistenModel::update($id, $nama, $keterangan, $status, $conn);
    header('Location: index.php?page=asisten');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;
    AsistenModel::delete($id, $conn);
    header('Location: index.php?page=asisten');
    exit;
  }

  public static function ajaxSearch() {
      global $conn;

      $keyword = $_GET['q'] ?? '';
      // Ambil hasil pencarian tanpa pagination (atau bisa pakai limit kecil)
      $results = AsistenModel::getPaginated($conn, 10, 0, $keyword);

      $no = 1;
      foreach ($results as $row) {
          $rowStatus = strtolower(trim($row['status'] ?? 'nonaktif'));
          $btnClass  = $rowStatus === 'aktif' ? 'success' : 'secondary';

          echo "<tr>
                  <td>{$no}</td>
                  <td>".htmlspecialchars($row['nama_asisten'])."</td>
                  <td>".htmlspecialchars($row['keterangan'])."</td>
                  <td><span class='badge badge-{$btnClass}'>".ucfirst($rowStatus)."</span></td>
                  <td><a href='index.php?page=asisten&action=edit&id={$row['id']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a></td>
                </tr>";
          $no++;
      }

      if (empty($results)) {
          echo "<tr><td colspan='5' class='text-center text-muted'>Tidak ada hasil</td></tr>";
      }

      exit; // hentikan agar header/footer tidak ikut
  }

}