<?php
require_once(__DIR__ . '/../../models/master/JabatanModel.php');

class JabatanController {
  public static function index() {
    self::startSession();
    $data = JabatanModel::getAll();
    $role = $_SESSION['role'] ?? '';
    include(__DIR__ . '/../../views/master/jabatan/index.php');
  }

  public static function create() {
    self::startSession();
    self::denyIfNotSuperadmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $nama = $_POST['nama_jabatan'] ?? '';
      $keterangan = $_POST['keterangan'] ?? '';
      JabatanModel::create($nama, $keterangan);
      header('Location: index.php?page=jabatan');
      exit;
    } else {
      include(__DIR__ . '/../../views/master/jabatan/create.php');
    }
  }

  public static function edit($id) {
    self::startSession();
    self::denyIfNotSuperadmin();

    if (!$id) {
      die('ID jabatan tidak valid.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $nama = $_POST['nama_jabatan'] ?? '';
      $keterangan = $_POST['keterangan'] ?? '';
      JabatanModel::update($id, $nama, $keterangan);
      header('Location: index.php?page=jabatan');
      exit;
    } else {
      $jabatan = JabatanModel::getById($id);
      include(__DIR__ . '/../../views/master/jabatan/edit.php');
    }
  }

  public static function update($id) {
    // Optional: bisa digabung dengan edit() POST
    self::edit($id);
  }

  public static function destroy($id) {
    die('Akses hapus dinonaktifkan.');
  }

  // 🔒 Helper internal
  private static function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  private static function denyIfNotSuperadmin() {
    $role = strtolower($_SESSION['role'] ?? '');
    if ($role !== 'superadmin') {
      die('Akses ditolak.');
    }
  }
}
