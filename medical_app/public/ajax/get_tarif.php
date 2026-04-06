<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ⬇️ Naik 2 folder dari /public/ajax/
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/master/KelasKamarModel.php';

$id = $_GET['id'] ?? null;
$tarif = $id ? KelasKamarModel::getTarifById($conn, $id) : 0;

header('Content-Type: application/json');
echo json_encode(['tarif' => (int)$tarif]);
