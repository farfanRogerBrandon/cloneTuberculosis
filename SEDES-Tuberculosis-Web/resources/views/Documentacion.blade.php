@extends('layout')

@section('title', 'Reporte de Pacientes por Establecimiento')

@section('content')
<div class="container mt-5">
    
</div>

<!-- Include Bootstrap JS, Popper.js, and Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Fetch and display chart
    document.addEventListener('DOMContentLoaded', function () {
        fetch('/reportes/pacientes-por-establecimiento', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            console.log('Report response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Report data:', data);
            const canvas = document.getElementById('patientsChart');
            const noDataMessage = document.getElementById('noDataMessage');
            const errorMessage = document.getElementById('errorMessage');

            if (data.success && data.data.length > 0) {
                // Prepare chart data
                const labels = data.data.map(item => item.establishment_name);
                const patientCounts = data.data.map(item => item.patient_count);

                // Create bar chart
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Número de Pacientes',
                            data: patientCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Número de Pacientes'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Establecimiento'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });
                canvas.style.display = 'block';
                noDataMessage.style.display = 'none';
                errorMessage.style.display = 'none';
            } else {
                canvas.style.display = 'none';
                noDataMessage.style.display = 'block';
                errorMessage.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching report:', error);
            document.getElementById('patientsChart').style.display = 'none';
            document.getElementById('noDataMessage').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'block';
            document.getElementById('errorMessage').textContent = `Error al cargar los datos: ${error.message}`;
            showAlert('danger', `Error al cargar el reporte: ${error.message}`);
        });
    });

    // Function to show alerts (assuming it exists in your codebase)
    function showAlert(type, message) {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
        alertContainer.role = 'alert';
        alertContainer.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.prepend(alertContainer);
        setTimeout(() => alertContainer.remove(), 5000);
    }
</script>
@endsection