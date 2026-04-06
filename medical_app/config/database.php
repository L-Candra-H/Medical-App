<?php
$host = 'localhost';
$user = 'root';
$password = ''; // isi jika pakai password
$database = 'medical_app';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
  die('Koneksi database gagal: ' . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');

return $conn;
