document.addEventListener('DOMContentLoaded', function() {
    // ========== EDIT TEMPLATE ==========

    const form = document.getElementById('templateSettingsForm');
    const formInputs = form.querySelectorAll('input, textarea');
    const editSaveButton = document.getElementById('editSaveButton');
    let isEditMode = false;  // Flag to track whether we are in edit mode or not

    // Fungsi untuk mengatur status disabled pada form input
    function setFormDisabled(disabled) {
        formInputs.forEach(input => {
            // Jangan disable tombol submit itu sendiri
            if (input.type !== 'submit' && input.id !== 'editSaveButton') {
                input.disabled = disabled;
            }
        });
        // Atur status disabled pada textarea juga
        form.querySelector('textarea[name="tembusan_default"]').disabled = disabled;
    }

    // Fungsi untuk toggle mode edit/simpan
    editSaveButton.addEventListener('click', function(event) {
        event.preventDefault();  // Prevent form submission when clicking "Edit" or "Simpan"

        if (!isEditMode) {
            // Masuk mode edit
            setFormDisabled(false); // Enable semua input
            this.textContent = 'Simpan'; // Ubah teks tombol menjadi Simpan
            this.type = 'button'; // Ubah tipe tombol menjadi button (bukan submit)
            this.classList.remove('btn-primary'); // Hapus warna default
            this.classList.add('btn-success'); // Ganti warna jadi hijau (atau sesuaikan)
            isEditMode = true;  // Set flag edit mode ke true
        } else {
            // Mode simpan
            // Validasi sisi klien HTML5
            if (!form.reportValidity()) {
                // Jika ada input required yang kosong, browser akan menampilkan pesan validasi
                return;
            }
            // Jika valid, baru submit form
            form.submit();  // Submit form jika validasi berhasil
        }
    });

    // Set form disabled saat pertama kali halaman dimuat
    setFormDisabled(true);

    // ========== DELETE MODAL ==========

    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            const form = document.getElementById('deleteForm');
            const namePlaceholder = document.getElementById('userName');

            form.action = `/admin/akun/${userId}`;
            namePlaceholder.textContent = userName;
        });
    }

    // ========== TOGGLE PASSWORD ==========

    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    if (togglePassword && passwordInput && togglePasswordIcon) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePasswordIcon.classList.toggle('bi-eye');
            togglePasswordIcon.classList.toggle('bi-eye-slash');
        });
    }

    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const toggleConfirmPasswordIcon = document.getElementById('toggleConfirmPasswordIcon');

    if (toggleConfirmPassword && confirmPasswordInput && toggleConfirmPasswordIcon) {
        toggleConfirmPassword.addEventListener('click', () => {
            const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
            confirmPasswordInput.type = type;
            toggleConfirmPasswordIcon.classList.toggle('bi-eye');
            toggleConfirmPasswordIcon.classList.toggle('bi-eye-slash');
        });
    }

    // ========== TOGGLE PENGUSUL FIELDS ==========

    function togglePengusulFields() {
        const role = document.getElementById('role').value;
        const isPengusul = role === 'pengusul';

        const kodePengusulField = document.getElementById('kode-pengusul-field');
        const unitKerjaField = document.getElementById('unit-kerja-field');

        if (kodePengusulField && unitKerjaField) {
            kodePengusulField.classList.toggle('d-none', !isPengusul);
            unitKerjaField.classList.toggle('d-none', !isPengusul);
        }
    }

    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        togglePengusulFields(); // inisialisasi saat load
        roleSelect.addEventListener('change', togglePengusulFields);
    }

    // ========== TOGGLE AKTIF STATUS (PEGAWAI) + LABEL UPDATE ==========

    document.querySelectorAll('.toggle-aktif').forEach(function (checkbox) {
        const label = checkbox.closest('.form-switch').querySelector('.status-label');

        // Set label awal
        label.textContent = checkbox.checked ? 'Aktif' : 'Tidak Aktif';

        checkbox.addEventListener('change', function () {
            const id = this.dataset.id;
            const status = this.checked ? 1 : 0;

            // Update label teks langsung saat toggle
            label.textContent = this.checked ? 'Aktif' : 'Tidak Aktif';

            fetch(`/pegawai/${id}/toggle-aktif`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ aktif: status }),
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memperbarui status');
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat mengubah status');
                console.error(error);
            });
        });
    });
});