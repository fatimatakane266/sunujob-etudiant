    <!-- ===== FOOTER ===== -->
    <footer class="footer-sunujob mt-5">
        <div class="container">
            <div class="row g-4">

                <!-- Colonne logo + desc -->
                <div class="col-lg-4 col-md-12">
                    <div class="footer-logo mb-3">
                        <img src="/assets/images/logo.svg" alt="SunuJob Étudiant" style="height:46px; filter: brightness(0) invert(1);">
                    </div>
                    <p style="color:rgba(255,255,255,0.7); font-size:0.9rem; line-height:1.7;">
                        Plateforme numérique de missions temporaires pour étudiants sénégalais.
                        Connectez-vous aux opportunités partout au Sénégal.
                    </p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="col-lg-2 col-sm-6">
                    <h5>Navigation</h5>
                    <ul class="footer-links">
                        <li><a href="/index.php"><i class="fas fa-chevron-right"></i> Accueil</a></li>
                        <li><a href="/missions.php"><i class="fas fa-chevron-right"></i> Missions</a></li>
                        <li><a href="/categories.php"><i class="fas fa-chevron-right"></i> Catégories</a></li>
                        <li><a href="/a-propos.php"><i class="fas fa-chevron-right"></i> À propos</a></li>
                        <li><a href="/contact.php"><i class="fas fa-chevron-right"></i> Contact</a></li>
                    </ul>
                </div>

                <!-- Étudiants -->
                <div class="col-lg-2 col-sm-6">
                    <h5>Étudiants</h5>
                    <ul class="footer-links">
                        <li><a href="/register.php?role=etudiant"><i class="fas fa-chevron-right"></i> S'inscrire</a></li>
                        <li><a href="/missions.php"><i class="fas fa-chevron-right"></i> Trouver une mission</a></li>
                        <li><a href="/pages/etudiant/dashboard.php"><i class="fas fa-chevron-right"></i> Mon espace</a></li>
                        <li><a href="/pages/etudiant/profil.php"><i class="fas fa-chevron-right"></i> Mon profil</a></li>
                    </ul>
                </div>

                <!-- Recruteurs -->
                <div class="col-lg-2 col-sm-6">
                    <h5>Recruteurs</h5>
                    <ul class="footer-links">
                        <li><a href="/register.php?role=recruteur"><i class="fas fa-chevron-right"></i> S'inscrire</a></li>
                        <li><a href="/pages/recruteur/ajouter-mission.php"><i class="fas fa-chevron-right"></i> Publier</a></li>
                        <li><a href="/pages/recruteur/dashboard.php"><i class="fas fa-chevron-right"></i> Mon espace</a></li>
                        <li><a href="/pages/recruteur/candidatures.php"><i class="fas fa-chevron-right"></i> Candidatures</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-2 col-sm-6">
                    <h5>Contact</h5>
                    <ul class="footer-contact list-unstyled">
                        <li><i class="fas fa-envelope"></i><span>contact@sunujob.sn</span></li>
                        <li><i class="fas fa-phone"></i><span>+221 77 123 45 67</span></li>
                        <li><i class="fas fa-map-marker-alt"></i><span>Dakar, Sénégal</span></li>
                        <li><i class="fas fa-clock"></i><span>Lun–Sam 8h–18h</span></li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Bottom bar -->
        <div class="footer-bottom">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <span>&copy; <?= date('Y') ?> SunuJob Étudiant. Tous droits réservés.</span>
                    <div class="d-flex gap-3">
                        <a href="/mentions-legales.php" style="color:rgba(255,255,255,0.65);">Mentions légales</a>
                        <a href="/contact.php" style="color:rgba(255,255,255,0.65);">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS Custom -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
