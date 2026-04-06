<?php
require_once __DIR__ . '/../../models/master/KelasKamarModel.php';

class KelasKamarController {
  public static function index() {
    global $conn;

    $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
    if ($page < 1) $page = 1;

    $limit  = 6;
    $offset = ($page - 1) * $limit;
    $search = $_GET['q'] ?? '';

    // Hitung total data sesuai pencarian
    $totalData = KelasKamarModel::countBySearch($conn, $search);
    $totalPage = ceil($totalData / $limit);
    if ($totalPage < 1) $totalPage = 1;

    // Ambil data sesuai halaman + pencarian
    $kelas = KelasKamarModel::getPaginated($conn, $limit, $offset, $search);

    $pagination = [
      'page'      => $page,
      'totalPage' => $totalPage,
      'search'    => $search
    ];

    $data = [
      'kelas'      => $kelas,
      'pagination' => $pagination,
      'offset'     => $offset
    ];

    include __DIR__ . '/../../views/master/kelas_kamar/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/master/kelas_kamar/create.php';
  }

  public static function store() {
    global $conn;
    $nama         = $_POST['nama_kelas'] ?? '';
    $jenis_pasien = $_POST['jenis_pasien'] ?? null;
    $tarif        = $_POST['tarif'] ?? 0;

    if (trim($nama) === '' || !in_array($jenis_pasien, ['dewasa', 'anak']) || !is_numeric($tarif)) {
      $_SESSION['error'] = 'Semua field harus diisi dengan benar.';
      header('Location: index.php?page=kelas_kamar&action=create');
      exit;
    }

    if (KelasKamarModel::insert($nama, $jenis_pasien, $tarif, $conn)) {
      $_SESSION['success'] = 'Kelas kamar berhasil ditambahkan!';
    } else {
      $_SESSION['error'] = 'Gagal menambahkan kelas kamar.';
    }

    header('Location: index.php?page=kelas_kamar');
    exit;
  }

  public static function edit() {
    global $conn;
    $id = $_GET['id'] ?? null;
    $data = KelasKamarModel::find($id, $conn);
    include __DIR__ . '/../../views/master/kelas_kamar/edit.php';
  }

  public static function update() {
    global $conn;
    $id           = $_GET['id'] ?? null;
    $nama         = $_POST['nama_kelas'] ?? '';
    $jenis_pasien = $_POST['jenis_pasien'] ?? null;
    $tarif        = $_POST['tarif'] ?? 0;

    if (trim($nama) === '' || !in_array($jenis_pasien, ['dewasa', 'anak']) || !is_numeric($tarif)) {
      $_SESSION['error'] = 'Semua field harus diisi dengan benar.';
      header("Location: index.php?page=kelas_kamar&action=edit&id=$id");
      exit;
    }

    if (KelasKamarModel::update($id, $nama, $jenis_pasien, $tarif, $conn)) {
      $_SESSION['success'] = 'Kelas kamar berhasil diperbarui!';
    } else {
      $_SESSION['error'] = 'Gagal memperbarui data kelas kamar.';
    }

    header('Location: index.php?page=kelas_kamar');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;

    if (KelasKamarModel::delete($id, $conn)) {
      $_SESSION['success'] = 'Data berhasil dihapus!';
    } else {
      $_SESSION['error'] = 'Gagal menghapus data.';
    }

    header('Location: index.php?page=kelas_kamar');
    exit;
  }
}