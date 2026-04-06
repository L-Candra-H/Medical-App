<?php
require_once __DIR__ . '/../models/PotonganModel.php';
require_once __DIR__ . '/../models/TiketRawatInapModel.php';
require_once __DIR__ . '/../helpers/terbilang.php';
require_once __DIR__ . '/../controllers/InstitusiController.php';

class PotonganController {

  // 🔒 Helper internal untuk validasi status tiket
  private static function isTiketSelesai(mysqli $conn, int $rawat_inap_id): bool {
    $stmt = $conn->prepare("SELECT status FROM tiket_rawat_inap WHERE id = ?");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return strtoupper($result['status'] ?? '') === 'SELESAI';
  }

  public static function index($conn) {
    $rawat_inap_id = $_GET['id'] ?? null;
    if (!$rawat_inap_id) {
      $_SESSION['error'] = 'ID tiket rawat inap tidak ditemukan.';
      header('Location: index.php?page=potongan&action=listAll');
      exit;
    }

    $tiket = PotonganModel::getPasienByRawatInap($conn, $rawat_inap_id);
    if (!$tiket) {
      $_SESSION['error'] = 'Data pasien tidak ditemukan.';
      header('Location: index.php?page=potongan&action=listAll');
      exit;
    }

    $potongan_list = PotonganModel::getAllByRawatInap($conn, $rawat_inap_id);
    
    $pagination = [
      'page'      => 1,
      'totalPage' => 1,
      'search'    => ''
    ];

    require __DIR__ . '/../views/potongan/index.php';
  }

  // === LIST ALL dengan pagination 10 ===
  public static function listAll($conn) {
    // Ambil halaman aktif dari URL (default 1)
    $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
    if ($page < 1) $page = 1;

    $limit  = 8; // jumlah baris per halaman
    $offset = ($page - 1) * $limit;
    $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

    // Hitung total data sesuai pencarian
    $totalData = PotonganModel::countAll($conn, $search);
    $totalPage = ceil($totalData / $limit);
    if ($totalPage < 1) $totalPage = 1;

    // Ambil data sesuai halaman + pencarian
    $potongan_list = PotonganModel::getPaginated($conn, $limit, $offset, $search);

    // Siapkan variabel untuk view
    $pagination = [
      'page'      => $page,
      'totalPage' => $totalPage,
      'search'    => $search
    ];

    $data = [
      'potongan'   => $potongan_list,
      'pagination' => $pagination,
      'offset'     => $offset
    ];

    require __DIR__ . '/../views/potongan/index.php';
  }

  public static function inputForm($conn) {
    $rawat_inap_id = $_GET['id'] ?? null;
    $pasien = null;
    $tiket_list = [];
    $sudah_ada = false;

    if ($rawat_inap_id) {
      if (self::isTiketSelesai($conn, $rawat_inap_id)) {
        echo "<h4 style='color:red'>Tiket sudah selesai. Tidak bisa menambah potongan.</h4>";
        exit;
      }

      $pasien = PotonganModel::getPasienByRawatInap($conn, $rawat_inap_id);

      if (!$pasien) {
        $_SESSION['warning'] = 'Data pasien tidak ditemukan. Anda tetap bisa input manual.';
      }

      $tiket_list[] = ['id' => $rawat_inap_id];
      $sudah_ada = PotonganModel::existsForRawatInap($conn, $rawat_inap_id);
    } else {
      $model = new TiketRawatInapModel($conn);
      $tiket_list = $model->getAktif();
    }

    require __DIR__ . '/../views/potongan/create.php';
  }

  public static function store($conn) {
    $rawat_inap_id = intval($_POST['rawat_inap_id']);
    $jumlah = floatval($_POST['jumlah'] ?? 0);

    if ($rawat_inap_id === 0 || $jumlah <= 0) {
      $_SESSION['error'] = 'Data potongan tidak valid.';
      header("Location: index.php?page=potongan&action=inputForm&id=$rawat_inap_id");
      exit;
    }

    if (self::isTiketSelesai($conn, $rawat_inap_id)) {
      $_SESSION['error'] = 'Tiket sudah selesai. Tidak bisa menambah potongan.';
      header("Location: index.php?page=potongan&action=index&id=$rawat_inap_id");
      exit;
    }

    if (PotonganModel::existsForRawatInap($conn, $rawat_inap_id)) {
      $_SESSION['error'] = 'Potongan sudah pernah diinput untuk pasien ini.';
      header("Location: index.php?page=potongan&action=inputForm&id=$rawat_inap_id");
      exit;
    }

    $data = [
      'rawat_inap_id' => $rawat_inap_id,
      'tanggal' => $_POST['tanggal'] ?? date('Y-m-d'),
      'jumlah' => $jumlah,
      'tipe' => $_POST['tipe'] ?? '',
      'keterangan' => $_POST['keterangan'] ?? '',
      'dibuat_oleh_id' => $_SESSION['user_id'] ?? null
    ];

    $sukses = PotonganModel::store($conn, $data);
    $_SESSION[$sukses ? 'success' : 'error'] = $sukses
      ? 'Potongan berhasil disimpan.'
      : 'Gagal menyimpan potongan.';

    header("Location: index.php?page=potongan&action=index&id=$rawat_inap_id");
    exit;
  }

  public static function editForm($conn, $id) {
    $stmt = $conn->prepare("
      SELECT p.*, r.tanggal_masuk, ps.nama_pasien, ps.nomor_register
      FROM potongan_rawat_inap p
      JOIN tiket_rawat_inap r ON r.id = p.rawat_inap_id
      JOIN pasien ps ON ps.id = r.pasien_id
      WHERE p.id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_assoc() : null;

    if (!$data) {
      $_SESSION['error'] = 'Data tidak ditemukan.';
      header('Location: index.php?page=potongan&action=listAll');
      exit;
    }

    if (self::isTiketSelesai($conn, $data['rawat_inap_id'])) {
      echo "<h4 style='color:red'>Tiket sudah selesai. Tidak bisa mengedit potongan.</h4>";
      exit;
    }

    require __DIR__ . '/../views/potongan/edit.php';
  }

  public static function update($conn, $id) {
    $data = [
      'tanggal' => $_POST['tanggal'] ?? date('Y-m-d'),
      'jumlah' => floatval($_POST['jumlah'] ?? 0),
      'tipe' => $_POST['tipe'] ?? '',
      'keterangan' => $_POST['keterangan'] ?? '',
      'rawat_inap_id' => intval($_POST['rawat_inap_id'] ?? 0)
    ];

    if ($data['jumlah'] <= 0 || $data['rawat_inap_id'] === 0) {
      $_SESSION['error'] = 'Data potongan tidak valid.';
      header("Location: index.php?page=potongan&action=edit&id=$id");
      exit;
    }

    if (self::isTiketSelesai($conn, $data['rawat_inap_id'])) {
      $_SESSION['error'] = 'Tiket sudah selesai. Tidak bisa mengubah potongan.';
      header("Location: index.php?page=potongan&action=index&id=" . $data['rawat_inap_id']);
      exit;
    }

    $sukses = PotonganModel::update($conn, $id, $data);
    $_SESSION[$sukses ? 'success' : 'error'] = $sukses
      ? 'Data potongan berhasil diperbarui.'
      : 'Gagal mengupdate potongan.';

    header("Location: index.php?page=potongan&action=index&id=" . $data['rawat_inap_id']);
    exit;
  }

  public static function cetak($conn, $id) {
    $transaksi = PotonganModel::getById($conn, $id);
    if (!$transaksi) {
      $_SESSION['error'] = 'Data potongan tidak ditemukan.';
      header('Location: index.php?page=potongan&action=listAll');
      exit;
    }

    // Ambil detail pasien + petugas
    $stmt = $conn->prepare("
      SELECT ps.nama_pasien, ps.nomor_register, k.nama_petugas, k.nip
      FROM tiket_rawat_inap r
      JOIN pasien ps ON ps.id = r.pasien_id
      LEFT JOIN kasir k ON k.user_id = ?
      WHERE r.id = ?
    ");
    $stmt->bind_param("ii", $transaksi['dibuat_oleh_id'], $transaksi['rawat_inap_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $detail = $result ? $result->fetch_assoc() : [];

    // Ambil data institusi
    $institusi = $conn->query("SELECT * FROM institusi LIMIT 1")->fetch_assoc() ?? [];

    // Gabungkan semua data
    $data = array_merge($transaksi, $detail, $institusi);
    $data['terbilang'] = terbilang($transaksi['jumlah'] ?? 0);
    $data['nama_petugas'] = $detail['nama_petugas'] ?? '-';
    $data['nip'] = $detail['nip'] ?? '-';

    require __DIR__ . '/../views/potongan/cetak_kuitansi.php';
  }

  public static function ajaxSearch($conn) {
      $keyword = $_GET['q'] ?? '';
      $keyword = trim($keyword);

      // Ambil data sesuai keyword
      $results = [];
      if ($keyword !== '') {
          $results = PotonganModel::getPaginated($conn, 10, 0, $keyword);
      }

      // Render hasil sebagai <tr> untuk tbody
      if (!empty($results)) {
          foreach ($results as $pot) {
              echo "<tr>
                      <td class='text-center'>{$pot['tanggal']}</td>
                      <td>".htmlspecialchars($pot['nama_pasien'])." / {$pot['nomor_register']}</td>
                      <td>Rp ".number_format($pot['jumlah'],0,',','.')."</td>
                      <td>{$pot['tipe']}</td>
                      <td>{$pot['keterangan']}</td>
                      <td>-</td>
                      <td class='text-center'>
                        <a href='index.php?page=potongan&action=edit&id={$pot['id']}' class='btn btn-sm btn-warning me-1'>
                          <i class='fas fa-edit'></i>
                        </a>
                        <a href='index.php?page=potongan&action=cetak&id={$pot['id']}' class='btn btn-sm btn-info' target='_blank'>
                          <i class='fas fa-print'></i>
                        </a>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada hasil</td></tr>";
      }
  }

}