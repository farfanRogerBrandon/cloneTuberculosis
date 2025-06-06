document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('establecimientoForm');
    const loading = document.getElementById('loading');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const submitBtn = document.getElementById('submitBtn');

    // Validation functions
    function validateDepartamento() {
        const departamento = document.getElementById('departamento').value;
        const inputGroup = document.getElementById('departamento').closest('.input-group');
        if (!departamento) {
            inputGroup.classList.add('show-error');
            return false;
        } else {
            inputGroup.classList.remove('show-error');
            return true;
        }
    }

    function validateProvincia() {
        const provincia = document.getElementById('provincia').value;
        const inputGroup = document.getElementById('provincia').closest('.input-group');
        if (!provincia) {
            inputGroup.classList.add('show-error');
            return false;
        } else {
            inputGroup.classList.remove('show-error');
            return true;
        }
    }

    function validateNombre() {
        const nombre = document.getElementById('nombre').value;
        const inputGroup = document.getElementById('nombre').closest('.input-group');
        if (!nombre) {
            inputGroup.classList.add('show-error');
            return false;
        } else {
            inputGroup.classList.remove('show-error');
            return true;
        }
    }

    function validateTelefono() {
        const telefono = document.getElementById('telefono').value;
        const inputGroup = document.getElementById('telefono').closest('.input-group');
        if (telefono && !/^\d{7,8}$/.test(telefono)) {
            inputGroup.classList.add('show-error');
            return false;
        } else {
            inputGroup.classList.remove('show-error');
            return true;
        }
    }

    // Add real-time validation listeners
    document.getElementById('departamento').addEventListener('change', validateDepartamento);
    document.getElementById('provincia').addEventListener('change', validateProvincia);
    document.getElementById('nombre').addEventListener('input', validateNombre);
    document.getElementById('telefono').addEventListener('input', validateTelefono);

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const isValid = validateDepartamento() && validateProvincia() && validateNombre() && validateTelefono();

        if (!isValid) {
            e.preventDefault();
            errorText.textContent = 'Por favor, complete todos los campos correctamente.';
            errorMessage.style.display = 'block';
            errorMessage.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        loading.style.display = 'flex';
        submitBtn.disabled = true;
    });

    // Cargar provincias dinámicamente con animación
    document.getElementById('departamento').addEventListener('change', function() {
        const departamentoId = this.value;
        const provinciaSelect = document.getElementById('provincia');
        provinciaSelect.innerHTML = '<option value="">Cargando provincias...</option>';
        provinciaSelect.disabled = true;

        if (departamentoId) {
            fetch(`/provincias/${departamentoId}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(provincias => {
                provinciaSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
                provincias.forEach(provincia => {
                    const option = document.createElement('option');
                    option.value = provincia.id;
                    option.textContent = provincia.nombre;
                    provinciaSelect.appendChild(option);
                });
                provinciaSelect.disabled = false;
                validateProvincia(); // Revalidate after loading provinces
                provinciaSelect.closest('.input-group').classList.add('pulse-animation');
                setTimeout(() => {
                    provinciaSelect.closest('.input-group').classList.remove('pulse-animation');
                }, 1000);
            })
            .catch(error => {
                console.error('Error al cargar provincias:', error);
                provinciaSelect.innerHTML = '<option value="">Error al cargar provincias</option>';
                provinciaSelect.disabled = false;
                errorText.textContent = 'Hubo un problema al cargar las provincias. Por favor, inténtelo de nuevo.';
                errorMessage.style.display = 'block';
            });
        } else {
            provinciaSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
            provinciaSelect.disabled = false;
            validateProvincia(); // Revalidate if department is cleared
        }
    });

    // Tooltips informativos
    document.querySelectorAll('.form-floating label').forEach(label => {
        const icon = document.createElement('i');
        icon.className = 'fas fa-question-circle ms-1 text-muted help-icon';
        icon.style.fontSize = '0.8rem';
        icon.style.cursor = 'pointer';
        
        icon.addEventListener('mouseenter', function(e) {
            if (this.tooltip) return;

            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-custom';
            tooltip.textContent = getTooltipText(label.getAttribute('for'));
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = `${rect.left + window.scrollX}px`;
            tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
            
            this.tooltip = tooltip;
        });
        
        icon.addEventListener('mouseleave', function() {
            if (this.tooltip) {
                this.tooltip.remove();
                this.tooltip = null;
            }
        });
        
        label.appendChild(icon);
    });
    
    function getTooltipText(inputId) {
        switch (inputId) {
            case 'departamento':
                return 'Seleccione el departamento donde se ubica el establecimiento';
            case 'provincia':
                return 'Seleccione la provincia correspondiente al departamento';
            case 'nombre':
                return 'Ingrese el nombre oficial del establecimiento de salud';
            case 'telefono':
                return 'Ingrese un número telefónico de 7 u 8 dígitos para contacto';
            default:
                return 'Complete este campo';
        }
    }
});