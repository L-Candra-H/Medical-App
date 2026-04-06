<?php
require_once __DIR__ . '/../../models/master/AmbulanceModel.php';

class AmbulanceController {
  public static function index() {
    global $conn;
    $data = AmbulanceModel::getAll($conn);
    include __DIR__ . '/../../views/master/ambulance/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/master/ambulance/create.php';
  }

  public static function store() {
    global $conn;
    $tipe = $_POST['tipe'] ?? '';
    $asal = $_POST['asal_ambulance'] ?? '';

    if (trim($tipe) === '') {
      $_SESSION['error'] = 'Tipe ambulance harus dipilih.';
      header('Location: index.php?page=ambulance&action=create');
      exit;
    }

    if (AmbulanceModel::insert($tipe, $asal, $conn)) {
      $_SESSION['success'] = 'Data ambulance berhasil ditambahkan!';
    } else {
      $_SESSION['error'] = 'Gagal menambahkan data ambulance.';
    }

    header('Location: index.php?page=ambulance');
    exit;
  }

  public static function edit() {
    global $conn;
    $id = $_GET['id'] ?? null;
    $data = AmbulanceModel::find($id, $conn);
    include __DIR__ . '/../../views/master/ambulance/edit.php';
  }

  public static function update() {
    global $conn;
    $id   = $_GET['id'] ?? null;
    $tipe = $_POST['tipe'] ?? '';
    $asal = $_POST['asal_ambulance'] ?? '';

    if (AmbulanceModel::update($id, $tipe, $asal, $conn)) {
      $_SESSION['success'] = 'Data ambulance berhasil diperbarui!';
    } else {
      $_SESSION['error'] = 'Gagal mengupdate data.';
    }

    header('Location: index.php?page=ambulance');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;

    if (AmbulanceModel::delete($id, $conn)) {
      $_SESSION['success'] = 'Data berhasil dihapus!';
    } else {
      $_SESSION['error'] = 'Gagal menghapus data.';
    }

    header('Location: index.php?page=ambulance');
    exit;
  }
}
