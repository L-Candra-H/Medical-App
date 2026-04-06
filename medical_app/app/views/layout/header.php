<?php include_once(__DIR__ . '/../../helpers/sessionGuard.php'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'MedicalApp' ?></title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/adminlte/plugins/fontawesome-free/css/all.min.css">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="assets/adminlte/plugins/bootstrap/css/bootstrap.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="assets/adminlte/css/adminlte.min.css">
  <!-- Custom -->
  <?php if (!empty($isDashboard)): ?>
    <link rel="stylesheet" href="assets/css/dashboard.css?v=1.0">
  <?php elseif (!empty($isCetak)): ?>
    <link rel="stylesheet" href="assets/css/cetak.css?v=1.0">
  <?php else: ?>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
  <?php endif; ?>

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
