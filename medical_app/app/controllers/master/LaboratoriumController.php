<?php
require_once __DIR__ . '/../../models/master/LaboratoriumModel.php';

class LaboratoriumController {
  public static function index() {
    global $conn;
    $data = LaboratoriumModel::getAll($conn);
    include __DIR__ . '/../../views/master/laboratorium/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/master/laboratorium/create.php';
  }

  public static function store() {
    global $conn;
    $tipe = $_POST['tipe'] ?? '';
    $asal = $_POST['asal_laboratorium'] ?? '';

    if (trim($tipe) === '') {
      $_SESSION['error'] = 'Tipe laboratorium harus dipilih.';
      header('Location: index.php?page=laboratorium&action=create');
      exit;
    }

    if (LaboratoriumModel::insert($tipe, $asal, $conn)) {
      $_SESSION['success'] = 'Data laboratorium berhasil ditambahkan!';
    } else {
      $_SESSION['error'] = 'Gagal menambahkan data laboratorium.';
    }

    header('Location: index.php?page=laboratorium');
    exit;
  }

  public static function edit() {
    global $conn;
    $id = $_GET['id'] ?? null;
    $data = LaboratoriumModel::find($id, $conn);
    include __DIR__ . '/../../views/master/laboratorium/edit.php';
  }

  public static function update() {
    global $conn;
    $id   = $_GET['id'] ?? null;
    $tipe = $_POST['tipe'] ?? '';
    $asal = $_POST['asal_laboratorium'] ?? '';

    if (LaboratoriumModel::update($id, $tipe, $asal, $conn)) {
      $_SESSION['success'] = 'Data laboratorium berhasil diperbarui!';
    } else {
      $_SESSION['error'] = 'Gagal mengupdate data laboratorium.';
    }

    header('Location: index.php?page=laboratorium');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;

    if (LaboratoriumModel::delete($id, $conn)) {
      $_SESSION['success'] = 'Data laboratorium berhasil dihapus!';
    } else {
      $_SESSION['error'] = 'Gagal menghapus data.';
    }

    header('Location: index.php?page=laboratorium');
    exit;
  }
}
