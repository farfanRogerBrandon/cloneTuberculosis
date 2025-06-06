document.addEventListener('DOMContentLoaded', function() {
    const transferModal = document.getElementById('transferModal');
    const transferNewEstablishment = document.getElementById('transferNewEstablishment');
    const transferDocument = document.getElementById('transferDocument');
    const transferPreview = document.getElementById('transferPreview');
    const transferForm = document.getElementById('transferForm');
    const transferHistoryBody = document.getElementById('transferHistoryBody');
    const confirmTransferBtn = document.getElementById('confirmTransferBtn');

    // Load establishments and transfer history for transfer modal
    transferModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const patientId = button.getAttribute('data-patient-id');
        const patientName = button.getAttribute('data-patient-name');
        const currentEstablishment = button.getAttribute('data-current-establishment');

        document.getElementById('transferPatientName').value = patientName;
        document.getElementById('transferCurrentEstablishment').value = currentEstablishment;

        // Fetch establishments
        fetch('/api/establecimientos')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Establishments data:', data); // Debug
                transferNewEstablishment.innerHTML = '<option value="">Seleccione un establecimiento</option>';
                data.forEach(establecimiento => {
                    if (establecimiento.nombre !== currentEstablishment) {
                        const option = document.createElement('option');
                        option.value = establecimiento.id;
                        option.textContent = establecimiento.nombre;
                        transferNewEstablishment.appendChild(option);
                    }
                });
            })
            .catch(error => console.error('Error loading establishments:', error));
        // Fetch transfer history
        fetch(`/pacientes/${patientId}/transferencias`)
            .then(response => response.json())
            .then(data => {
                transferHistoryBody.innerHTML = '';
                if (data.success && data.transferencias.length > 0) {
                    data.transferencias.forEach(transfer => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${new Date(transfer.fechaRegistro).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                            <td>${transfer.Origen}</td>
                            <td>${transfer.Destino}</td>
                            <td>${transfer.imagen ? `<a href="${transfer.imagen}" target="_blank"><img src="${transfer.imagen}" alt="Documento" style="max-width: 100px;"></a>` : 'Sin documento'}</td>
                        `;
                        transferHistoryBody.appendChild(row);
                    });
                } else {
                    transferHistoryBody.innerHTML = '<tr><td colspan="4">No hay transferencias registradas.</td></tr>';
                }
            })
            .catch(error => console.error('Error loading transfer history:', error));

        transferForm.setAttribute('data-patient-id', patientId);
    });

    // Preview transfer document
    transferDocument.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                transferPreview.src = e.target.result;
                transferPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            transferPreview.style.display = 'none';
        }
    });

    // Handle transfer form submission
    confirmTransferBtn.addEventListener('click', function () {
        const patientId = transferForm.getAttribute('data-patient-id');
        const formData = new FormData();
        formData.append('newEstablishmentId', transferNewEstablishment.value);
        formData.append('document', transferDocument.files[0]);
        formData.append('_token', csrfToken);
    
        confirmTransferBtn.disabled = true;
        confirmTransferBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Transfiriendo...';
    
        fetch(`/pacientes/${patientId}/transfer`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Refresh transfer history
                fetch(`/api/pacientes/${patientId}/transferencias`)
                    .then(response => response.json())
                    .then(historyData => {
                        transferHistoryBody.innerHTML = '';
                        if (historyData.success && historyData.transferencias.length > 0) {
                            historyData.transferencias.forEach(transfer => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${new Date(transfer.fechaRegistro).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                                    <td>${transfer.Origen}</td>
                                    <td>${transfer.Destino}</td>
                                    <td>${transfer.imagen ? `<a href="${transfer.imagen}" target="_blank"><img src="${transfer.imagen}" alt="Documento" style="max-width: 100px;"></a>` : 'Sin documento'}</td>
                                `;
                                transferHistoryBody.appendChild(row);
                            });
                        } else {
                            transferHistoryBody.innerHTML = '<tr><td colspan="4">No hay transferencias registradas.</td></tr>';
                        }
                    })
                    .catch(error => console.error('Error refreshing transfer history:', error));
                // Refresh patient list
                loadPatients(document.getElementById('searchInput').value);
                // Clear form
                transferForm.reset();
                transferPreview.style.display = 'none';
                bootstrap.Modal.getInstance(transferModal).hide();
            } else {
                showAlert('danger', data.message || 'Error desconocido al transferir el paciente');
            }
        })
        .catch(error => {
            console.error('Error during transfer:', error);
            showAlert('danger', `Error al transferir el paciente: ${error.message}`);
        })
        .finally(() => {
            confirmTransferBtn.disabled = false;
            confirmTransferBtn.innerHTML = 'Transferir';
        });
    });
});