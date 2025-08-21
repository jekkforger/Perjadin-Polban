// review-surat-tugas.js
// Requires: signature_pad@4.0.0, Bootstrap 5

document.addEventListener('DOMContentLoaded', function() {
    const btnSetujui = document.getElementById('btnSetujui');
    const modalKonfirmasiSumberDana = new bootstrap.Modal(document.getElementById('modalKonfirmasiSumberDana'));
    const modalEditSumberDana = new bootstrap.Modal(document.getElementById('modalEditSumberDana'));
    const modalSignaturePad = new bootstrap.Modal(document.getElementById('modalSignaturePad'));
    const modalSignaturePosition = new bootstrap.Modal(document.getElementById('modalSignaturePosition'));
    const btnEditSumberDana = document.getElementById('btnEditSumberDana');
    const btnKonfirmasiSetuju = document.getElementById('btnKonfirmasiSetuju');
    const btnSimpanSumberDana = document.getElementById('btnSimpanSumberDana');
    const formProsesReview = document.querySelector('form[action*="process-review"]');

    // Initialize Signature Pad
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)',
        velocityFilterWeight: 0.7,
        minWidth: 0.5,
        maxWidth: 2.5,
        throttle: 16,
        minDistance: 5
    });

    // Position controls
    let signatureData = '';
    let signaturePosition = { x: 0, y: -15, width: 80, height: 60 };

    // Clear signature
    document.getElementById('clearSignature').addEventListener('click', function() {
        signaturePad.clear();
    });

    // Menampilkan modal konfirmasi saat tombol "Setujui" diklik
    if (btnSetujui) {
        btnSetujui.addEventListener('click', function() {
            modalKonfirmasiSumberDana.show();
        });
    }

    // Menampilkan modal edit sumber dana saat "Edit Sumber Dana" di modal konfirmasi diklik
    if (btnEditSumberDana) {
        btnEditSumberDana.addEventListener('click', function() {
            modalKonfirmasiSumberDana.hide();
            modalEditSumberDana.show();
        });
    }

    // Aksi Setujui tanpa perubahan sumber dana - menampilkan signature pad
    if (btnKonfirmasiSetuju) {
        btnKonfirmasiSetuju.addEventListener('click', function() {
            modalKonfirmasiSumberDana.hide();
            modalSignaturePad.show();
        });
    }

    // Aksi Simpan Sumber Dana (menampilkan signature pad)
    if (btnSimpanSumberDana) {
        btnSimpanSumberDana.addEventListener('click', function() {
            const selectedSumberDana = document.querySelector('input[name="sumber_dana_baru"]:checked');
            if (selectedSumberDana) {
                modalEditSumberDana.hide();
                modalSignaturePad.show();
            } else {
                alert('Mohon pilih sumber dana baru.');
            }
        });
    }

    // Save signature and move to position modal
    document.getElementById('saveSignature').addEventListener('click', function() {
        if (signaturePad.isEmpty()) {
            alert('Mohon buat tanda tangan terlebih dahulu.');
            return;
        }

        signatureData = signaturePad.toDataURL();
        modalSignaturePad.hide();
        modalSignaturePosition.show();

        // Set signature preview
        document.getElementById('signaturePreview').src = signatureData;
        updateSignaturePosition();
    });

    // Back to signature modal
    document.getElementById('backToSignature').addEventListener('click', function() {
        modalSignaturePosition.hide();
        modalSignaturePad.show();
    });

    // Position controls
    const positionX = document.getElementById('positionX');
    const positionY = document.getElementById('positionY');
    const signatureWidth = document.getElementById('signatureWidth');
    const signatureHeight = document.getElementById('signatureHeight');
    const draggableSignature = document.getElementById('draggableSignature');

    function updateSignaturePosition() {
        const x = parseInt(positionX.value);
        const y = parseInt(positionY.value);
        const width = parseInt(signatureWidth.value);
        const height = parseInt(signatureHeight.value);

        signaturePosition = { x, y, width, height };

        // Update draggable element
        draggableSignature.style.right = (120 - x) + 'px';
        draggableSignature.style.top = (300 + y) + 'px';
        draggableSignature.style.width = width + 'px';
        draggableSignature.style.height = height + 'px';

        // Update value displays
        document.getElementById('positionXValue').textContent = x + 'px';
        document.getElementById('positionYValue').textContent = y + 'px';
        document.getElementById('signatureWidthValue').textContent = width + 'px';
        document.getElementById('signatureHeightValue').textContent = height + 'px';
    }

    // Event listeners for controls
    positionX.addEventListener('input', updateSignaturePosition);
    positionY.addEventListener('input', updateSignaturePosition);
    signatureWidth.addEventListener('input', updateSignaturePosition);
    signatureHeight.addEventListener('input', updateSignaturePosition);

    // Reset position
    document.getElementById('resetPosition').addEventListener('click', function() {
        positionX.value = 0;
        positionY.value = -15;
        signatureWidth.value = 80;
        signatureHeight.value = 60;
        updateSignaturePosition();
    });

    // Drag functionality
    let isDragging = false;
    let startX, startY, initialRight, initialTop;

    draggableSignature.addEventListener('mousedown', function(e) {
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;

        const rect = draggableSignature.getBoundingClientRect();
        const parentRect = document.getElementById('documentPreview').getBoundingClientRect();

        initialRight = parentRect.right - rect.right;
        initialTop = rect.top - parentRect.top;

        draggableSignature.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;

        const deltaX = startX - e.clientX;
        const deltaY = e.clientY - startY;

        const newRight = initialRight + deltaX;
        const newTop = initialTop + deltaY;

        draggableSignature.style.right = newRight + 'px';
        draggableSignature.style.top = newTop + 'px';

        // Update sliders
        const newX = 120 - newRight;
        const newY = newTop - 300;

        positionX.value = Math.max(-200, Math.min(200, newX));
        positionY.value = Math.max(-100, Math.min(100, newY));

        updateSignaturePosition();
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            draggableSignature.style.cursor = 'move';
        }
    });

    // Final save
    document.getElementById('finalSaveSignature').addEventListener('click', function() {
        if (!signatureData) {
            alert('Tanda tangan tidak ditemukan.');
            return;
        }

        // Get selected sumber dana if any
        const selectedSumberDana = document.querySelector('input[name="sumber_dana_baru"]:checked');

        // Hide position modal
        modalSignaturePosition.hide();

        // Create hidden inputs
        const hiddenSignatureInput = document.createElement('input');
        hiddenSignatureInput.type = 'hidden';
        hiddenSignatureInput.name = 'signature_data';
        hiddenSignatureInput.value = signatureData;
        formProsesReview.appendChild(hiddenSignatureInput);

        const hiddenPositionInput = document.createElement('input');
        hiddenPositionInput.type = 'hidden';
        hiddenPositionInput.name = 'signature_position';
        hiddenPositionInput.value = JSON.stringify(signaturePosition);
        formProsesReview.appendChild(hiddenPositionInput);

        if (selectedSumberDana) {
            const hiddenSumberDanaInput = document.createElement('input');
            hiddenSumberDanaInput.type = 'hidden';
            hiddenSumberDanaInput.name = 'updated_sumber_dana';
            hiddenSumberDanaInput.value = selectedSumberDana.value;
            formProsesReview.appendChild(hiddenSumberDanaInput);
        }

        // Add action approve
        const hiddenActionInput = document.createElement('input');
        hiddenActionInput.type = 'hidden';
        hiddenActionInput.name = 'action';
        hiddenActionInput.value = 'approve';
        formProsesReview.appendChild(hiddenActionInput);

        // Submit form
        formProsesReview.submit();
    });

    // Resize canvas when modal is shown
    document.getElementById('modalSignaturePad').addEventListener('shown.bs.modal', function() {
        signaturePad.clear();
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }
        resizeCanvas();
    });

    // Initialize position modal when shown
    document.getElementById('modalSignaturePosition').addEventListener('shown.bs.modal', function() {
        updateSignaturePosition();
    });
});