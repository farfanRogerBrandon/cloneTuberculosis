let isEditMode = false;
let originalPatientData = null;
let currentPatientId = null;

window.showPatientDetails = function(id) {
    fetch(`/patients/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
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
        renderPatientDetails(data);
    })
    .catch(error => {
        console.error('Error fetching patient details:', error);
        showAlert('danger', `Error al cargar los datos del paciente: ${error.message}`);
    });
};

function renderPatientDetails(data) {
    currentPatientId = data.id;
    originalPatientData = { ...data };
    const modal = new bootstrap.Modal(document.getElementById('patientModal'));

    // Render patient info (unchanged)
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

const doseTable = document.getElementById('doseHistoryTable');
doseTable.innerHTML = '';

if (data.dosis && data.dosis.length > 0) {
    data.dosis.forEach(dosis => {
        if (dosis.estado !== '0') {
            const estadoLabel = {
                '1': 'Habilitado',
                '2': 'Registrado a tiempo',
                '3': 'Enviado fuera de fecha'
            }[dosis.estado] || 'Desconocido';

            const videoContainerId = `video-${dosis.id}`;
            
            const videoButton = `
                <button class="btn btn-sm btn-success" onclick="loadAndToggleVideo(${dosis.id})">
                    Ver Video
                </button>
                <div id="${videoContainerId}" style="display: none; margin-top: 8px;"></div>
            `;

            doseTable.innerHTML += `
                <tr>
                    <td>${dosis.nroDosis}</td>
                    <td>${dosis.fechaGrabacion}</td>
                    <td>${estadoLabel}</td>
                    <td>
                        ${videoButton}
                        <button class="btn btn-sm btn-danger ms-2" onclick="deleteDose(${dosis.id})">
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
function loadAndToggleVideo(id) {
    const container = document.getElementById(`video-${id}`);

    if (container.innerHTML === '') {
        // Aún no se ha cargado el video, pedirlo al backend
        fetch(`/video-url/${id}`)
            .then(res => res.json())
            .then(data => {
                const videoUrl = data.url;
                const extension = videoUrl.split('.').pop();

                container.innerHTML = `
                    <video width="260" controls>
                        <source src="${videoUrl}" type="video/${extension}">
                        Tu navegador no soporta video.
                    </video>
                `;
                container.style.display = 'block';
            })
            .catch(err => {
                container.innerHTML = `<span class="text-danger">No hay video</span>`;
                container.style.display = 'block';
                console.error(err);
            });
    } else {
        // Si ya está cargado, simplemente alternar visibilidad
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
    }
}



// Ensure showAlert is defined
function showAlert(type, message) {
    // Replace with your actual alert implementation
    alert(`${type}: ${message}`);
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
};

function updateEditButton() {
    const editBtn = document.getElementById('editPatientBtn');
    editBtn.innerHTML = isEditMode ? 
        '<i class="fas fa-edit me-2"></i>Editando' : 
        '<i class="fas fa-edit me-2"></i>Editar';
    editBtn.classList.toggle('btn-secondary', !isEditMode);
    editBtn.classList.toggle('btn-primary', isEditMode);
}

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

    fetch(`/patients/${patientId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
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
            loadPatients(document.getElementById('searchInput').value);
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
};

window.cancelEdit = function() {
    toggleEditMode();
    renderPatientDetails(originalPatientData);
};

function createDose() {
    if (!currentPatientId) {
        showAlert('danger', 'Error: No se ha seleccionado un paciente.');
        return;
    }

    const nroDosis = document.getElementById('doseNumber').value;
    const fechaGrabacion = document.getElementById('startDate').value;

    if (!nroDosis || !fechaGrabacion) {
        showAlert('warning', 'Por favor, complete todos los campos obligatorios.');
        return;
    }

    const formData = {
        nroDosis: parseInt(nroDosis),
        fechaGrabacion
    };

    fetch(`/patients/${currentPatientId}/dosis`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
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
            showAlert('success', data.message);
            document.getElementById('doseForm').reset();
            showPatientDetails(currentPatientId);
        } else {
            showAlert('danger', data.message || 'Error al crear la dosis.');
        }
    })
    .catch(error => {
        console.error('Error al crear la dosis:', error);
        showAlert('danger', 'Error al crear la dosis: ' + error.message);
    });
}
window.deleteDose = function(doseId) {
    if (confirm('¿Está seguro de que desea eliminar esta dosis?')) {
        fetch(`/dosis/${doseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
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
                showAlert('success', data.message);
                showPatientDetails(currentPatientId);
            } else {
                showAlert('danger', data.message || 'Error al eliminar la dosis.');
            }
        })
        .catch(error => {
            console.error('Error al eliminar la dosis:', error);
            showAlert('danger', 'Error al eliminar la dosis: ' + error.message);
        });
    }
};

// Attach createDose to form submission
document.getElementById('doseForm')?.addEventListener('submit', function(event) {
    event.preventDefault();
    createDose();
});