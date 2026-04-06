<?php
$isDashboard = false;
$isCetak = false;
include(__DIR__ . '/../layout/header.php');
include(__DIR__ . '/../layout/sidebar.php');

$profil = $profil ?? [];
$role = strtolower($profil['role'] ?? '');
$isSuperadmin = $role === 'superadmin';
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1 class="mt-2">Profil Pengguna</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- Card Informasi Profil -->
      <div class="card card-info">
        <div class="card-header bg-primary text-white">
          <strong>👤 Profil Saya</strong>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Kiri: Detail Profil -->
            <div class="col-md-8 profil-detail">
              <dl class="row">
                <dt class="col-sm-4">Nama Lengkap</dt>
                <dd class="col-sm-8">
                  <strong><?= $isSuperadmin ? 'ADMIN' : htmlspecialchars($profil['nama_petugas'] ?? '-') ?></strong>
                </dd>

                <?php if (!$isSuperadmin): ?>
                  <dt class="col-sm-4">NIP</dt>
                  <dd class="col-sm-8"><?= htmlspecialchars($profil['nip'] ?? '-') ?></dd>

                  <dt class="col-sm-4">Jabatan</dt>
                  <dd class="col-sm-8"><?= htmlspecialchars($profil['nama_jabatan'] ?? '-') ?></dd>
                <?php endif; ?>

                <dt class="col-sm-4">Username</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($profil['username'] ?? '-') ?></dd>

                <dt class="col-sm-4">Login Terakhir</dt>
                <dd class="col-sm-8"><?= $_SESSION['last_login'] ?? '-' ?></dd>
              </dl>
            </div>

            <!-- Kanan: QR Code (hidden untuk SUPERADMIN) -->
            <?php if (!$isSuperadmin): ?>
              <div class="col-md-4 text-center align-self-center">
                <?php if (!empty($profil['qr_path'])): ?>
                  <img src="<?= htmlspecialchars($profil['qr_path']) ?>" alt="QR Kasir" width="180" />
                  <p class="mt-1">
                    <a href="<?= htmlspecialchars($profil['qr_path']) ?>" download class="btn btn-outline-primary btn-sm">
                      ⬇️ Unduh QR Code
                    </a>
                  </p>
                <?php else: ?>
                  <em>QR belum tersedia</em>
                <?php endif; ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

      <!-- Form Ganti Password hanya untuk USER biasa -->
      <?php if (!$isSuperadmin): ?>
      <div class="card card-outline card-warning mt-3">
        <div class="card-header">
          <strong>🔐 Ganti Password</strong>
        </div>
        <form method="POST" action="index.php?page=profil&action=update">
          <div class="card-body">

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Password Baru</label>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="password" name="new_password" id="new_password" class="form-control" placeholder="••••••••" required>
                  <div class="input-group-append">
                    <span class="input-group-text" style="cursor:pointer;">
                      <i class="fas fa-eye toggle-password" data-target="#new_password"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Konfirmasi Password</label>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                  <div class="input-group-append">
                    <span class="input-group-text" style="cursor:pointer;">
                      <i class="fas fa-eye toggle-password" data-target="#confirm_password"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-md-6 offset-md-4 text-right">
                <button type="submit" class="btn btn-warning">Update</button>
              </div>
            </div>

          </div>
        </form>
      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<script>
  document.querySelectorAll('.toggle-password').forEach(function (icon) {
    icon.addEventListener('click', function () {
      const target = document.querySelector(this.dataset.target);
      if (target) {
        const isHidden = target.type === 'password';
        target.type = isHidden ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      }
    });
  });
</script>

<?php include(__DIR__ . '/../layout/footer.php'); ?>
