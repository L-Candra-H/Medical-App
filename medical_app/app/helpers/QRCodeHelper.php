<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/medical_app/vendor/phpqrcode/qrlib.php';

class QRCodeHelper
{
  public static function generate($nip, $nama_petugas)
  {
      if (empty($nip)) {
          throw new Exception("❌ NIP kosong, tidak bisa buat QR");
      }

      $filename = 'qr_' . trim($nip) . '.png';
      $path = $_SERVER['DOCUMENT_ROOT'] . '/medical_app/public/qrcode/' . $filename;

      if (file_exists($path) && filesize($path) > 0) {
          return $filename;
      }

      $isiQR = "Ditandatangani Oleh : " . trim($nama_petugas) . "\nNIP : " . trim($nip);
      \QRCode::png($isiQR, $path);

      return $filename;
  }

  public static function getPath($filename)
  {
    if (empty($filename)) return null;
    $path = $_SERVER['DOCUMENT_ROOT'] . '/medical_app/public/qrcode/' . $filename;
    return file_exists($path) ? $path : null;
  }

  public static function isPetugasValid() {
      return isset($_SESSION['nip'], $_SESSION['nama_petugas']);
  }

  public static function getPetugas() {
      return [
          'nip' => $_SESSION['nip'] ?? '-',
          'nama_petugas' => $_SESSION['nama_petugas'] ?? 'Petugas Tidak Diketahui'
      ];
  }

  public static function renderExistingQR($height = 50) {
      $petugas = self::getPetugas();
      $nip = trim($petugas['nip']);
      $filename = 'qr_' . $nip . '.png';
      $path = self::getPath($filename);

      if (self::isValidQR($path)) {
          $src = '/medical_app/public/qrcode/' . rawurlencode($filename);
          $alt = 'QR Validasi ' . htmlspecialchars($petugas['nama_petugas']);
          return "<img src=\"{$src}\" alt=\"{$alt}\" height=\"{$height}\" loading=\"lazy\"/>";
      }

      return '<span class="text-warning">QR belum tersedia — file tidak ditemukan atau rusak</span>';
  }

  public static function retrieveOnly($nip, $nama_petugas, $height = 50) {
      $filename = 'qr_' . trim($nip) . '.png';
      $path = $_SERVER['DOCUMENT_ROOT'] . '/medical_app/public/qrcode/' . $filename;

      if (self::isValidQR($path)) {
          $src = '/medical_app/public/qrcode/' . rawurlencode(basename($filename));
          $alt = 'QR Validasi ' . htmlspecialchars($nama_petugas);
          return "<img src=\"{$src}\" alt=\"{$alt}\" height=\"{$height}\" loading=\"lazy\"/>";
      }

      return '<span class="text-warning">QR belum tersedia — file tidak ditemukan atau rusak</span>';
  }

  private static function isValidQR($path) {
    return (
      $path &&
      file_exists($path) &&
      getimagesize($path) !== false
    );
  }

  public static function renderQRImage($nip, $nama_petugas, $height = 50) {
      $filename = 'qr_' . trim($nip) . '.png';
      $path = self::getPath($filename);

      if ($path && file_exists($path)) {
          $src = '/medical_app/public/qrcode/' . rawurlencode(basename($filename));
          $alt = 'QR Validasi ' . htmlspecialchars($nama_petugas);
          return "<div class=\"border p-2 rounded bg-light\">
                      <img src=\"{$src}\" alt=\"{$alt}\" height=\"{$height}\" loading=\"lazy\"/>
                  </div>";
      }

      return '<span class="text-warning">QR belum tersedia — file tidak ditemukan atau rusak</span>';
  }

    public static function getQRPathForLoginPetugas(): ?string {
        $nip = $_SESSION['nip'] ?? null;
        if (!$nip) return null;

        $filename = 'qr_' . trim($nip) . '.png';
        $path = '/medical_app/public/qrcode/' . $filename;

        return $path;
    }

    public static function simpanQRValidasi(mysqli $conn, int $rawat_inap_id): bool {
        $user_id = $_SESSION['user_id'] ?? 0;
        $nip = $_SESSION['nip'] ?? '-';
        $qrPath = self::getQRPathForLoginPetugas();

        if (!$qrPath || !$user_id || $nip === '-') return false;

        require_once $_SERVER['DOCUMENT_ROOT'] . '/medical_app/app/models/RincianRawatInapModel.php';
        return RincianRawatInapModel::updateQRValidasi($conn, $rawat_inap_id, $qrPath, $user_id, $nip);
    }
    
}
