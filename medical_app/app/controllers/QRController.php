<?php
require_once(__DIR__ . '/../helpers/QRCodeHelper.php');

class QRController {
  public static function generateAndSave($conn, $id_transaksi) {
    // ✅ Validasi awal
    if (!is_numeric($id_transaksi)) {
      throw new InvalidArgumentException("ID transaksi harus numerik.");
    }

    // 🛡️ Pastikan session aktif (jika belum)
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    try {
      // 🔎 Ambil data pembuat dari rawat_inap
      $sql = "SELECT created_by_nama, created_by_nip, qr_filename FROM rawat_inap WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->execute([$id_transaksi]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$row || empty($row['created_by_nama']) || empty($row['created_by_nip'])) {
        error_log("❌ Gagal generate QR: data pembuat tidak ditemukan untuk transaksi ID $id_transaksi.");
        return null;
      }

      $nama_petugas = trim($row['created_by_nama']);
      $nip_petugas  = trim($row['created_by_nip']);

      // ♻️ Hapus QR lama jika ada
      if (!empty($row['qr_filename'])) {
        $existingPath = QRCodeHelper::getPath($row['qr_filename']);
        if (file_exists($existingPath)) {
          unlink($existingPath);
          error_log("🗑️ QR lama dihapus untuk transaksi ID $id_transaksi.");
        }
      }

      // 🎯 Generate QR dengan konten baru
      $qr_content = "Petugas: {$nama_petugas}\nNIP: {$nip_petugas}\nTransaksi ID: {$id_transaksi}";
      $filename = QRCodeHelper::generate($qr_content); // Pastikan generate() menerima konten

      // 💾 Simpan filename ke database
      $update = $conn->prepare("UPDATE rawat_inap SET qr_filename = ? WHERE id = ?");
      $update->bindValue(1, $filename);
      $update->bindValue(2, $id_transaksi, PDO::PARAM_INT);
      $update->execute();

      error_log(date('[Y-m-d H:i:s]') . " ✅ QR baru dibuat & disimpan untuk transaksi ID $id_transaksi oleh " . ($_SESSION['nama'] ?? 'anonymous'));
      return $filename;
    } catch (Exception $e) {
      error_log(date('[Y-m-d H:i:s]') . " ⚠️ QRController exception: " . $e->getMessage());
      return null;
    }
  }
}
