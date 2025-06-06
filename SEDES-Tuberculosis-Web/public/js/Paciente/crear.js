
// Espera a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('Formb');
    const loading = document.getElementById('loading');
    const errorMessage = document.getElementById('errorMessage');
    const submitBtn = document.getElementById('submitBtn');
    const today = new Date();
    const maxDate = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());

    // Objeto para rastrear interacción con los campos
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

    // Validación para CI
    function validateCI(showError = false) {
        const ci = document.getElementById('ci');
        const error = document.getElementById('ci-error');
        const value = ci.value;
        const regex = /^[0-9]{7,8}(\s[A-Z]{1,2})?$/; // 7-8 dígitos, espacio opcional y hasta 2 letras

        let isValid = true;
        if (value && (!regex.test(value) || value.trim() !== value || value.split(' ').length > 2)) {
            isValid = false;
            if (showError) {
                ci.classList.add('is-invalid');
                error.textContent = 'El CI debe tener 7-8 dígitos con una extensión opcional (e.g., "1234567 LP").';
            }
        } else if (showError && !value) {
            isValid = false;
            ci.classList.add('is-invalid');
            error.textContent = 'El CI es obligatorio.';
        } else {
            ci.classList.remove('is-invalid');
            error.textContent = '';
        }
        return isValid;
    }

    // Validación para campos de texto (nombres y apellidos)
    function validateTextField(fieldId, errorId, fieldName, isRequired = true, showError = false) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        const value = field.value;
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?$/; // Solo letras, un espacio

        let isValid = true;
        if (isRequired && !value) {
            isValid = false;
            if (showError) {
                field.classList.add('is-invalid');
                error.textContent = `${fieldName} es obligatorio.`;
            }
        } else if (value && (!regex.test(value) || value.trim() !== value || value.split(' ').length > 2)) {
            isValid = false;
            if (showError) {
                field.classList.add('is-invalid');
                error.textContent = `${fieldName} solo puede contener letras y un espacio.`;
            }
        } else {
            field.classList.remove('is-invalid');
            error.textContent = '';
        }
        return isValid;
    }

    // Validación para celular
    function validateCelular(showError = false) {
        const celular = document.getElementById('celular');
        const error = document.getElementById('celular-error');
        const value = celular.value;
        const regex = /^[6-7][0-9]{7}$/; // Empieza con 6 o 7, 8 dígitos

        let isValid = true;
        if (!value) {
            isValid = false;
            if (showError) {
                celular.classList.add('is-invalid');
                error.textContent = 'El celular es obligatorio.';
            }
        } else if (!regex.test(value) || value.length !== 8 || value.trim() !== value) {
            isValid = false;
            if (showError) {
                celular.classList.add('is-invalid');
                error.textContent = 'El celular debe ser 8 dígitos empezando con 6 o 7.';
            }
        } else {
            celular.classList.remove('is-invalid');
            error.textContent = '';
        }
        return isValid;
    }

    // Validación para fecha de nacimiento
    function validateFechaNacimiento(showError = false) {
        const fecha = document.getElementById('fechaNacimiento');
        const error = document.getElementById('fechaNacimiento-error');
        const birthDate = new Date(fecha.value);

        let isValid = true;
        if (!fecha.value) {
            isValid = false;
            if (showError) {
                fecha.classList.add('is-invalid');
                error.textContent = 'La fecha de nacimiento es obligatoria.';
            }
        } else if (birthDate >= today || birthDate > maxDate) {
            isValid = false;
            if (showError) {
                fecha.classList.add('is-invalid');
                error.textContent = 'El paciente debe tener al menos 1 año.';
            }
        } else {
            fecha.classList.remove('is-invalid');
            error.textContent = '';
        }
        return isValid;
    }

    // Validación para género
    function validateGenero(showError = false) {
        const genero = form.querySelector('input[name="genero"]:checked');
        const error = document.getElementById('genero-error');

        let isValid = true;
        if (!genero) {
            isValid = false;
            if (showError) error.style.display = 'block';
        } else {
            error.style.display = 'none';
        }
        return isValid;
    }

    // Validación para establecimiento
    function validateEstablecimiento(showError = false) {
        const establecimiento = document.getElementById('establecimiento');
        const error = document.getElementById('establecimiento-error');

        let isValid = true;
        if (!establecimiento.value) {
            isValid = false;
            if (showError) {
                establecimiento.classList.add('is-invalid');
                error.textContent = 'Seleccione un establecimiento.';
            }
        } else {
            establecimiento.classList.remove('is-invalid');
            error.textContent = '';
        }
        return isValid;
    }

    // Listeners para validación en tiempo real
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
    form.querySelectorAll('input[name="genero"]').forEach(radio => radio.addEventListener('change', () => {
        hasInteracted.genero = true;
        validateGenero(true);
    }));
    document.getElementById('establecimiento').addEventListener('change', () => {
        hasInteracted.establecimiento = true;
        validateEstablecimiento(true);
    });

    // Manejo del envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar todos los campos
        const isValid = validateCI(true) && 
                        validateTextField('nombres', 'nombres-error', 'Nombres', true, true) && 
                        validateTextField('primerApellido', 'primerApellido-error', 'Primer Apellido', true, true) && 
                        validateTextField('segundoApellido', 'segundoApellido-error', 'Segundo Apellido', false, true) && 
                        validateCelular(true) && 
                        validateFechaNacimiento(true) && 
                        validateGenero(true) && 
                        validateEstablecimiento(true);

        if (!isValid) {
            errorMessage.textContent = 'Corrija los errores en el formulario.';
            errorMessage.style.display = 'block';
            return;
        }

        const formData = new FormData(form);
        loading.style.display = 'block';
        errorMessage.style.display = 'none';
        submitBtn.disabled = true;

        fetch(patientsStoreUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (response.status === 401) {
                errorMessage.textContent = 'Sesión expirada. Redirigiendo al login...';
                errorMessage.style.display = 'block';
                setTimeout(() => {
                    window.location.href = "{{ route('login') }}";
                }, 2000);
                return Promise.reject('No autenticado');
            }
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
         .then(data => {
                    loading.style.display = 'none';
                    submitBtn.disabled = false;
                    if (data.success) {
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                        form.reset();
                        localStorage.setItem('newPatientAdded', JSON.stringify(data.paciente));
                        setTimeout(() => {
                            successModal.hide();
                            window.location.href = patientsIndexUrl;

                        }, 2000);
                    } else {
                        errorMessage.textContent = data.message;
                        errorMessage.style.display = 'block';
                    }
                })

        .catch(error => {
            loading.style.display = 'none';
            submitBtn.disabled = false;
            errorMessage.textContent = 'Error: ' + error.message;
            errorMessage.style.display = 'block';
            console.error('Error:', error);
        });
    });
});
