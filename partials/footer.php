<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- About Section -->
            <div class="footer-section">
                <h3>Besthomesespana</h3>
                <p>
                    Prémium spanyol ingatlanok magyar ügyfeleknek.
                    Több mint 10 év tapasztalat a Costa Blanca és Costa del Sol régióban.
                </p>
                <div class="social-links mt-3">
                    <a href="https://facebook.com" class="social-link" target="_blank" rel="noopener" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://instagram.com" class="social-link" target="_blank" rel="noopener" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://youtube.com" class="social-link" target="_blank" rel="noopener" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="mailto:<?= ADMIN_EMAIL ?>" class="social-link" aria-label="Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Gyors Linkek</h3>
                <ul class="footer-links">
                    <li><a href="/index.php">Főoldal</a></li>
                    <li><a href="/properties.php">Ingatlanok</a></li>
                    <li><a href="/about.php">Rólunk</a></li>
                    <li><a href="/contact.php">Kapcsolat</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="footer-section">
                <h3>Szolgáltatásaink</h3>
                <ul class="footer-links">
                    <li><a href="#">Ingatlan keresés</a></li>
                    <li><a href="#">Jogi tanácsadás</a></li>
                    <li><a href="#">Finanszírozási segítség</a></li>
                    <li><a href="#">Utólagos ügyintézés</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-section">
                <h3>Elérhetőség</h3>
                <p>
                    <i class="fas fa-map-marker-alt"></i> Alicante, Spanyolország
                </p>
                <p>
                    <i class="fas fa-phone"></i> +34 123 456 789
                </p>
                <p>
                    <i class="fas fa-phone"></i> +36 20 123 4567
                </p>
                <p>
                    <i class="fas fa-envelope"></i> <?= ADMIN_EMAIL ?>
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Besthomesespana. Minden jog fenntartva. |
                <a href="/privacy.php">Adatvédelem</a> |
                <a href="/terms.php">Felhasználási feltételek</a>
            </p>
        </div>
    </div>
</footer>

<!-- Scroll to top button -->
<style>
.scroll-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--accent-gold);
    color: var(--white);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    box-shadow: var(--shadow-md);
    transition: var(--transition-fast);
    z-index: 999;
}

.scroll-top:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.scroll-top.visible {
    display: flex;
}
</style>

<button class="scroll-top" id="scrollTop" aria-label="Vissza a tetejére">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Scroll to top functionality
document.addEventListener('DOMContentLoaded', function() {
    const scrollTopBtn = document.getElementById('scrollTop');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            scrollTopBtn.classList.add('visible');
        } else {
            scrollTopBtn.classList.remove('visible');
        }
    });

    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

<!-- Properties AJAX Search -->
<script src="/assets/js/properties.js"></script>

</body>
</html>
