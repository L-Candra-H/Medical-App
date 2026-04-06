<?php
class TiketRawatInapModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // === COUNT ALL (dengan pencarian opsional) ===
    public function countAll(string $search = ''): int {
        $sql = "SELECT COUNT(*) AS jml
                FROM tiket_rawat_inap t
                JOIN pasien p ON t.pasien_id = p.id
                WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ? OR t.status LIKE ?)";
            $stmt = $this->db->prepare($sql);
            $like = "%$search%";
            $stmt->bind_param("sss", $like, $like, $like);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return (int)($result['jml'] ?? 0);
        } else {
            $result = $this->db->query($sql);
            return $result ? (int)$result->fetch_assoc()['jml'] : 0;
        }
    }

    // === GET PAGINATED (dengan pencarian opsional) ===
    public function getPaginated(int $limit, int $offset, string $search = ''): array {
        $sql = "SELECT t.*, 
                       p.nama_pasien, 
                       p.nomor_register,
                       (SELECT COUNT(*) FROM rawat_inap_rincian r WHERE r.rawat_inap_id = t.id) AS rincian_count
                FROM tiket_rawat_inap t
                JOIN pasien p ON t.pasien_id = p.id
                WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (p.nama_pasien LIKE ? OR p.nomor_register LIKE ? OR t.status LIKE ?)";
        }
        $sql .= " ORDER BY t.dibuat_pada DESC LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        if (!empty($search)) {
            $like = "%$search%";
            $stmt->bind_param("sssii", $like, $like, $like, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // === GET ALL (tanpa pagination) ===
    public function getAll(): array {
        $query = "
            SELECT t.*, 
                   p.nama_pasien, 
                   p.nomor_register,
                   (SELECT COUNT(*) FROM rawat_inap_rincian r WHERE r.rawat_inap_id = t.id) AS rincian_count
            FROM tiket_rawat_inap t
            JOIN pasien p ON t.pasien_id = p.id
            ORDER BY t.dibuat_pada DESC
        ";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // === GET BY ID ===
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, p.nama_pasien, p.nomor_register
            FROM tiket_rawat_inap t
            JOIN pasien p ON t.pasien_id = p.id
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    // === GET AKTIF ===
    public function getAktif(): array {
        $query = "
            SELECT t.*, p.nama_pasien, p.nomor_register
            FROM tiket_rawat_inap t
            JOIN pasien p ON t.pasien_id = p.id
            WHERE t.status = 'AKTIF'
            ORDER BY t.tanggal_masuk DESC
        ";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // === INSERT ===
    public function insert($data) {
        $stmt = $this->db->prepare("
            INSERT INTO tiket_rawat_inap 
            (pasien_id, tanggal_masuk, tanggal_keluar, status, catatan, dibuat_oleh, dibuat_pada, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isssssss",
            $data['pasien_id'],
            $data['tanggal_masuk'],
            $data['tanggal_keluar'],
            $data['status'],
            $data['catatan'],
            $data['dibuat_oleh'],
            $data['dibuat_pada'],
            $data['updated_at']
        );
        return $stmt->execute();
    }

    // === UPDATE ===
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE tiket_rawat_inap SET 
            pasien_id = ?, tanggal_masuk = ?, tanggal_keluar = ?, status = ?, catatan = ?, updated_at = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "isssssi",
            $data['pasien_id'],
            $data['tanggal_masuk'],
            $data['tanggal_keluar'],
            $data['status'],
            $data['catatan'],
            $data['updated_at'],
            $id
        );
        return $stmt->execute();
    }

    // === DELETE ===
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM tiket_rawat_inap WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // === PASIEN TANPA TIKET ===
    public function getPasienTanpaTiket() {
        $query = "
            SELECT p.* 
            FROM pasien p
            LEFT JOIN tiket_rawat_inap t ON p.id = t.pasien_id AND t.status = 'AKTIF'
            WHERE t.id IS NULL
        ";
        return $this->db->query($query);
    }

    // === ENUM STATUS ===
    public function getStatusEnum(): array {
        $result = $this->getStatusEnumRaw();
        if (!$result) return [];
        $row = $result->fetch_assoc();
        preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
        $values = str_getcsv($matches[1], ',', "'");
        return $values;
    }

    public function getStatusEnumRaw(): ?mysqli_result {
        return $this->db->query("SHOW COLUMNS FROM tiket_rawat_inap LIKE 'status'");
    }

    // === FULL BY ID ===
    public function getFullById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, p.nama_pasien, p.nomor_register
            FROM tiket_rawat_inap t
            LEFT JOIN pasien p ON t.pasien_id = p.id
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    // === CEK SELESAI ===
    public function isSelesai(int $tiket_id): bool {
        $stmt = $this->db->prepare("
            SELECT status FROM tiket_rawat_inap
            WHERE id = ? LIMIT 1
        ");
        $stmt->bind_param("i", $tiket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return strtoupper($row['status'] ?? '') === 'SELESAI';
    }
}