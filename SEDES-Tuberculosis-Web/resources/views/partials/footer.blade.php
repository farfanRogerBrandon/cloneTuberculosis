<footer class="footer">
    <div class="footer-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96V0H0Z"></path>
        </svg>
    </div>
    
    <div class="footer-content">
        <div class="footer-section contact-info">
            <div class="logo-container">
                <img src="/images/logoMedico.png" alt="Logo Médico" class="footer-logo">
            </div>
            <h3>SEDES Cochabamba</h3>
            <div class="contact-details">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>Av. Aniceto Arce N°2876</p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <p>Lun-Vie 8:00AM - 14:00PM</p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <p>(529) 4-4221891</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <script>document.write(new Date().getFullYear())</script> FluenceSoft, Inc. Todos los derechos reservados.</p>
            {{-- <div class="footer-links">
                <a href="/privacidad">Privacidad</a>
                <a href="/terminos">Términos</a>
                <a href="/soporte">Soporte</a>
            </div> --}}
        </div>
    </div>
</footer>

<link rel="stylesheet" href="{{ asset('css/footer.css') }}">

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.footer-section').forEach(section => {
            observer.observe(section);
        });
    });
</script>