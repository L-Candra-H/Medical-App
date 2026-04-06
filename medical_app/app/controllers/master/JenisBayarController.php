<?php
require_once __DIR__ . '/../../models/master/JenisBayarModel.php';

class JenisBayarController {
  public static function index() {
    global $conn;
    $data = JenisBayarModel::getAll($conn);
    include __DIR__ . '/../../views/master/jenis_bayar/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/master/jenis_bayar/create.php';
  }

  public static function store() {
    global $conn;
    $nama = $_POST['nama_jenis'] ?? '';

    if (trim($nama) === '') {
      $_SESSION['error'] = 'Nama jenis bayar tidak boleh kosong!';
      header('Location: index.php?page=jenis_bayar&action=create');
      exit;
    }

    if (JenisBayarModel::insert($nama, $conn)) {
      $_SESSION['success'] = 'Jenis bayar berhasil ditambahkan!';
    } else {
      $_SESSION['error'] = 'Gagal menambahkan jenis bayar.';
    }

    header('Location: index.php?page=jenis_bayar');
    exit;
  }

  public static function edit() {
    global $conn;
    $id = $_GET['id'] ?? null;
    $data = JenisBayarModel::find($id, $conn);
    include __DIR__ . '/../../views/master/jenis_bayar/edit.php';
  }

  public static function update() {
    global $conn;
    $id   = $_GET['id'] ?? null;
    $nama = $_POST['nama_jenis'] ?? '';

    if (JenisBayarModel::update($id, $nama, $conn)) {
      $_SESSION['success'] = 'Jenis bayar berhasil diperbarui!';
    } else {
      $_SESSION['error'] = 'Gagal mengupdate jenis bayar.';
    }

    header('Location: index.php?page=jenis_bayar');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;

    if (JenisBayarModel::delete($id, $conn)) {
      $_SESSION['success'] = 'Data berhasil dihapus!';
    } else {
      $_SESSION['error'] = 'Gagal menghapus data.';
    }

    header('Location: index.php?page=jenis_bayar');
    exit;
  }
}
