<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="index.php?page=profil" class="brand-link text-white text-center">
    <img src="assets/adminlte/img/avatar-default.png" class="brand-image img-circle elevation-3" alt="Avatar">
    <span class="brand-text font-weight-light"><?= strtoupper($_SESSION['nama'] ?? 'PENGGUNA') ?></span>
    <!-- Tambahan versi aplikasi -->
    <span style="color:gold; font-weight:bold; display:block; font-size:0.60em; margin-top:2px;">
      © <?= date('Y') ?> @Medical App - versi : 2.0
    </span>
  </a>

  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <!-- Menu Dashboard -->
        <li class="nav-item">
          <a href="index.php?page=dashboard" class="nav-link <?= ($_GET['page'] ?? '') == 'dashboard' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Menu Pengaturan -->
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-cogs"></i>
            <p>Pengaturan <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li><a href="index.php?page=institusi" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Institusi</p></a></li>
            <li><a href="index.php?page=jabatan" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Jabatan (Read Only)</p></a></li>
            <li><a href="index.php?page=jenis_bayar" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Jenis Bayar</p></a></li>
            <li><a href="index.php?page=kelas_kamar" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Kelas Kamar</p></a></li>
            <li><a href="index.php?page=laboratorium" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Laboratorium</p></a></li>
            <li><a href="index.php?page=ambulance" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Ambulance</p></a></li>
          </ul>
        </li>

        <!-- Menu Input Data -->
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-plus"></i>
            <p>Input Data <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li><a href="index.php?page=kasir" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Petugas/Kasir</p></a></li>
            <li><a href="index.php?page=dokter" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Dokter</p></a></li>
            <li><a href="index.php?page=asisten" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Asisten</p></a></li>
            <li><a href="index.php?page=pasien" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pasien</p></a></li>
          </ul>
        </li>

        <!-- Menu Rawat Inap -->
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-notes-medical"></i>
            <p>Rawat Inap <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li>
              <a href="index.php?page=tiket_rawat_inap" class="nav-link <?= $page === 'tiket_rawat_inap' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Tiket Rawat Inap</p>
              </a>
            </li>
            <li>
              <a href="index.php?page=uang_muka&action=admin" class="nav-link <?= $page === 'uang_muka' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Uang Muka</p>
              </a>
            </li>
            <li>
              <a href="index.php?page=potongan&action=list" class="nav-link <?= $page === 'potongan' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Potongan</p>
              </a>
            </li>
            <li>
              <a href="index.php?page=rincian_rawat_inap" class="nav-link <?= $page === 'rincian_rawat_inap' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Rincian Rawat Inap</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Menu Laporan -->
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Laporan <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li>
              <a href="index.php?page=rekap_dokter" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Rekap HR Dokter</p>
              </a>
            </li>
            <li>
              <a href="index.php?page=rekap_asisten" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Rekap HR Asisten</p>
              </a>
            </li>
                        <li class="nav-item">
              <a href="index.php?page=jurnal_input" class="nav-link">
                <i class="far fa-edit nav-icon"></i>
                <p>Input Jurnal</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="index.php?page=jurnal_history" class="nav-link">
                <i class="far fa-clock nav-icon"></i>
                <p>History Jurnal</p>
              </a>
            </li>

          </ul>
        </li>

        <!-- Menu Logout -->
        <li class="nav-item mt-4">
          <a href="logout.php" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>