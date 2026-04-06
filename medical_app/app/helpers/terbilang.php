<?php
function terbilang($angka, $isNested = false, $suffix = "Rupiah") {
  if (!is_numeric($angka)) return "Tidak valid";
  $angka = abs($angka);
  $kata = [
    "", "Satu", "Dua", "Tiga", "Empat", "Lima",
    "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"
  ];
  
  $hasil = "";

  if ($angka < 12) {
    $hasil = $kata[$angka];
  } elseif ($angka < 20) {
    $hasil = $kata[$angka - 10] . " Belas";
  } elseif ($angka < 100) {
    $hasil = $kata[(int)($angka / 10)] . " Puluh " . $kata[$angka % 10];
  } elseif ($angka < 200) {
    $hasil = "Seratus " . terbilang($angka - 100, true);
  } elseif ($angka < 1000) {
    $hasil = $kata[(int)($angka / 100)] . " Ratus " . terbilang($angka % 100, true);
  } elseif ($angka < 2000) {
    $hasil = "Seribu " . terbilang($angka - 1000, true);
  } elseif ($angka < 1000000) {
    $hasil = terbilang((int)($angka / 1000), true) . " Ribu " . terbilang($angka % 1000, true);
  } elseif ($angka < 1000000000) {
    $hasil = terbilang((int)($angka / 1000000), true) . " Juta " . terbilang($angka % 1000000, true);
  } elseif ($angka < 1000000000000) {
    $hasil = terbilang((int)($angka / 1000000000), true) . " Miliar " . terbilang($angka % 1000000000, true);
  } elseif ($angka < 1000000000000000) {
    $hasil = terbilang((int)($angka / 1000000000000), true) . " Triliun " . terbilang($angka % 1000000000000, true);
  } else {
    $hasil = "Angka terlalu besar";
  }

  return preg_replace('/\s+/', ' ', trim($hasil)) . (!$isNested ? " $suffix" : "");
}

function formatAngka($val) {
  return is_numeric($val) ? number_format((float)$val, 0, '', '') : '0';
}