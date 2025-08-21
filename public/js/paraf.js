// public/js/paraf.js

document.addEventListener('DOMContentLoaded', function() {
    // Tampilkan modal upload jika ada error validasi dari sesi
    if (typeof showUploadModalOnLoad !== 'undefined' && showUploadModalOnLoad === true) {
        var uploadModal = new bootstrap.Modal(document.getElementById('uploadParafModal'));
        uploadModal.show();
    }

    // Logika untuk Drag & Drop dan File Input
    // Logika untuk Drag & Drop dan File Input
    const parafFileInput = document.getElementById('paraf_file'); // ID target
    const parafDropArea = document.querySelector('.paraf-drop-area'); // Class target
    const selectedFileNameSpan = document.getElementById('selected-file-name'); // ID target
    const filePreviewArea = document.getElementById('file-preview-area'); // ID target
    const removeFileBtn = document.getElementById('remove-file-btn'); // ID target
    const uploadModalElement = document.getElementById('uploadParafModal'); // ID target

    // Referensi untuk form upload dan tombol submit
    const parafUploadForm = document.getElementById('parafUploadForm'); // ID target

    // Debugging: Pastikan semua elemen ditemukan
    if (!parafFileInput) console.error("Elemen 'paraf_file' (input hidden) tidak ditemukan!");
    // ... (other console.error checks) ...

    if (parafFileInput && parafDropArea && selectedFileNameSpan && filePreviewArea && removeFileBtn && uploadModalElement && parafUploadForm) {
        // ... (preventDefaults, highlight, unhighlight functions) ...

        // Handle file drop
        parafDropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            console.log('File dropped. Initial files:', files);
            if (files.length > 0) {
                // ... (validation logic) ...
                const dataTransfer = new DataTransfer(); // Create new DataTransfer
                dataTransfer.items.add(file); // Add the validated file
                parafFileInput.files = dataTransfer.files; // Assign FileList to input
                
                console.log('File successfully assigned to input via drop. Current input.files:', parafFileInput.files);
                displaySelectedFile(); // Update tampilan
            }
        }

        // Handle file selection via browse click (ketika label di-klik)
        const parafBrowseLink = parafDropArea.querySelector('.paraf-browse-link');
        if (parafBrowseLink) {
            parafBrowseLink.addEventListener('click', function(e) {
                e.preventDefault();
                parafFileInput.click();
                console.log('Browse link clicked. Input file click triggered.');
            });
        }
        
        // Handle file selected via input (baik dari drop atau browse)
        parafFileInput.addEventListener('change', function() {
            console.log('parafFileInput change event fired. Files:', this.files);
            // ... (validation logic) ...
            displaySelectedFile(); // Always update display
        });

        /**
         * Memperbarui tampilan nama file yang dipilih.
         */
        function displaySelectedFile() {
            console.log('displaySelectedFile called. Checking parafFileInput.files:', parafFileInput.files);
            if (parafFileInput.files && parafFileInput.files.length > 0) {
                selectedFileNameSpan.textContent = parafFileInput.files[0].name; // THIS IS THE CRUCIAL LINE
                filePreviewArea.style.display = 'block';
                console.log('Displaying file name:', parafFileInput.files[0].name);
            } else {
                selectedFileNameSpan.textContent = '';
                filePreviewArea.style.display = 'none';
                console.log('No file selected, hiding preview.');
            }
        }

        // ... (remove button, modal hidden, AJAX submit logic) ...

    } else {
        console.warn("One or more essential elements for paraf upload modal not found. Check IDs and classes.");
    }

    // === Logika Modal Konfirmasi Hapus Paraf (existing) ===
    const deleteParafModal = document.getElementById('deleteParafModal');
    if (deleteParafModal) {
        deleteParafModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const parafId = button.getAttribute('data-paraf-id');
            const parafName = button.getAttribute('data-paraf-name');
            
            const parafFileNamePlaceholder = document.getElementById('parafFileName');
            if (parafFileNamePlaceholder) { parafFileNamePlaceholder.textContent = parafName; }

            const confirmDeleteBtn = document.getElementById('confirmDeleteParafBtn');
            if (confirmDeleteBtn) { confirmDeleteBtn.setAttribute('data-paraf-id', parafId); }
        });

        const confirmDeleteParafBtn = document.getElementById('confirmDeleteParafBtn');
        if (confirmDeleteParafBtn) {
            confirmDeleteParafBtn.addEventListener('click', function() {
                const parafIdToDelete = this.getAttribute('data-paraf-id');
                if (parafIdToDelete) {
                    const deleteForm = document.getElementById(`delete-paraf-form-${parafIdToDelete}`);
                    if (deleteForm) {
                        deleteForm.submit();
                    } else {
                        console.error('Delete form not found for paraf ID:', parafIdToDelete);
                        Swal.fire('Error!', 'Gagal menghapus paraf. Form tidak ditemukan.', 'error');
                    }
                } else {
                    console.error('Paraf ID not found for delete confirmation.');
                    Swal.fire('Error!', 'ID Paraf tidak ditemukan. Gagal menghapus.', 'error');
                }
            });
        }
    }
});