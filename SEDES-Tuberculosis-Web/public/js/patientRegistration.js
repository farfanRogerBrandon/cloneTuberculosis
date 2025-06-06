// Esperar a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    // Elementos del DOM que necesitamos
    const form = document.getElementById('patientRegistrationForm');
    const loading = document.getElementById('loading');
    const errorMessage = document.getElementById('errorMessage');
    const submitBtn = document.getElementById('submitBtn');
    
    // Fechas para validación
    const today = new Date();
    const maxDate = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
    
    // Objeto para rastrear interacción del usuario con los campos
    const hasInteracted = {
        ci: false,
        nombres: false,
        primerApellido: false,
        segundoApellido: false,
        celular: false,
        fechaNacimiento: false,
        genero: false,
        establecimiento: false
    };

    // Función para validar el Carnet de Identidad
    function validateCI(showError = false) {
        const ci = document.getElementById('ci');
        const error = document.getElementById('ci-error');
        const value = ci.value.trim();
        const regex = /^[0-9]{6,11}(\s[A-Z]{1,2})?$/;

        // Validar que el campo no esté vacío
        if (!value) {
            if (showError) {
                ci.classList.add('is-invalid');
                error.textContent = 'El CI es obligatorio.';
            }
            return false;
        }

        // Validar formato del CI (6-11 dígitos con extensión opcional)
        if (!regex.test(value) || value.split(' ').length > 2) {
            if (showError) {
                ci.classList.add('is-invalid');
                error.textContent = 'Formato de CI inválido. Debe ser 6-11 dígitos con extensión opcional (ej. "1234567 LP").';
            }
            return false;
        }

        // Si pasa las validaciones, limpiar errores
        ci.classList.remove('is-invalid');
        error.textContent = '';
        return true;
    }

    // Función genérica para validar campos de texto
    function validateTextField(fieldId, errorId, fieldName, isRequired = true, showError = false) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        const value = field.value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?$/;

        // Validar campo obligatorio
        if (isRequired && !value) {
            if (showError) {
                field.classList.add('is-invalid');
                error.textContent = `${fieldName} es obligatorio.`;
            }
            return false;
        }

        // Validar formato del texto (solo letras y un espacio)
        if (value && (!regex.test(value) || value.split(' ').length > 2)) {
            if (showError) {
                field.classList.add('is-invalid');
                error.textContent = `${fieldName} solo puede contener letras y un espacio.`;
            }
            return false;
        }

        // Si pasa las validaciones, limpiar errores
        field.classList.remove('is-invalid');
        error.textContent = '';
        return true;
    }

    // Función para validar el número de celular
    function validateCelular(showError = false) {
        const celular = document.getElementById('celular');
        const error = document.getElementById('celular-error');
        const value = celular.value.trim();
        const regex = /^[6-7][0-9]{7}$/;

        // Validar que el campo no esté vacío
        if (!value) {
            if (showError) {
                celular.classList.add('is-invalid');
                error.textContent = 'El celular es obligatorio.';
            }
            return false;
        }

        // Validar formato del celular (8 dígitos que empiezan con 6 o 7)
        if (!regex.test(value) || value.length !== 8) {
            if (showError) {
                celular.classList.add('is-invalid');
                error.textContent = 'El celular debe tener 8 dígitos comenzando con 6 o 7.';
            }
            return false;
        }

        // Si pasa las validaciones, limpiar errores
        celular.classList.remove('is-invalid');
        error.textContent = '';
        return true;
    }

    // Función para validar la fecha de nacimiento
    function validateFechaNacimiento(showError = false) {
        const fecha = document.getElementById('fechaNacimiento');
        const error = document.getElementById('fechaNacimiento-error');
        const birthDate = new Date(fecha.value);

        // Validar que el campo no esté vacío
        if (!fecha.value) {
            if (showError) {
                fecha.classList.add('is-invalid');
                error.textContent = 'La fecha de nacimiento es obligatoria.';
            }
            return false;
        }

        // Validar que la fecha sea menor a hoy y que el paciente tenga al menos 1 año
        if (birthDate >= today || birthDate > maxDate) {
            if (showError) {
                fecha.classList.add('is-invalid');
                error.textContent = 'El paciente debe tener al menos 1 año.';
            }
            return false;
        }

        // Si pasa las validaciones, limpiar errores
        fecha.classList.remove('is-invalid');
        error.textContent = '';
        return true;
    }

    // Función para validar la selección de género
    function validateGenero(showError = false) {
        const genero = form.querySelector('input[name="genero"]:checked');
        const error = document.getElementById('genero-error');

        // Validar que se haya seleccionado un género
        if (!genero) {
            if (showError) error.style.display = 'block';
            return false;
        }

        // Si pasa la validación, ocultar error
        error.style.display = 'none';
        return true;
    }

    // Función para validar la selección de establecimiento
    function validateEstablecimiento(showError = false) {
        const establecimiento = document.getElementById('establecimiento');
        const error = document.getElementById('establecimiento-error');

        // Validar que se haya seleccionado un establecimiento
        if (!establecimiento.value) {
            if (showError) {
                establecimiento.classList.add('is-invalid');
                error.textContent = 'Seleccione un establecimiento.';
            }
            return false;
        }

        // Si pasa la validación, limpiar errores
        establecimiento.classList.remove('is-invalid');
        error.textContent = '';
        return true;
    }

    // Event listeners para validación en tiempo real
    document.getElementById('ci').addEventListener('input', () => {
        hasInteracted.ci = true;
        validateCI(true);
    });

    document.getElementById('nombres').addEventListener('input', () => {
        hasInteracted.nombres = true;
        validateTextField('nombres', 'nombres-error', 'Nombres', true, true);
    });

    document.getElementById('primerApellido').addEventListener('input', () => {
        hasInteracted.primerApellido = true;
        validateTextField('primerApellido', 'primerApellido-error', 'Primer Apellido', true, true);
    });

    document.getElementById('segundoApellido').addEventListener('input', () => {
        hasInteracted.segundoApellido = true;
        validateTextField('segundoApellido', 'segundoApellido-error', 'Segundo Apellido', false, true);
    });

    document.getElementById('celular').addEventListener('input', () => {
        hasInteracted.celular = true;
        validateCelular(true);
    });

    document.getElementById('fechaNacimiento').addEventListener('change', () => {
        hasInteracted.fechaNacimiento = true;
        validateFechaNacimiento(true);
    });

    document.getElementById('fechaNacimiento').addEventListener('input', () => {
        hasInteracted.fechaNacimiento = true;
        validateFechaNacimiento(true);
    });

    form.querySelectorAll('input[name="genero"]').forEach(radio => {
        radio.addEventListener('change', () => {
            hasInteracted.genero = true;
            validateGenero(true);
        });
    });

    document.getElementById('establecimiento').addEventListener('change', () => {
        hasInteracted.establecimiento = true;
        validateEstablecimiento(true);
    });

    // Manejar el envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar todos los campos antes de enviar
        const isValid = validateCI(true) && 
                       validateTextField('nombres', 'nombres-error', 'Nombres', true, true) && 
                       validateTextField('primerApellido', 'primerApellido-error', 'Primer Apellido', true, true) && 
                       validateTextField('segundoApellido', 'segundoApellido-error', 'Segundo Apellido', false, true) && 
                       validateCelular(true) && 
                       validateFechaNacimiento(true) && 
                       validateGenero(true) && 
                       validateEstablecimiento(true);

        // Mostrar mensaje de error si hay campos inválidos
        if (!isValid) {
            errorMessage.textContent = 'Por favor, corrija los errores en el formulario.';
            errorMessage.style.display = 'block';
            return;
        }

        // Preparar datos del formulario para enviar
        const formData = new FormData(form);
        loading.style.display = 'block';
        errorMessage.style.display = 'none';
        submitBtn.disabled = true;

        // Enviar formulario mediante AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            // Verificar si la respuesta del servidor es correcta
            if (!response.ok) {
                throw new Error('La respuesta del servidor no fue exitosa');
            }
            return response.json();
        })
        .then(data => {
            loading.style.display = 'none';
            submitBtn.disabled = false;
            
            // Manejar respuesta exitosa del servidor
            if (data.success) {
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                form.reset();
                
                // Guardar datos del paciente en localStorage para uso posterior
                localStorage.setItem('newPatientAdded', JSON.stringify(data.paciente));
                
                // Redirigir después de 2 segundos
                setTimeout(() => {
                    successModal.hide();
                    window.location.href = "{{ route('patients.index') }}";
                }, 2000);
            } else {
                // Mostrar mensaje de error del servidor
                errorMessage.textContent = data.message || 'Error al registrar el paciente.';
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            // Manejar errores de conexión o del servidor
            loading.style.display = 'none';
            submitBtn.disabled = false;
            errorMessage.textContent = 'Error al conectar con el servidor. Por favor, intente nuevamente.';
            errorMessage.style.display = 'block';
            console.error('Error:', error);
        });
    });
});