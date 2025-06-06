
$(document).ready(function() {
    $('.view-credentials').on('click', function() {
        const establecimientoId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('credentialsModal'));
        const credentialsList = $('#credentialsList');
        const loadingCredentials = $('#loadingCredentials');

        credentialsList.empty();
        loadingCredentials.show();

        $.ajax({
            url: `/establecimiento/${establecimientoId}/credentials`,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                loadingCredentials.hide();
                if (data.success) {
                    $('#credentialsModalLabel').text(`Credenciales para ${data.establecimiento}`);
                    if (Object.keys(data.credentials).length === 0) {
                        credentialsList.append(
                            '<li class="list-group-item text-muted">No hay credenciales disponibles.</li>'
                        );
                    } else {
                        $.each(data.credentials, function(rol, cred) {
                            credentialsList.append(
                                `<li class="list-group-item">
                                    <strong>${rol.charAt(0).toUpperCase() + rol.slice(1)}:</strong><br>
                                    <span class="ms-2">Usuario: ${cred.username}</span><br>
                                    <span class="ms-2">Contraseña: ${cred.codigoEmpleado || 'No disponible'}</span>
                                </li>`
                            );
                        });
                    }
                    modal.show();
                } else {
                    credentialsList.append(
                        '<li class="list-group-item text-danger">Error al cargar las credenciales.</li>'
                    );
                    modal.show();
                }
            },
            error: function(xhr) {
                loadingCredentials.hide();
                credentialsList.append(
                    '<li class="list-group-item text-danger">Error al conectar con el servidor.</li>'
                );
                modal.show();
                console.error('Error:', xhr);
            }
        });
    });
});