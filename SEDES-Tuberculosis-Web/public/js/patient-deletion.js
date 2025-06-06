let patientToDelete = null;

window.confirmDelete = function(id, fullName) {
    patientToDelete = id;
    document.getElementById('patientName').textContent = fullName;
    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
};

document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (patientToDelete) {
        deletePatient(patientToDelete);
    }
});

function deletePatient(id) {
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Eliminando...';
    deleteBtn.disabled = true;

    fetch(`/patients/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
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
            loadPatients(document.getElementById('searchInput').value);
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