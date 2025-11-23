<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Rólunk';
$pageDescription = 'Tudjon meg többet a Besthomesespana csapatáról. Több mint 10 év tapasztalat a spanyol ingatlanpiacon.';

include __DIR__ . '/partials/header.php';
?>

<style>
.about-page {
    margin-top: 90px;
}

.about-hero {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: var(--white);
    padding: var(--spacing-xxl) 0;
    text-align: center;
}

.about-hero h1 {
    color: var(--white);
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
}

.about-content {
    padding: var(--spacing-xxl) 0;
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xxl);
    margin: var(--spacing-xl) 0;
}

.about-text {
    line-height: 1.8;
    color: var(--text-medium);
}

.about-text h2 {
    color: var(--text-dark);
    margin-top: var(--spacing-lg);
    margin-bottom: var(--spacing-md);
}

.about-image {
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.about-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 968px) {
    .about-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="about-page">
    <!-- Hero -->
    <div class="about-hero">
        <div class="container">
            <h1>Rólunk</h1>
            <p>Az Ön megbízható partnere a spanyol ingatlanpiacon</p>
        </div>
    </div>

    <!-- Content -->
    <div class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Küldetésünk</h2>
                    <p>
                        A Besthomesespana 2010 óta segít magyar ügyfeleknek megtalálni álmaik spanyol ingatlanát.
                        Csapatunk mélyreható ismeretekkel rendelkezik a Costa Blanca és Costa del Sol régió ingatlanpiacáról.
                    </p>

                    <p>
                        Célunk, hogy ügyfeleink számára a lehető legegyszerűbbé és legbiztonságosabbá tegyük
                        a spanyolországi ingatlanvásárlás folyamatát. Magyar nyelvű ügyintézéssel, átlátható
                        szerződésekkel és teljes körű jogi támogatással szolgálunk.
                    </p>

                    <h2>Miért válasszon minket?</h2>
                    <ul style="list-style-position: inside; line-height: 2;">
                        <li>10+ év tapasztalat a spanyol ingatlanpiacon</li>
                        <li>100% magyar nyelvű ügyintézés</li>
                        <li>Csak prémium, ellenőrzött ingatlanok</li>
                        <li>Teljes körű jogi támogatás</li>
                        <li>Finanszírozási segítség</li>
                        <li>Vásárlás utáni ügyfélszolgálat</li>
                    </ul>

                    <h2>Szolgáltatásaink</h2>
                    <p>
                        <strong>Ingatlan keresés:</strong> Segítünk megtalálni a tökéletes ingatlant az Ön igényei szerint.<br>
                        <strong>Jogi tanácsadás:</strong> Tapasztalt jogászaink végigkísérik a vásárlás minden lépését.<br>
                        <strong>Finanszírozás:</strong> Kapcsolatban állunk vezető spanyol bankokkal.<br>
                        <strong>Utólagos ügyintézés:</strong> Segítünk a közműszerződések, biztosítások intézésében.
                    </p>
                </div>

                <div>
                    <div class="about-image">
                        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600" alt="Spanish coast">
                    </div>

                    <div style="margin-top: var(--spacing-xl); padding: var(--spacing-lg); background: var(--sand-light); border-radius: var(--radius-lg);">
                        <h3>Elérhetőségek</h3>
                        <p style="margin-top: var(--spacing-md);">
                            <i class="fas fa-phone"></i> <strong>Spanyolország:</strong> +34 123 456 789<br>
                            <i class="fas fa-phone"></i> <strong>Magyarország:</strong> +36 20 123 4567<br>
                            <i class="fas fa-envelope"></i> <?= ADMIN_EMAIL ?><br>
                            <i class="fas fa-map-marker-alt"></i> Alicante, Costa Blanca, Spanyolország
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
