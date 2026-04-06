<?php
require_once __DIR__ . '/../models/InstitusiModel.php';

class InstitusiController {
  public static function index($conn) {
    $data = InstitusiModel::getAll($conn);
    include __DIR__ . '/../views/institusi/index.php';
  }

  public static function create($conn) {
    $existing = InstitusiModel::getAll($conn);
    if (count($existing) >= 1) {
      $_SESSION['error'] = 'Institusi sudah ditambahkan. Silakan gunakan menu Edit untuk memperbarui.';
      header('Location: index.php?page=institusi');
      exit;
    }

    $jenisList = InstitusiModel::getJenisList($conn);
    include __DIR__ . '/../views/institusi/create.php';
  }

  public static function store($conn, $post) {
    $jenisId = $post['jenis_id'] ?? 0;
    if (!InstitusiModel::isJenisValid($jenisId, $conn)) {
      $_SESSION['error'] = 'Jenis institusi tidak valid.';
      header('Location: index.php?page=institusi&action=create');
      exit;
    }

    $logoName = '';
    $targetDir = __DIR__ . '/../../public/assets/img/';
    $logoPrefix = 'logo_institusi';

    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === 0) {
      $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
      $allowedExt = ['png', 'jpg', 'jpeg'];

      if (in_array($ext, $allowedExt)) {
        foreach (glob($targetDir . $logoPrefix . '.*') as $oldLogo) {
          unlink($oldLogo);
        }

        $logoName = $logoPrefix . '.' . $ext;
        $targetPath = $targetDir . $logoName;

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
          error_log("❌ Upload logo gagal saat store.");
          $logoName = '';
        }
      } else {
        $_SESSION['error'] = 'Format logo harus PNG atau JPG.';
        header('Location: index.php?page=institusi&action=create');
        exit;
      }
    }

    $data = [
      'nama_institusi' => $post['nama_institusi'] ?? '',
      'sub_institusi'  => $post['sub_institusi'] ?? '',
      'jenis_id'       => (int) $jenisId,
      'alamat'         => $post['alamat'] ?? '',
      'telepon'        => $post['telepon'] ?? '',
      'email'          => $post['email'] ?? '',
      'logo'           => $logoName,
      'status'         => $post['status'] ?? 'AKTIF',
    ];

    if (InstitusiModel::create($data, $conn)) {
      $_SESSION['success'] = 'Data institusi berhasil ditambahkan!';
    } else {
      $_SESSION['error'] = 'Gagal menambahkan institusi.';
    }

    header('Location: index.php?page=institusi');
    exit;
  }

  public static function edit($conn, $id) {
    $institusi = InstitusiModel::find($id, $conn);
    $jenisList = InstitusiModel::getJenisList($conn);
    include __DIR__ . '/../views/institusi/edit.php';
  }

  public static function update($conn, $id, $post) {
    $jenisId = $post['jenis_id'] ?? 0;
    if (!InstitusiModel::isJenisValid($jenisId, $conn)) {
      $_SESSION['error'] = 'Jenis institusi tidak valid.';
      header("Location: index.php?page=institusi&action=edit&id=$id");
      exit;
    }

    $logoName = $post['logo_lama'] ?? '';
    $targetDir = __DIR__ . '/../../public/assets/img/';
    $logoPrefix = 'logo_institusi';

    $isNewLogo = !empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === 0;
    if ($isNewLogo) {
      $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
      $allowedExt = ['png', 'jpg', 'jpeg'];

      if (in_array($ext, $allowedExt)) {
        foreach (glob($targetDir . $logoPrefix . '.*') as $oldLogo) {
          unlink($oldLogo);
        }

        $logoName = $logoPrefix . '.' . $ext;
        $targetPath = $targetDir . $logoName;

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
          error_log("❌ Upload logo gagal saat update.");
        }
      } else {
        $_SESSION['error'] = 'Format logo harus PNG atau JPG.';
        header("Location: index.php?page=institusi&action=edit&id=$id");
        exit;
      }
    }

    $data = [
      'nama_institusi' => $post['nama_institusi'] ?? '',
      'sub_institusi'  => $post['sub_institusi'] ?? '',
      'jenis_id'       => (int) $jenisId,
      'alamat'         => $post['alamat'] ?? '',
      'telepon'        => $post['telepon'] ?? '',
      'email'          => $post['email'] ?? '',
      'logo'           => $logoName,
      'status'         => $post['status'] ?? 'AKTIF',
    ];

    if (InstitusiModel::update($id, $data, $conn)) {
      $_SESSION['success'] = 'Data institusi berhasil diperbarui!';
    } else {
      $_SESSION['error'] = 'Gagal update institusi.';
    }

    header('Location: index.php?page=institusi');
    exit;
  }

  public static function delete($conn, $id) {
    if (InstitusiModel::delete($id, $conn)) {
      $_SESSION['success'] = 'Institusi berhasil dihapus!';
    } else {
      $_SESSION['error'] = 'Gagal menghapus institusi.';
    }

    header('Location: index.php?page=institusi');
    exit;
  }
}
