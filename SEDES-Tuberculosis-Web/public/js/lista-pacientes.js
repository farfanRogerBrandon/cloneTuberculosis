    let isEditMode = false;
    let originalPatientData = null;
    let patientToDelete = null;
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    document.addEventListener('DOMContentLoaded', function() {
        // Cargar pacientes al iniciar
        loadPatients();

        // Verificar si hay un nuevo paciente agregado desde otra vista
        checkForNewPatient();

        // Configurar búsqueda en tiempo real
        setupSearch();
    });

    // Función para verificar si hay un nuevo paciente agregado
    function checkForNewPatient() {
        const newPatient = localStorage.getItem('newPatientAdded');
        if (newPatient) {
            const paciente = JSON.parse(newPatient);
            addPatientToTable(paciente);
            localStorage.removeItem('newPatientAdded');
            showAlert('success', 'Paciente registrado correctamente');
        }
    }

    // Función para configurar la búsqueda en tiempo real
    function setupSearch() {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadPatients(this.value);
            }, 300);
        });
    }

    // Función principal para cargar pacientes
    function loadPatients(searchTerm = '') {
        const tbody = document.getElementById('patientsTableBody');
        showLoadingIndicator(tbody);

        fetch(`/patients/data?search=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            renderPatients(data);
        })
        .catch(error => {
            showError(tbody, error);
        });
    }

    // Función para mostrar indicador de carga
    function showLoadingIndicator(tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;
    }

    // Función para renderizar pacientes en la tabla
    function renderPatients(data) {
        const tbody = document.getElementById('patientsTableBody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            showNoResults(tbody);
            return;
        }

        data.forEach(paciente => {
            addPatientToTable(paciente);
        });
    }

    // Función para mostrar mensaje cuando no hay resultados
    function showNoResults(tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-user-slash me-2"></i>No se encontraron pacientes
                </td>
            </tr>
        `;
    }

    // Función para mostrar errores
    function showError(tbody, error) {
        console.error('Error al cargar pacientes:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error al cargar los datos
                </td>
            </tr>
        `;
    }

    // Función para agregar un paciente a la tabla
    function addPatientToTable(paciente) {
        const tbody = document.getElementById('patientsTableBody');
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${paciente.nombres} ${paciente.primerApellido} ${paciente.segundoApellido || ''}</td>
            <td>${paciente.ci}</td>
            <td>${paciente.establecimiento ? paciente.establecimiento.nombre : 'Sin establecimiento'}</td>
            <td>Primera Fase</td>
            <td><span class="badge ${paciente.estado === '1' ? 'bg-success' : 'bg-danger'}">
                ${paciente.estado === '1' ? 'Activo' : 'Inactivo'}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="showPatientDetails(${paciente.id})">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="showTransferModal(${paciente.id}, '${paciente.nombres} ${paciente.primerApellido} ${paciente.segundoApellido || ''}', '${paciente.establecimiento ? paciente.establecimiento.nombre : ''}')">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="confirmDelete(${paciente.id}, '${paciente.nombres} ${paciente.primerApellido}')">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    }

    // Transferencia de un hospital a otro


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
        .then(response => response.json())
        .then(data => {
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
    fetch(`/api/pacientes/${patientId}/transferencias`)
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

    fetch(`/api/pacientes/${patientId}/transfer`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
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
                loadPatients(searchInput.value);
                // Clear form
                transferForm.reset();
                transferPreview.style.display = 'none';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al transferir el paciente');
        });
});
    //  Previavista de la imagen
    document.getElementById('transferDocument').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('transferPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Funcion de transferir al paciente
    function transferPatient(patientId) {
        const transferBtn = document.getElementById('confirmTransferBtn');
        const newEstablishmentId = document.getElementById('transferNewEstablishment').value;
        const documentFile = document.getElementById('transferDocument').files[0];
        
        if (!newEstablishmentId || !documentFile) {
            showAlert('warning', 'Por favor complete todos los campos');
            return;
        }

        const formData = new FormData();
        formData.append('newEstablishmentId', newEstablishmentId);
        formData.append('document', documentFile);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        transferBtn.disabled = true;
        transferBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Transfiriendo...';

        fetch(`/patients/${patientId}/transfer`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('transferModal')).hide();
                loadPatients(searchInput.value);
                showAlert('success', 'Paciente transferido correctamente');
            } else {
                throw new Error(data.message || 'Error al transferir');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message);
        })
        .finally(() => {
            transferBtn.disabled = false;
            transferBtn.innerHTML = 'Transferir';
        });
    }

    // Función para mostrar detalles del paciente
    window.showPatientDetails = function(id) {
        fetch(`/patients/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Patient data received:', data); 
            renderPatientDetails(data);
        })
        .catch(error => {
            console.error('Error fetching patient details:', error);
            showAlert('danger', `Error al cargar los datos del paciente: ${error.message}`);
        });
    };

    // Función para renderizar detalles del paciente
    function renderPatientDetails(data) {
        currentPatientId = data.id; // Ensure currentPatientId is set
        const modal = new bootstrap.Modal(document.getElementById('patientModal'));
        originalPatientData = {...data};
    
        // Handle patient photo
        const photoImg = document.getElementById('patientPhoto');
        const noPhotoMsg = document.getElementById('noPhotoMessage');
        if (data.foto) {
            photoImg.src = `/storage/${data.foto}`;
            photoImg.style.display = 'block';
            noPhotoMsg.style.display = 'none';
        } else {
            photoImg.style.display = 'none';
            noPhotoMsg.style.display = 'block';
        }
    
        // Render patient info
        document.getElementById('patientInfo').innerHTML = `
            <div class="col-md-6">
                <label class="form-label">CI:</label>
                <input type="text" name="ci" class="form-control" value="${data.ci || 'N/A'}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nombres:</label>
                <input type="text" name="nombres" class="form-control" value="${data.nombres || 'N/A'}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Primer Apellido:</label>
                <input type="text" name="primerApellido" class="form-control" value="${data.primerApellido || 'N/A'}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Segundo Apellido:</label>
                <input type="text" name="segundoApellido" class="form-control" value="${data.segundoApellido || ''}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Celular:</label>
                <input type="text" name="celular" class="form-control" value="${data.celular || ''}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Sexo:</label>
                <select name="sexo" class="form-select" disabled>
                    <option value="m" ${data.sexo === 'm' ? 'selected' : ''}>Masculino</option>
                    <option value="f" ${data.sexo === 'f' ? 'selected' : ''}>Femenino</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha Nacimiento:</label>
                <input type="date" name="fechaNacimiento" class="form-control" value="${data.fechaNacimiento || ''}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Establecimiento:</label>
                <input type="text" class="form-control" 
                    value="${data.establecimiento ? data.establecimiento.nombre : 'N/A'}" readonly disabled>
            </div>
        `;
    
        // Render dose history
        const doseTable = document.getElementById('doseHistoryTable');
        doseTable.innerHTML = '';
        if (data.dosis && data.dosis.length > 0) {
            data.dosis.forEach(dosis => {
                if (dosis.estado === '1') {
                    doseTable.innerHTML += `
                        <tr>
                            <td>${dosis.nroDosis}</td>
                            <td>${dosis.fechaGrabacion}</td>
                            <td>${dosis.estado === '1' ? 'Activo' : 'Inactivo'}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteDose(${dosis.id})">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    `;
                }
            });
        } else {
            doseTable.innerHTML = '<tr><td colspan="4" class="text-center">No hay dosis registradas</td></tr>';
        }
    
        modal.show();
        isEditMode = false;
        updateEditButton();
    }
    window.toggleEditMode = function() {
        isEditMode = !isEditMode;
        const inputs = document.querySelectorAll('#patientInfo input, #patientInfo select');
        const saveContainer = document.getElementById('saveChangesContainer');
        
        inputs.forEach(input => {
            if (input.name !== 'ci' && input.name !== 'establecimiento') {
                if (input.tagName === 'INPUT') {
                    input.readOnly = !isEditMode;
                } else if (input.tagName === 'SELECT') {
                    input.disabled = !isEditMode;
                }
            }
        });
        
        saveContainer.classList.toggle('d-none', !isEditMode);
        updateEditButton();
    }

    // Boton editar
    function updateEditButton() {
        const editBtn = document.getElementById('editPatientBtn');
        editBtn.innerHTML = isEditMode ? 
            '<i class="fas fa-edit me-2"></i>Editando' : 
            '<i class="fas fa-edit me-2"></i>Editar';
        editBtn.classList.toggle('btn-secondary', !isEditMode);
        editBtn.classList.toggle('btn-primary', isEditMode);
    }

    // Guardar cambios del edit
    window.savePatientChanges = function() {
        const form = document.getElementById('patientInfoForm');
        const patientId = originalPatientData.id;
        const formData = new FormData();
    
        formData.append('nombres', document.querySelector('#patientInfo input[name="nombres"]').value);
        formData.append('primerApellido', document.querySelector('#patientInfo input[name="primerApellido"]').value);
        formData.append('segundoApellido', document.querySelector('#patientInfo input[name="segundoApellido"]').value);
        formData.append('celular', document.querySelector('#patientInfo input[name="celular"]').value);
        formData.append('sexo', document.querySelector('#patientInfo select[name="sexo"]').value);
        formData.append('fechaNacimiento', document.querySelector('#patientInfo input[name="fechaNacimiento"]').value);
        formData.append('_method', 'PUT'); 
    
        
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
    
        fetch(`/patients/${patientId}`, {
            method: 'POST', 
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                toggleEditMode();
                showPatientDetails(patientId);
                loadPatients(searchInput.value);
                showAlert('success', 'Datos actualizados correctamente');
            } else {
                throw new Error(data.message || 'Error al actualizar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (error.errors) {
                const errorMessages = Object.values(error.errors).flat().join(' ');
                showAlert('danger', `Error al actualizar el paciente: ${errorMessages}`);
            } else {
                showAlert('danger', `Error al actualizar el paciente: ${error.message}`);
            }
        });
    }

    // cancelar la edicion
    window.cancelEdit = function() {
        toggleEditMode();
        renderPatientDetails(originalPatientData); // Restore original data
    }
    // Función para confirmar eliminación
    window.confirmDelete = function(id, fullName) {
        patientToDelete = id;
        document.getElementById('patientName').textContent = fullName;
        const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        modal.show();
    };

    // Configurar botón de confirmar eliminación
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (patientToDelete) {
            deletePatient(patientToDelete);
        }
    });

    // Función para eliminar paciente
    function deletePatient(id) {
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Eliminando...';
        deleteBtn.disabled = true;

        fetch(`/patients/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al eliminar');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
                loadPatients(searchInput.value);
                showAlert('success', 'Paciente eliminado correctamente');
            } else {
                throw new Error(data.message || 'Error al eliminar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message);
        })
        .finally(() => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            patientToDelete = null;
        });
    }

    // Función para mostrar alertas
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show fixed-top mx-auto mt-3`;
        alertDiv.style.maxWidth = '500px';
        alertDiv.style.zIndex = '1060';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }

    // Función para historial médico 
    window.showMedicalHistory = function() {
        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
        historyModal.show();
    };

    let currentPatientId = null;

    // Cargar detalles del paciente
// Assume this is within the loadPatientDetails function
function loadPatientDetails(patientId) {
    currentPatientId = patientId;
    fetch(`/patients/${patientId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        // Personal info population (unchanged, assuming it’s correct)
        document.getElementById('patientInfo').innerHTML = `
            <div class="col-md-6">
                <label class="form-label">CI</label>
                <input type="text" class="form-control" value="${data.ci}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nombres</label>
                <input type="text" class="form-control" value="${data.nombres}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Primer Apellido</label>
                <input type="text" class="form-control" value="${data.primerApellido}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Segundo Apellido</label>
                <input type="text" class="form-control" value="${data.segundoApellido || ''}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Celular</label>
                <input type="text" class="form-control" value="${data.celular}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Sexo</label>
                <input type="text" class="form-control" value="${data.sexo === 'm' ? 'Masculino' : 'Femenino'}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control" value="${data.fechaNacimiento}" readonly>
            </div>
        `;

        // Clear dose form
        document.getElementById('doseForm').reset();

        // Load dose history table
        const doseTable = document.getElementById('doseHistoryTable');
        doseTable.innerHTML = '';
        if (data.dosis && data.dosis.length > 0) {
            data.dosis.forEach(dosis => {
                if (dosis.estado === '1') {
                    doseTable.innerHTML += `
                        <tr>
                            <td>${dosis.nroDosis}</td>
                            <td>${dosis.fechaGrabacion}</td>
                            <td>${dosis.estado === '1' ? 'Activo' : 'Inactivo'}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteDose(${dosis.id})">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    `;
                }
            });
        } else {
            doseTable.innerHTML = '<tr><td colspan="4" class="text-center">No hay dosis registradas</td></tr>';
        }

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('patientModal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error al cargar detalles del paciente:', error);
        alert('Error al cargar los detalles del paciente.');
    });
}

// Create a new dose
function createDose() {
    console.log('Creating dose for patientId:', currentPatientId);
    if (!currentPatientId) {
        alert('Error: No se ha seleccionado un paciente. Por favor, seleccione un paciente primero.');
        return;
    }

    const nroDosis = document.getElementById('doseNumber').value;
    const fechaGrabacion = document.getElementById('startDate').value;

    if (!nroDosis || !fechaGrabacion) {
        alert('Por favor, complete todos los campos obligatorios.');
        return;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const grabacionDate = new Date(fechaGrabacion);
    grabacionDate.setHours(0, 0, 0, 0);
    if (grabacionDate < today) {
        alert('La fecha de grabación no puede ser anterior a hoy.');
        return;
    }

    const formData = {
        nroDosis: parseInt(nroDosis),
        fechaGrabacion
    };

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token) {
        alert('Error: Token CSRF no encontrado. Por favor, recargue la página.');
        return;
    }

    fetch(`/patients/${currentPatientId}/dosis`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || `HTTP error! status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('doseForm').reset();
            loadPatientDetails(currentPatientId);
        } else {
            alert(data.message || 'Error desconocido al crear la dosis.');
        }
    })
    .catch(error => {
        console.error('Error al crear la dosis:', error);
        alert('Error al crear la dosis: ' + error.message);
    });
}

// Delete a dose
function deleteDose(doseId) {
    if (confirm('¿Está seguro de que desea eliminar esta dosis?')) {
        fetch(`/dosis/${doseId}`, { // Changed from /doses/ to /dosis/
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadPatientDetails(currentPatientId); // Reload modal
            } else {
                alert(data.message || 'Error al eliminar la dosis.');
            }
        })
        .catch(error => {
            console.error('Error al eliminar la dosis:', error);
            alert('Error al eliminar la dosis: ' + error.message);
        });
    }
}

// Connect the "Acciones" button in the table
document.querySelectorAll('.view-details-btn').forEach(button => {
    button.addEventListener('click', function() {
        const patientId = this.getAttribute('data-patient-id');
        loadPatientDetails(patientId);
    });
});

// Medical history modal (unchanged, assuming it’s in the full script)
window.showMedicalHistory = function() {
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    historyModal.show();
};