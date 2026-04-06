<?php
require_once __DIR__ . '/../../models/input/PasienModel.php';

class PasienController {
  public static function index() {
      global $conn;

      // Ambil halaman aktif dari URL (default 1)
      $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
      if ($page < 1) $page = 1;

      $limit  = 10; // jumlah baris per halaman
      $offset = ($page - 1) * $limit;
      $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

      // Hitung total data sesuai pencarian
      $totalData = PasienModel::countAll($conn, $search);
      $totalPage = ceil($totalData / $limit);
      if ($totalPage < 1) $totalPage = 1;

      // Ambil data sesuai halaman + pencarian
      $pasien = PasienModel::getPaginated($conn, $limit, $offset, $search);

      // Siapkan variabel untuk view
      $pagination = [
          'page'      => $page,
          'totalPage' => $totalPage,
          'search'    => $search
      ];

      $data = [
          'pasien'     => $pasien,
          'pagination' => $pagination,
          'offset'     => $offset
      ];

      include __DIR__ . '/../../views/input/pasien/index.php';
  }

  public static function create() {
    include __DIR__ . '/../../views/input/pasien/create.php';
  }

  public static function store() {
    global $conn;
    $no_register = trim($_POST['nomor_register'] ?? '');
    $nama        = trim($_POST['nama_pasien'] ?? '');

    if (empty($no_register) || empty($nama)) {
      $_SESSION['error'] = 'Nomor register dan nama pasien wajib diisi.';
      header('Location: index.php?page=pasien&action=create');
      exit;
    }

    if (PasienModel::insert($no_register, $nama, $conn)) {
      $_SESSION['success'] = 'Data pasien berhasil ditambahkan!';
    } else {
      $_SESSION['error'] = 'Gagal menambahkan data pasien.';
    }

    header('Location: index.php?page=pasien');
    exit;
  }

  public static function edit() {
    global $conn;
    $id = $_GET['id'] ?? null;
    $data = PasienModel::find($id, $conn);
    include __DIR__ . '/../../views/input/pasien/edit.php';
  }

  public static function update() {
    global $conn;
    $id          = $_GET['id'] ?? null;
    $no_register = trim($_POST['nomor_register'] ?? '');
    $nama        = trim($_POST['nama_pasien'] ?? '');

    if (!$id || empty($no_register) || empty($nama)) {
      $_SESSION['error'] = 'Data tidak lengkap.';
      header('Location: index.php?page=pasien');
      exit;
    }

    if (PasienModel::update($id, $no_register, $nama, $conn)) {
      $_SESSION['success'] = 'Data pasien berhasil diperbarui!';
    } else {
      $_SESSION['error'] = 'Gagal memperbarui data pasien.';
    }

    header('Location: index.php?page=pasien');
    exit;
  }

  public static function destroy() {
    global $conn;
    $id = $_GET['id'] ?? null;

    if (!$id) {
      $_SESSION['error'] = 'ID tidak valid.';
      header('Location: index.php?page=pasien');
      exit;
    }

    if (PasienModel::delete($id, $conn)) {
      $_SESSION['success'] = 'Data pasien berhasil dihapus!';
    } else {
      $_SESSION['error'] = 'Gagal menghapus data pasien.';
    }

    header('Location: index.php?page=pasien');
    exit;
  }

  // Modular hooks
  public static function uangMuka() {}
  public static function potongan() {}
  public static function rincian() {}

  // ✅ AJAX Search Pasien
  public static function ajax_search() {
    global $conn;
    $q = $_GET['q'] ?? '';
    $q = $conn->real_escape_string($q);

    $result = $conn->query("
      SELECT id, nomor_register, nama_pasien
      FROM pasien
      WHERE nama_pasien LIKE '%$q%' OR nomor_register LIKE '%$q%'
      ORDER BY id DESC
    ");

    if ($result->num_rows > 0) {
      $i = 1;
      while ($row = $result->fetch_assoc()) {
        echo "<tr>
          <td>{$i}</td>
          <td>" . htmlspecialchars($row['nomor_register']) . "</td>
          <td>" . htmlspecialchars($row['nama_pasien']) . "</td>
          <td class='text-end'>
            <a href='index.php?page=pasien&action=edit&id={$row['id']}' class='btn btn-warning btn-sm me-1'>
              <i class='fas fa-edit'></i> Edit Data Pasien
            </a>
            <a href='index.php?page=rawat_inap&action=tiket&id={$row['id']}' class='btn btn-info btn-sm me-1'>
              <i class='fas fa-ticket-alt'></i> Tiket Inap
            </a>
          </td>
        </tr>";
        $i++;
      }
    } else {
      echo "<tr><td colspan='4' class='text-muted text-center'>Tidak ditemukan</td></tr>";
    }
  }
}