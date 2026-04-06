<?php
require_once __DIR__ . '/UangMukaModel.php';
require_once __DIR__ . '/PotonganModel.php';

class RincianRawatInapModel {

  public static function getByRawatInapId(mysqli $conn, int $rawat_inap_id): ?array {
    $stmt = $conn->prepare("
      SELECT
        rincian.*,
        rincian.tanggal_pemeriksaan,
        pasien.nama_pasien AS nama_pasien,
        petugas.nama_petugas AS nama_petugas,
        petugas.nip AS nip_petugas,
        rincian.nama_jenis_bayar,
        rincian.nama_kelas_perawatan
      FROM rawat_inap_rincian rincian
      JOIN tiket_rawat_inap tiket ON tiket.id = rincian.rawat_inap_id
      JOIN pasien ON pasien.id = tiket.pasien_id
      LEFT JOIN kasir petugas ON petugas.id = tiket.dibuat_oleh
      WHERE rincian.rawat_inap_id = ?
      LIMIT 1
    ");

    if (!$stmt) return null;

    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
  }

  private static function allowedFields(): array {
    return [
      'rawat_inap_id',
      'nama_pasien', 'nomor_register',
      'jenis_bayar_id', 'nama_jenis_bayar',
      'kelas_kamar_id', 'nama_kelas_perawatan',
      'jenis_rincian',

      'ibu_mulai', 'ibu_selesai', 'ibu_hari', 'ibu_tarif', 'ibu_total',
      'bayi_mulai', 'bayi_selesai', 'bayi_hari', 'bayi_tarif', 'bayi_total',
      'anak_mulai', 'anak_selesai', 'anak_hari', 'anak_tarif', 'anak_total',
      'total_kamar_perawatan', 'total_bersalin', 'total_jasa_rs', 'total_karcis', 'total_materai',

      'ambulance_1_id', 'jumlah_ambulance_1',
      'ambulance_2_id', 'jumlah_ambulance_2',
      'total_ambulance',

      'total_kamar_akomodasi',

      'jasa_tindakan', 'total_jasa_tindakan',
      
      'dokter_visite_1_id', 'lama_visite_1', 'biaya_visite_1', 'total_visite_1',
      'dokter_visite_2_id', 'lama_visite_2', 'biaya_visite_2', 'total_visite_2',
      'dokter_visite_3_id', 'lama_visite_3', 'biaya_visite_3', 'total_visite_3',
      'total_visite_dokter',

      'dokter_tindakan_1_id', 'biaya_tindakan_dokter_1',
      'dokter_tindakan_2_id', 'biaya_tindakan_dokter_2',
      'dokter_tindakan_3_id', 'biaya_tindakan_dokter_3',

      'asisten_tindakan_1_id', 'biaya_asisten_1',
      'asisten_tindakan_2_id', 'biaya_asisten_2',
      'asisten_tindakan_3_id', 'biaya_asisten_3',

      'instrumen_onlop_id', 'biaya_instrumen_onlop',
      'total_tindakan_medis', 'total_tenaga_ahli',

      'dokter_konsultasi_1_id', 'jumlah_konsul_1', 'biaya_konsul_1',
      'dokter_konsultasi_2_id', 'jumlah_konsul_2', 'biaya_konsul_2',
      'dokter_konsultasi_3_id', 'jumlah_konsul_3', 'biaya_konsul_3',
      'total_konsultasi',

      'laboratorium_1_id', 'jumlah_lab_1',
      'laboratorium_2_id', 'jumlah_lab_2',
      'total_laboratorium',

      'usg_kali', 'jumlah_usg',
      'radiologi_tambahan_1', 'jumlah_radiologi_tambahan_1',
      'radiologi_tambahan_2', 'jumlah_radiologi_tambahan_2',
      'total_radiologi',

      'phototerapi_seri', 'phototerapi_biaya',
      'biaya_suction', 'biaya_syringe',
      'incubator_kali', 'incubator_biaya',
      'nebulizer_kali', 'nebulizer_biaya',
      'nama_tindakan_1', 'tindakan_lain_1',
      'nama_tindakan_2', 'tindakan_lain_2',
      'total_tindakan',

      'nst_kali', 'nst_biaya', 'ecg_biaya',
      'total_penunjang',

      'jumlah_transfusi', 'total_transfusi_darah',

      'biaya_persalinan', 'total_prosedur_non_bedah',
      'biaya_obat', 'total_obat',
      'biaya_kamar_operasi', 'total_prosedur_bedah',
      'biaya_alkes',
      'nama_alkes_1', 'alkes_tambahan_1',
      'nama_alkes_2', 'alkes_tambahan_2',
      'nama_alkes_3', 'alkes_tambahan_3',
      'total_alkes',
      'biaya_rehabilitasi', 'total_rehabilitasi',
      'biaya_rawat_intensif', 'total_rawat_intensif',
      'biaya_bmhp', 'total_bmhp',

      'total_semua_bagian', 'uang_muka', 'potongan', 'sisa_tagihan',

      'catatan', 'tanggal_pemeriksaan', 'qr_validasi_path', 'dibuat_oleh', 'nip'
    ];
  }

  public static function isValidField(string $field): bool {
    return in_array($field, self::allowedFields());
  }

  public static function normalizeData(array $data): array {
      $data = array_filter($data, fn($key) => in_array($key, self::allowedFields()), ARRAY_FILTER_USE_KEY);

      // Eksklusif: hanya salah satu kamar yang boleh terisi
      $ibuTerisi  = !empty($data['ibu_mulai'])  || !empty($data['ibu_selesai']);
      $bayiTerisi = !empty($data['bayi_mulai']) || !empty($data['bayi_selesai']);
      $anakTerisi = !empty($data['anak_mulai']) || !empty($data['anak_selesai']);

      $gabung = isset($data['jenis_rincian']) && $data['jenis_rincian'] === 'Gabung';

      if ($gabung) {
          // ✅ Mode gabung: Ibu + Bayi boleh, Anak harus kosong
          if ($ibuTerisi && $anakTerisi) {
              error_log("❌ Tidak boleh mengisi kamar ibu dan anak sekaligus (gabung).");
              return [];
          }
          if ($bayiTerisi && $anakTerisi) {
              error_log("❌ Tidak boleh mengisi kamar bayi dan anak sekaligus (gabung).");
              return [];
          }
      } else {
          // ✅ Mode sendiri: hanya boleh pilih salah satu (Ibu atau Anak)
          if ($ibuTerisi && $anakTerisi) {
              error_log("❌ Tidak boleh mengisi kamar ibu dan anak sekaligus (sendiri).");
              return [];
          }
          if ($bayiTerisi) {
              error_log("❌ Kamar bayi hanya tersedia jika jenis rincian = Gabung.");
              return [];
          }
      }

      if ($ibuTerisi) {
        $data['anak_mulai'] = null;
        $data['anak_selesai'] = null;
      } elseif ($anakTerisi) {
        $data['ibu_mulai'] = null;
        $data['ibu_selesai'] = null;
      }

      // Field yang boleh kosong (tanggal)
      $nullableDateFields = [
        'ibu_mulai', 'ibu_selesai',
        'bayi_mulai', 'bayi_selesai',   // 🔹 ditambahkan
        'anak_mulai', 'anak_selesai',
        'tanggal_pemeriksaan'
      ];

      // Field yang boleh kosong (angka)
      $nullableNumericFields = [
        'ibu_hari','ibu_tarif','ibu_total',
        'bayi_hari','bayi_tarif','bayi_total',   // 🔹 ditambahkan
        'anak_hari','anak_tarif','anak_total',
        'total_bersalin','total_ambulance',
        'jasa_tindakan','total_jasa_tindakan',
        'biaya_visite_1','biaya_visite_2','biaya_visite_3',
        'total_visite_1','total_visite_2','total_visite_3','total_visite_dokter',
        'biaya_tindakan_dokter_1','biaya_tindakan_dokter_2','biaya_tindakan_dokter_3',
        'biaya_asisten_1','biaya_asisten_2','biaya_asisten_3',
        'biaya_instrumen_onlop','total_tindakan_medis','total_tenaga_ahli',
        'biaya_konsul_1','biaya_konsul_2','biaya_konsul_3','total_konsultasi',
        'jumlah_lab_1','jumlah_lab_2','total_laboratorium','jumlah_usg',
        'jumlah_radiologi_tambahan_1','jumlah_radiologi_tambahan_2',
        'total_radiologi','phototerapi_biaya','biaya_suction','biaya_syringe',
        'incubator_biaya','nebulizer_biaya',
        'tindakan_lain_1','tindakan_lain_2',
        'total_tindakan',
        'nst_biaya','ecg_biaya','total_penunjang',
        'total_transfusi_darah','biaya_persalinan','total_prosedur_non_bedah',
        'biaya_obat','total_obat','biaya_kamar_operasi','total_prosedur_bedah',
        'biaya_alkes','alkes_tambahan_1','alkes_tambahan_2','alkes_tambahan_3',
        'total_alkes','biaya_rehabilitasi','total_rehabilitasi',
        'biaya_rawat_intensif','total_rawat_intensif',
        'biaya_bmhp','total_bmhp',
        'total_semua_bagian','uang_muka','potongan','sisa_tagihan',
        'jumlah_ambulance_1','jumlah_ambulance_2',
        'lama_visite_1','lama_visite_2','lama_visite_3',
        'jumlah_konsul_1','jumlah_konsul_2','jumlah_konsul_3',
        'usg_kali','radiologi_tambahan_1','radiologi_tambahan_2',
        'phototerapi_seri','incubator_kali','nebulizer_kali',
        'nst_kali','jumlah_transfusi',
        'total_jasa_rs','total_karcis','total_materai',
        'total_kamar_akomodasi',
        'jenis_bayar_id','kelas_kamar_id'
      ];

      $referensiIdFields = [
        'ambulance_1_id','ambulance_2_id',
        'dokter_visite_1_id','dokter_visite_2_id','dokter_visite_3_id',
        'dokter_tindakan_1_id','dokter_tindakan_2_id','dokter_tindakan_3_id',
        'asisten_tindakan_1_id','asisten_tindakan_2_id','asisten_tindakan_3_id',
        'instrumen_onlop_id',
        'dokter_konsultasi_1_id','dokter_konsultasi_2_id','dokter_konsultasi_3_id',
        'laboratorium_1_id','laboratorium_2_id',
        'jenis_bayar_id','kelas_kamar_id'
      ];

      // 🔧 Ubah '' menjadi null untuk field tanggal
      foreach ($nullableDateFields as $field) {
        if (isset($data[$field]) && trim($data[$field]) === '') {
          $data[$field] = null;
        }
      }

      // 🔧 Ubah '' menjadi null untuk field angka
      foreach ($nullableNumericFields as $field) {
        if (isset($data[$field]) && trim((string)$data[$field]) === '') {
          $data[$field] = null;
        }
      }

      // 🔧 Ubah null jadi 0 untuk angka
      foreach ($nullableNumericFields as $field) {
        if (array_key_exists($field, $data) && $data[$field] === null) {
          $data[$field] = 0;
        }
      }

      // 🔧 Ubah '' atau '0' jadi null untuk ID referensi
      foreach ($referensiIdFields as $field) {
        if (isset($data[$field])) {
          $val = trim((string) $data[$field]);
          $data[$field] = ($val === '' || $val === '0') ? null : (int)$val;
        }
      }

      // 🔧 qr_validasi_path kosong jadi null
      if (isset($data['qr_validasi_path']) && trim($data['qr_validasi_path']) === '') {
        $data['qr_validasi_path'] = null;
      }

      return $data;
  }

  public static function prepareAutoFields($post) {
    return self::normalizeData($post);
  }

  public static function resolveForeignFields(mysqli $conn, array $data): array {
    $rawat_inap_id = $data['rawat_inap_id'] ?? null;
    if (!$rawat_inap_id) return $data;

    $stmt = $conn->prepare("
      SELECT 
        nama_pasien,
        nomor_register,
        nama_jenis_bayar,
        nama_kelas_perawatan,
        jenis_rincian,
        qr_validasi_path
      FROM rawat_inap_rincian
      WHERE rawat_inap_id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
      foreach (['nama_pasien', 'nomor_register', 'nama_jenis_bayar', 'nama_kelas_perawatan', 'jenis_rincian', 'qr_validasi_path'] as $field) {
        if (empty($data[$field])) {
          $data[$field] = $result[$field] ?? null;
        }
      }
    }

    return $data;
  }

  public static function resolveIdFields(mysqli $conn, array $data): array {
    $map = [
      'ambulance_1_id' => ['table' => 'ambulance', 'column' => 'asal_ambulance'],
      'ambulance_2_id' => ['table' => 'ambulance', 'column' => 'asal_ambulance'],
      'dokter_visite_1_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'dokter_visite_2_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'dokter_visite_3_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'dokter_tindakan_1_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'dokter_tindakan_2_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'dokter_tindakan_3_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'asisten_tindakan_1_id' => ['table' => 'asisten_dokter', 'column' => 'nama_asisten'],
      'asisten_tindakan_2_id' => ['table' => 'asisten_dokter', 'column' => 'nama_asisten'],
      'asisten_tindakan_3_id' => ['table' => 'asisten_dokter', 'column' => 'nama_asisten'],
      'instrumen_onlop_id' => ['table' => 'asisten_dokter', 'column' => 'nama_asisten'],
      'dokter_konsultasi_1_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'dokter_konsultasi_2_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'dokter_konsultasi_3_id' => ['table' => 'dokter', 'column' => 'nama_dokter'],
      'laboratorium_1_id' => ['table' => 'laboratorium', 'column' => 'asal_laboratorium'],
      'laboratorium_2_id' => ['table' => 'laboratorium', 'column' => 'asal_laboratorium'],
    ];

    foreach ($map as $field => $info) {
      if (!empty($data[$field]) && !is_numeric($data[$field])) {
        $stmt = $conn->prepare("SELECT id FROM {$info['table']} WHERE {$info['column']} = ? LIMIT 1");
        $stmt->bind_param("s", $data[$field]);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $data[$field] = $result['id'] ?? 0;
      }
    }

    return $data;
  }

  public static function save(mysqli $conn, array $data): bool {
    if (empty($data)) {
      error_log("❌ Data kosong, tidak bisa disimpan.");
      return false;
    }

    // 🔄 Sinkronisasi uang_muka dan potongan
    if (!empty($data['rawat_inap_id'])) {
      $rawatInapId = (int) $data['rawat_inap_id'];
      $data['uang_muka'] = UangMukaModel::getTotalByRawatInap($conn, $rawatInapId);
      $data['potongan']  = PotonganModel::getTotalByRawatInap($conn, $rawatInapId);
    }

    $data = self::normalizeData($data);
    if (empty($data)) return false;

    // 🔧 Generate SQL
    $sql = self::generateSQL('rawat_inap_rincian', $data);
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      error_log("❌ Prepare gagal: " . $conn->error);
      error_log("SQL: $sql");
      return false;
    }

    // 🔗 Bind values
    [$types, $values] = self::bindValues($data);
    if (!$stmt->bind_param($types, ...$values)) {
      error_log("❌ Bind gagal: " . $stmt->error);
      return false;
    }

    // 🚀 Execute
    $success = $stmt->execute();
    if (!$success) {
      error_log("❌ Execute gagal: " . $stmt->error);
      error_log("Data: " . print_r($data, true));
    }

    $stmt->close();
    return $success;

  }

  public static function updateById(mysqli $conn, int $id, array $data): bool {
    if (empty($data)) {
      error_log("❌ Data kosong, tidak bisa update.");
      return false;
    }

    $allowed = self::allowedFields();
    $filtered = array_intersect_key($data, array_flip($allowed));

    if (empty($filtered)) {
      error_log("❌ Tidak ada field valid untuk update.");
      return false;
    }

    // 🔧 Normalisasi field tanggal kosong
    foreach (['anak_mulai', 'anak_selesai', 'ibu_mulai', 'ibu_selesai', 'bayi_mulai', 'bayi_selesai', 'tanggal_pemeriksaan'] as $key) {
      if (array_key_exists($key, $filtered) && empty($filtered[$key])) {
        $filtered[$key] = null;
      }
    }

    // 🔁 Bangun SET clause dan bind list
    $setClauseParts = [];
    $bindFields = [];

    foreach ($filtered as $key => $val) {
      if ($val === null) {
        $setClauseParts[] = "`$key` = NULL";
      } else {
        $setClauseParts[] = "`$key` = ?";
        $bindFields[] = $key;
      }
    }

    $setClause = implode(', ', $setClauseParts);
    $sql = "UPDATE rawat_inap_rincian SET $setClause WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      error_log("❌ Prepare gagal: " . $conn->error);
      error_log("SQL: $sql");
      return false;
    }

    // 🔗 Bind types dan values
    $types = '';
    $values = [];

    foreach ($bindFields as $f) {
      $val = $filtered[$f];
      $types .= is_int($val) ? 'i' : (is_float($val) ? 'd' : 's');
      $values[] = $val;
    }

    $types .= 'i'; // untuk WHERE id
    $values[] = $id;

    if (!$stmt->bind_param($types, ...$values)) {
      error_log("❌ Bind gagal: " . $stmt->error);
      return false;
    }
    error_log("Filtered Data: " . json_encode($filtered));
    error_log("Bind Values: " . json_encode($values));

    $success = $stmt->execute();
    if (!$success) {
      error_log("❌ Execute gagal: " . $stmt->error);
      error_log("SQL: $sql");
      error_log("Bind Types: $types");
      error_log("Bind Values: " . json_encode($values));
      error_log("Filtered Data: " . json_encode($filtered));
    } else {
      error_log("✅ Update berhasil untuk ID: $id");
    }

    $stmt->close();
    return $success;
  }

  public static function insertNewVersion(mysqli $conn, int $rawatInapId, array $data): bool {
    $data['rawat_inap_id'] = $rawatInapId;

    $columns = array_keys($data);
    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $sql = "INSERT INTO rawat_inap_rincian (" . implode(',', $columns) . ") VALUES ($placeholders)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      error_log("❌ Prepare gagal: " . $conn->error);
      error_log("SQL: $sql");
      return false;
    }

    $types = str_repeat('s', count($data)); // bisa diganti helper getBindTypes($data)
    $values = array_values($data);
    $stmt->bind_param($types, ...$values);

    if (!$stmt->execute()) {
      error_log("❌ Gagal insert versi baru: " . $stmt->error);
      error_log("SQL: $sql");
      error_log("Data: " . print_r($data, true));
      return false;
    }

    return true;
  }

  public static function generateSQL(string $table, array $data): string {
      $fields = array_keys($data);
      $placeholders = array_fill(0, count($fields), '?');

      $sql = "INSERT INTO `$table` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
      return $sql;
  }

  private static function bindValues(array $data): array {
    $types = '';
    $values = [];

    foreach ($data as $value) {
      if (is_int($value)) {
        $types .= 'i';
        $values[] = $value;
      } elseif (is_numeric($value)) {
        $types .= 'd';
        $values[] = (float) $value;
      } else {
        $types .= 's';
        $values[] = $value === null ? null : (string) $value;
      }
    }

    return [$types, $values];
  }

  public static function getBindTypes(array $data): string {
    return implode('', array_map(function($v) {
      return is_int($v) ? 'i' : (is_float($v) ? 'd' : 's');
    }, $data));
  }

  public static function getTotalBiaya(mysqli $conn, int $rawat_inap_id): float {
    $totalFields = [
      'total_kamar_akomodasi',
      'total_jasa_tindakan',
      'total_tenaga_ahli',
      'total_konsultasi',
      'total_laboratorium',
      'total_radiologi',
      'total_tindakan',
      'total_penunjang',
      'total_transfusi_darah',
      'total_prosedur_non_bedah',
      'total_obat',
      'total_prosedur_bedah',
      'total_alkes',
      'total_rehabilitasi',
      'total_rawat_intensif',
      'total_bmhp'
    ];

    $validFields = array_filter($totalFields, fn($f) => in_array($f, self::allowedFields()));
    if (empty($validFields)) return 0;

    $sumParts = implode(" + ", array_map(fn($f) => "IFNULL(SUM($f), 0)", $validFields));
    $sql = "SELECT ($sumParts) AS total FROM rawat_inap_rincian WHERE rawat_inap_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;

    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return (float) ($row['total'] ?? 0);
  }

  public static function getRekapBiaya(mysqli $conn, int $rawat_inap_id): array {
    $stmt = $conn->prepare("
      SELECT 
        r.total_semua_bagian,
        IFNULL(um.jumlah, 0) AS uang_muka,
        IFNULL(pot.jumlah, 0) AS potongan,
        (r.total_semua_bagian - IFNULL(um.jumlah, 0) - IFNULL(pot.jumlah, 0)) AS sisa_tagihan,
        p.nama_pasien,
        t.tanggal_masuk,
        r.id
      FROM rawat_inap_rincian r
      JOIN tiket_rawat_inap t ON r.rawat_inap_id = t.id
      JOIN pasien p ON t.pasien_id = p.id
      LEFT JOIN uang_muka_rawat_inap um ON um.rawat_inap_id = t.id
      LEFT JOIN potongan_rawat_inap pot ON pot.rawat_inap_id = t.id
      WHERE r.rawat_inap_id = ?
      LIMIT 1
    ");
    if (!$stmt) return self::emptyRekap();

    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: self::emptyRekap();
  }

  public static function updateByRawatInapId(mysqli $conn, int $rawatInapId, array $data): bool {
    if (empty($data)) {
      error_log("❌ Data kosong, tidak bisa update by rawat_inap_id.");
      return false;
    }

    $allowed = self::allowedFields();
    $filtered = array_filter($data, fn($v, $k) => in_array($k, $allowed), ARRAY_FILTER_USE_BOTH);

    if (empty($filtered)) {
      error_log("❌ Tidak ada field valid untuk update.");
      return false;
   }

    $setClauseParts = [];
    $bindFields = [];

    foreach ($filtered as $key => $val) {
      if ($val === null) {
        $setClauseParts[] = "`$key` = NULL";
      } else {
        $setClauseParts[] = "`$key` = ?";
        $bindFields[] = $key;
      }
    }

    $setClause = implode(', ', $setClauseParts);
    $sql = "UPDATE rawat_inap_rincian SET $setClause WHERE rawat_inap_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      error_log("❌ Prepare gagal: " . $conn->error);
      error_log("SQL: $sql");
      return false;
    }

    $types = '';
    $values = [];

    foreach ($bindFields as $f) {
      $val = $filtered[$f];
      $types .= is_int($val) ? 'i' : (is_float($val) ? 'd' : 's');
      $values[] = $val;
    }

    $types .= 'i'; // untuk rawat_inap_id
    $values[] = $rawatInapId;

    if (!$stmt->bind_param($types, ...$values)) {
      error_log("❌ Bind gagal: " . $stmt->error);
      return false;
    }

    $success = $stmt->execute();
    if (!$success) {
      error_log("❌ Execute gagal: " . $stmt->error);
      error_log("SQL: $sql");
      error_log("Bind Types: $types");
      error_log("Bind Values: " . json_encode($values));
    }

    $stmt->close();
    return $success;
  }

  public static function syncRekap(mysqli $conn, int $rawat_inap_id): void {
    $uang_muka = (float) UangMukaModel::getTotalByRawatInap($conn, $rawat_inap_id);
    $potongan  = (float) PotonganModel::getTotalByRawatInap($conn, $rawat_inap_id);
    $total     = (float) self::getTotalBiaya($conn, $rawat_inap_id);

    // Pastikan tidak negatif
    $sisa = max(0, $total - $uang_muka - $potongan);

    self::updateByRawatInapId($conn, $rawat_inap_id, [
      'uang_muka'     => $uang_muka,
      'potongan'      => $potongan,
      'sisa_tagihan'  => $sisa
    ]);
  }

  private static function emptyRekap(): array {
    return [
      'total_semua_bagian' => 0,
      'uang_muka' => 0,
      'potongan' => 0,
      'sisa_tagihan' => 0,
      'nama_pasien' => '-',
      'tanggal_masuk' => '-',
      'id' => 0
    ];
  }

  public static function getCetakData(mysqli $conn, int $rawat_inap_id): ?array {
    $stmt = $conn->prepare("
      SELECT r.*, r.jenis_rincian AS jenis_rawat, p.nama_pasien, p.nomor_register, t.tanggal_masuk AS tanggal_pemeriksaan,
             k.nama_petugas, k.nip
      FROM rawat_inap_rincian r
      JOIN tiket_rawat_inap t ON t.id = r.rawat_inap_id
      JOIN pasien p ON p.id = t.pasien_id
      LEFT JOIN kasir k ON k.user_id = r.dibuat_oleh
      WHERE r.rawat_inap_id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $rawat_inap_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
  }

  public static function updateQRValidasi(mysqli $conn, int $rawat_inap_id, string $path, int $user_id, string $nip): bool {
    $stmt = $conn->prepare("
      UPDATE rawat_inap_rincian
      SET qr_validasi_path = ?, dibuat_oleh = ?, nip = ?
      WHERE rawat_inap_id = ?
    ");
    $stmt->bind_param("ssii", $path, $user_id, $nip, $rawat_inap_id);
    return $stmt->execute();
}

  public static function getRawatInapIdByRincianId(mysqli $conn, int $rincian_id): ?int {
    $stmt = $conn->prepare("SELECT rawat_inap_id FROM rawat_inap_rincian WHERE id = ?");
    if (!$stmt) return null;

    $stmt->bind_param("i", $rincian_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['rawat_inap_id'] ?? null;
  }

  public static function getTarifKelasKamar(mysqli $conn, string $nama_kelas, string $jenis_pasien): ?float {
    $stmt = $conn->prepare("
      SELECT tarif 
      FROM kelas_kamar 
      WHERE nama_kelas = ? AND jenis_pasien = ?
      LIMIT 1
    ");
    $stmt->bind_param("ss", $nama_kelas, $jenis_pasien);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
      return floatval($row['tarif']);
    }
    return null;
  }

  public static function findById($conn, $id): ?array {
    $stmt = $conn->prepare("SELECT * FROM rawat_inap_rincian WHERE rawat_inap_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
  }

      // === COUNT ALL (dengan pencarian opsional) ===
    public static function countAll(mysqli $conn, string $search = ''): int {
        $sql = "SELECT COUNT(*) AS jml
                FROM rawat_inap_rincian r
                JOIN tiket_rawat_inap t ON r.rawat_inap_id = t.id
                JOIN pasien p ON t.pasien_id = p.id
                WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ?)";
            $stmt = $conn->prepare($sql);
            $like = "%$search%";
            $stmt->bind_param("ss", $like, $like);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return (int)($result['jml'] ?? 0);
        } else {
            $result = $conn->query($sql);
            return $result ? (int)$result->fetch_assoc()['jml'] : 0;
        }
    }

    // === GET PAGINATED (dengan pencarian opsional) ===
    public static function getPaginated(mysqli $conn, int $limit, int $offset, string $search = ''): array {
        $sql = "SELECT r.*, p.nama_pasien, p.nomor_register, t.tanggal_masuk, t.status AS status_tiket
                FROM rawat_inap_rincian r
                JOIN tiket_rawat_inap t ON r.rawat_inap_id = t.id
                JOIN pasien p ON t.pasien_id = p.id
                WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ?)";
        }
        $sql .= " ORDER BY t.tanggal_masuk DESC LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];

        if (!empty($search)) {
            $like = "%$search%";
            $stmt->bind_param("ssii", $like, $like, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

}
?>