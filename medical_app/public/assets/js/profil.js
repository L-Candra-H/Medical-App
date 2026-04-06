document.addEventListener("DOMContentLoaded", function () {
  // Validasi konfirmasi password
  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", function (e) {
      const pw = document.querySelector("[name=new_password]").value;
      const cpw = document.querySelector("[name=confirm_password]").value;
      if (pw && pw !== cpw) {
        e.preventDefault();
        Swal.fire("Password tidak sama!", "Pastikan kedua field password cocok.", "error");
      }
    });
  }

  // Toggle ikon mata
  document.querySelectorAll('.toggle-password').forEach(function (icon) {
    icon.addEventListener('click', function () {
      const input = document.querySelector(this.dataset.target);
      if (!input) return;
      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });
  });

  console.log("profil.js aktif");
});
