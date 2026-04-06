<?php
require_once __DIR__ . '/../models/UangMukaModel.php';
require_once __DIR__ . '/../helpers/terbilang.php';
require_once __DIR__ . '/../helpers/QRCodeHelper.php';

class UangMukaController {

  // 🔒 Helper internal untuk validasi status tiket
  private static function isTiketSelesai(mysqli $conn, int $rawat_inap_id): bool {
    $stmt = $conn->prepare("SELECT status FROM tiket_rawat_inap WHERE id = ?");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return strtoupper($result['status'] ?? '') === 'SELESAI';
  }

  public static function index($conn) {
      $rawat_inap_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

      if (!$rawat_inap_id) {
        $_SESSION['error'] = 'Data tidak ditemukan.';
        header('Location: index.php?page=pasien');
        exit;
      }

      $pasien = UangMukaModel::getPasienInfo($conn, $rawat_inap_id);
      $uang_muka_list = UangMukaModel::getAllByRawatInap($conn, $rawat_inap_id);

      // status tiket
      $status_tiket = UangMukaModel::getTiketStatus($conn, $rawat_inap_id)['status'] ?? 'AKTIF';

      // default pagination supaya view tidak error
      $pagination = [
        'page'      => 1,
        'totalPage' => 1,
        'search'    => ''
      ];

      require __DIR__ . '/../views/uang_muka/index.php';
  }

  public static function inputForm($conn, $id = null) {
    $pasien = null;
    $tiket_list = [];
    $riwayat = [];

    if ($id) {
      if (self::isTiketSelesai($conn, $id)) {
        echo "<h4 style='color:red'>Tiket sudah selesai. Tidak bisa menambah uang muka.</h4>";
        exit;
      }

      $pasien = UangMukaModel::getPasienInfo($conn, $id);
      $riwayat = UangMukaModel::getRiwayatByTiket($conn, $id);
    } else {
      $tiket_list = UangMukaModel::getAllWithPasien($conn);
    }

    require __DIR__ . '/../views/uang_muka/create.php';
  }

  public static function store($conn) {
    $rawat_inap_id   = intval($_POST['tiket_id'] ?? 0);
    $tanggal         = $_POST['tanggal'] ?? '';
    $jumlah          = floatval($_POST['jumlah'] ?? 0);
    $metode          = $_POST['metode_pembayaran'] ?? '';
    $keterangan      = $_POST['keterangan'] ?? '';
    $dibuat_oleh_id  = $_SESSION['user']['id'] ?? 1;
    $dicetak_oleh_id = null;

    if (!$rawat_inap_id || !$tanggal || $jumlah <= 0 || !$metode) {
      $_SESSION['error'] = "Data tidak lengkap atau tidak valid.";
      header("Location: index.php?page=uang_muka&action=input&id=" . $rawat_inap_id);
      exit;
    }

    if (self::isTiketSelesai($conn, $rawat_inap_id)) {
      $_SESSION['error'] = 'Tiket sudah selesai. Tidak bisa menambah uang muka.';
      header("Location: index.php?page=uang_muka&id=" . $rawat_inap_id);
      exit;
    }

    $data = [
      'rawat_inap_id'     => $rawat_inap_id,
      'tanggal'           => $tanggal,
      'jumlah'            => $jumlah,
      'metode_pembayaran' => $metode,
      'keterangan'        => $keterangan,
      'dibuat_oleh_id'    => $dibuat_oleh_id,
      'dicetak_oleh_id'   => $dicetak_oleh_id,
      'created_at'        => date('Y-m-d H:i:s'),
      'updated_at'        => date('Y-m-d H:i:s')
    ];

    $sukses = UangMukaModel::insert($conn, $data);

    $_SESSION[$sukses ? 'success' : 'error'] = $sukses
      ? "Transaksi uang muka berhasil disimpan."
      : "Gagal menyimpan data.";

    header("Location: index.php?page=uang_muka&id=" . $rawat_inap_id);
    exit;
  }

  public static function editForm($conn, $id) {
    $data = UangMukaModel::getDetail($conn, $id);

    if (!$data || empty($data['id'])) {
      $_SESSION['error'] = 'Data tidak ditemukan.';
      header('Location: index.php?page=uang_muka&action=admin');
      exit;
    }

    if (self::isTiketSelesai($conn, $data['rawat_inap_id'])) {
      echo "<h4 style='color:red'>Tiket sudah selesai. Tidak bisa mengedit uang muka.</h4>";
      exit;
    }

    require __DIR__ . '/../views/uang_muka/edit.php';
  }

  public static function update($conn, $id) {
    $rawat_inap_id = intval($_POST['tiket_id'] ?? 0);
    $jumlah        = floatval($_POST['jumlah'] ?? 0);

    if (self::isTiketSelesai($conn, $rawat_inap_id)) {
      $_SESSION['error'] = 'Tiket sudah selesai. Tidak bisa mengubah uang muka.';
      header("Location: index.php?page=uang_muka&id=" . $rawat_inap_id);
      exit;
    }

    $data = [
      'tanggal'           => $_POST['tanggal'] ?? date('Y-m-d'),
      'jumlah'            => $jumlah,
      'metode_pembayaran' => $_POST['metode_pembayaran'] ?? '',
      'keterangan'        => $_POST['keterangan'] ?? '',
      'dibuat_oleh_id'    => $_SESSION['user']['id'] ?? 0,
      'rawat_inap_id'     => $rawat_inap_id
    ];

    if ($jumlah <= 0 || $rawat_inap_id === 0) {
      $_SESSION['error'] = 'Data tidak valid.';
      header('Location: index.php?page=uang_muka&action=edit&id=' . $id);
      exit;
    }

    $sukses = UangMukaModel::update($conn, $id, $data);
    $_SESSION[$sukses ? 'success' : 'error'] = $sukses
      ? 'Data berhasil diperbarui.'
      : 'Gagal mengupdate data.';

    header('Location: index.php?page=uang_muka&id=' . $rawat_inap_id);
    exit;
  }

  public static function cetak($conn, $id) {
    $transaksi = UangMukaModel::getCetakData($conn, $id);
    $institusi = $conn->query("SELECT * FROM institusi LIMIT 1")->fetch_assoc();
    $data = array_merge($transaksi ?? [], $institusi ?? []);

    $data['terbilang'] = terbilang($transaksi['jumlah'] ?? 0);
    $filename = "qr_" . ($transaksi['nip'] ?? 'petugas') . ".png";
    $data['qr_path'] = "/medical_app/public/qrcode/" . $filename;

    require __DIR__ . '/../views/uang_muka/cetak_kuitansi.php';
  }

  // === ADMIN VIEW dengan pagination 10 ===
  public static function adminView($conn) {
    // Ambil halaman aktif dari URL (default 1)
    $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
    if ($page < 1) $page = 1;

    $limit  = 8; // jumlah baris per halaman
    $offset = ($page - 1) * $limit;
    $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

    // Hitung total data sesuai pencarian
    $totalData = UangMukaModel::countAll($conn, $search);
    $totalPage = ceil($totalData / $limit);
    if ($totalPage < 1) $totalPage = 1;

    // Ambil data sesuai halaman + pencarian
    $uang_muka_list = UangMukaModel::getPaginated($conn, $limit, $offset, $search);

    // Siapkan variabel untuk view
    $pagination = [
      'page'      => $page,
      'totalPage' => $totalPage,
      'search'    => $search
    ];

    $data = [
      'uang_muka'  => $uang_muka_list,
      'pagination' => $pagination,
      'offset'     => $offset
    ];

    // Default agar view tidak error
    $status_tiket = 'AKTIF';

    require __DIR__ . '/../views/uang_muka/index.php';
  }

  public static function ajaxSearch($conn) {
      $keyword = $_GET['q'] ?? '';
      // Ambil maksimal 10 baris hasil pencarian
      $results = UangMukaModel::getPaginated($conn, 10, 0, $keyword);

      foreach ($results as $row) {
          $tanggal     = htmlspecialchars($row['tanggal'] ?? '-');
          $namaPasien  = htmlspecialchars($row['nama_pasien'] ?? '-');
          $register    = htmlspecialchars($row['nomor_register'] ?? '-');
          $jumlah      = number_format(floatval($row['jumlah'] ?? 0), 0, ',', '.');
          $metode      = htmlspecialchars($row['metode_pembayaran'] ?? '-');
          $keterangan  = htmlspecialchars($row['keterangan'] ?? '-');
          $pembuat     = htmlspecialchars($row['nama_pembuat'] ?? '-');
          $nipPembuat  = htmlspecialchars($row['nip_pembuat'] ?? '-');

          echo "<tr>
                  <td>{$tanggal}</td>
                  <td>{$namaPasien} / {$register}</td>
                  <td>Rp {$jumlah}</td>
                  <td>{$metode}</td>
                  <td>{$keterangan}</td>
                  <td>{$pembuat} / {$nipPembuat}</td>
                  <td class='text-center'>
                    <a href='index.php?page=uang_muka&action=edit&id={$row['id']}' class='btn btn-sm btn-warning me-1'>
                      <i class='fas fa-edit'></i>
                    </a>
                    <a href='index.php?page=uang_muka&action=cetak&id={$row['id']}' class='btn btn-sm btn-info' target='_blank'>
                      <i class='fas fa-print'></i>
                    </a>
                  </td>
                </tr>";
      }

      if (empty($results)) {
          echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada hasil</td></tr>";
      }

      exit; // penting: hentikan agar header/footer tidak ikut
  }

}