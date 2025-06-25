        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-mortarboard-fill me-2"></i>
                        GROUPE IPIRNET
                    </h5>
                    <p class="text-muted mb-2">
                        Centre de formation professionnelle spécialisé dans les technologies de l'information.
                    </p>
                    <p class="text-muted">
                        Formation - Innovation - Excellence
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Formations</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Développement Informatique</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Réseaux Informatiques</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Maintenance Informatique</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Contact</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="bi bi-envelope me-2"></i>contact@ipirnet.com</li>
                        <li><i class="bi bi-telephone me-2"></i>+212 5XX XX XX XX</li>
                        <li><i class="bi bi-geo-alt me-2"></i>Casablanca, Maroc</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> Groupe IPIRNET. Tous droits réservés.
                        Application développée pour le module "Technicien Spécialisé en Développement Informatique".
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <small class="text-muted">
                        Version 1.0 - <?php echo date('d/m/Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Confirmation for delete actions
        function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
            return confirm(message);
        }
        
        // Form validation helper
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(function(field) {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Veuillez remplir tous les champs obligatoires.');
                    }
                });
            }
        }
    </script>
</body>
</html>
