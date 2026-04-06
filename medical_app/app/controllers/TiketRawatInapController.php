<?php
require_once __DIR__ . '/../models/TiketRawatInapModel.php';

class TiketRawatInapController {
    private $model;

    public function __construct($db) {
        $this->model = new TiketRawatInapModel($db);
    }

    // === INDEX dengan pagination 10 ===
    public function index() {
        // Ambil halaman aktif dari URL (default 1)
        $page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
        if ($page < 1) $page = 1;

        $limit  = 5; // jumlah baris per halaman
        $offset = ($page - 1) * $limit;
        $search = $_GET['q'] ?? ''; // dukungan pencarian opsional

        // Hitung total data sesuai pencarian
        $totalData = $this->model->countAll($search);
        $totalPage = ceil($totalData / $limit);
        if ($totalPage < 1) $totalPage = 1;

        // Ambil data sesuai halaman + pencarian
        $list = $this->model->getPaginated($limit, $offset, $search);

        // Siapkan variabel untuk view
        $pagination = [
            'page'      => $page,
            'totalPage' => $totalPage,
            'search'    => $search
        ];

        $data = [
            'tiket'      => $list,
            'pagination' => $pagination,
            'offset'     => $offset
        ];

        include __DIR__ . '/../views/tiket_rawat_inap/index.php';
    }

    public function show($id) {
        return $this->model->getById($id);
        // Bisa include detail view jika diperlukan
    }

    public function store($post) {
        session_start(); // pastikan ini ada

        $data = [
            'pasien_id'      => (int) $post['pasien_id'],
            'tanggal_masuk'  => $post['tanggal_masuk'],
            'tanggal_keluar' => !empty($post['tanggal_keluar']) ? $post['tanggal_keluar'] : null,
            'status'         => !empty($post['status']) ? $post['status'] : 'AKTIF',
            'catatan'        => $post['catatan'],
            'dibuat_oleh'    => $_SESSION['nama'] ?? $_SESSION['user']['username'] ?? 'system',
            'dibuat_pada'    => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        $this->model->insert($data);
        // ✅ Redirect ke halaman pasien
        header('Location: index.php?page=pasien');
        exit;
    }

    public function create() {
        global $conn;
        $pasien_id = $_GET['pasien_id'] ?? null;

        // Ambil data pasien
        $stmt = $conn->prepare("SELECT * FROM pasien WHERE id = ?");
        $stmt->bind_param("i", $pasien_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pasien = $result->fetch_assoc();

        include __DIR__ . '/../views/tiket_rawat_inap/create.php';
    }

    public function update($id, $post) {
        $tanggal_keluar = !empty($post['tanggal_keluar']) ? $post['tanggal_keluar'] : null;

        $data = [
            'pasien_id'      => (int) $post['pasien_id'],
            'tanggal_masuk'  => $post['tanggal_masuk'],
            'tanggal_keluar' => $tanggal_keluar,
            'status'         => $post['status'],
            'catatan'        => $post['catatan'],
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        $this->model->update($id, $data);

        // ✅ Redirect ke halaman pasien
        header('Location: index.php?page=pasien');
        exit;
    }

    public function destroy($id) {
        $this->model->delete($id);
    }

    public function getPasienTanpaTiket() {
        return $this->model->getPasienTanpaTiket(); // ✅ fix
    }

    public function getStatusOptions() {
        $result = $this->model->getStatusEnumRaw();
        $row = $result->fetch_assoc();
        $type = $row['Type']; // contoh: enum('AKTIF','SELESAI','BATAL')

        preg_match("/^enum\((.*)\)$/", $type, $matches);
        $values = explode(",", $matches[1]);

        return array_map(function($val) {
            return trim($val, "'");
        }, $values);
    }

    public function ajaxSearch() {
        $keyword = $_GET['q'] ?? '';
        $results = $this->model->getPaginated(10, 0, $keyword);

        $no = 1;
        foreach ($results as $row) {
            $statusRaw  = $row['status'] ?? 'UNKNOWN';
            $status     = strtoupper($statusRaw);
            $badgeClass = match ($status) {
                'AKTIF'    => 'success',
                'SELESAI'  => 'secondary',
                'MENUNGGU' => 'warning',
                'BATAL'    => 'danger',
                default    => 'dark'
            };

            echo "<tr>
                    <td>{$no}</td>
                    <td>".htmlspecialchars($row['nomor_register'])."</td>
                    <td>".htmlspecialchars($row['nama_pasien'])."</td>
                    <td>".htmlspecialchars($row['tanggal_masuk'])."</td>
                    <td><span class='badge bg-{$badgeClass}'>".$status."</span></td>
                    <td>".htmlspecialchars($row['catatan'])."</td>
                    <td>".htmlspecialchars($row['dibuat_oleh'])."</td>
                    <td>".htmlspecialchars($row['dibuat_pada'])."</td>
                    <td><a href='index.php?page=tiket_rawat_inap&action=edit&id={$row['id']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a></td>
                  </tr>";
            $no++;
        }

        if (empty($results)) {
            echo "<tr><td colspan='9' class='text-center text-muted'>Tidak ada hasil</td></tr>";
        }

        exit;
    }
    
}