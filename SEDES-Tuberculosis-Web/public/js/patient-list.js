document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    // Load patients on page load
    loadPatients();

    // Check for new patient notification
    checkForNewPatient();

    // Set up real-time search
    setupSearch();

    function checkForNewPatient() {
        const newPatient = localStorage.getItem('newPatientAdded');
        if (newPatient) {
            const paciente = JSON.parse(newPatient);
            addPatientToTable(paciente);
            localStorage.removeItem('newPatientAdded');
            showAlert('success', 'Paciente registrado correctamente');
        }
    }

    function setupSearch() {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadPatients(this.value);
            }, 300);
        });
    }

    function loadPatients(searchTerm = '') {
        const tbody = document.getElementById('patientsTableBody');
        showLoadingIndicator(tbody);

        fetch(`/patients/data?search=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
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

    function showNoResults(tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-user-slash me-2"></i>No se encontraron pacientes
                </td>
            </tr>
        `;
    }

    function showError(tbody, error) {
        console.error('Error al cargar pacientes:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error al cargar los datos
                </td>
            </tr>
        `;
    }

    function addPatientToTable(paciente) {
        const tbody = document.getElementById('patientsTableBody');
        const row = document.createElement('tr');

        const transferButton = (empleadoRol !== 'ENFERMERO') ? `
            <td>
                <button class="btn btn-sm btn-warning" 
                        data-bs-toggle="modal" 
                        data-bs-target="#transferModal"
                        data-patient-id="${paciente.id}"
                        data-patient-name="${paciente.nombres} ${paciente.primerApellido} ${paciente.segundoApellido || ''}"
                        data-current-establishment="${paciente.establecimiento ? paciente.establecimiento.nombre : ''}">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </td>
        ` : '';

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
            ${transferButton}
            <td>
                <button class="btn btn-sm btn-danger" onclick="confirmDelete(${paciente.id}, '${paciente.nombres} ${paciente.primerApellido}')">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    }

    // Expose loadPatients globally for other scripts
    window.loadPatients = loadPatients;
});