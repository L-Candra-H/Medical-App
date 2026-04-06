document.addEventListener('DOMContentLoaded', function () {
  // Konfirmasi sebelum hapus tiket
  const deleteButtons = document.querySelectorAll('.btn-delete-tiket');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      if (confirm('Yakin ingin menghapus tiket ini?')) {
        window.location.href = `index.php?page=tiket_rawat_inap&action=delete&id=${id}`;
      }
    });
  });

  // Validasi form sebelum submit
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function (e) {
      const pasienId = form.querySelector('[name="pasien_id"]');
      const tanggalMasuk = form.querySelector('[name="tanggal_masuk"]');
      const status = form.querySelector('[name="status"]');

      if (!pasienId.value || !tanggalMasuk.value || !status.value) {
        alert('Pastikan semua field wajib sudah diisi.');
        e.preventDefault();
      }
    });
  }

  // Cleanup backdrop saat modal ditutup
  const modalInap = document.getElementById('modalInap');
  if (modalInap) {
    modalInap.addEventListener('hidden.bs.modal', function () {
      document.body.classList.remove('modal-open');
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(b => b.remove());
    });
  }
});
