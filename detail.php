<?php
/* ============================================================
   detail.php — Page de détail d'une moto (VERSION STATIQUE)
   ============================================================
   Affiche une moto selon l'ID dans l'URL (?id=1)
   Les données sont en dur
============================================================ */

require_once 'header.php';

$id = intval($_GET['id'] ?? 1);

// Données statiques de toutes les motos
$motos_data = [
    1 => ['id' => 1, 'marque' => 'Yamaha', 'modele' => 'MT-07', 'annee' => 2024,
          'cylindree' => 689, 'puissance' => 74, 'prix' => 7599000, 'stock' => 5,
          'image' => '', 'description' => 'La MT-07 est un roadster agile et puissant, parfait pour la ville et les petites routes. Son bicylindre de 689cc offre des accélérations vives et une sonorité envoutante.'],
    2 => ['id' => 2, 'marque' => 'Honda', 'modele' => 'CBR 650R', 'annee' => 2024,
          'cylindree' => 649, 'puissance' => 95, 'prix' => 8999000, 'stock' => 3,
          'image' => '', 'description' => 'La CBR 650R allie le style d\'une sportive à un confort au quotidien. Son 4 cylindres de 649cc délivre 95cv pour des sensations inoubliables.'],
    3 => ['id' => 3, 'marque' => 'Kawasaki', 'modele' => 'Z900', 'annee' => 2023,
          'cylindree' => 948, 'puissance' => 125, 'prix' => 10499000, 'stock' => 7,
          'image' => '', 'description' => 'La Z900 est un roadster musclé au look agressif. Son 4 cylindres de 948cc développe 125cv pour des performances exceptionnelles.'],
    4 => ['id' => 4, 'marque' => 'Suzuki', 'modele' => 'GSX-8S', 'annee' => 2024,
          'cylindree' => 776, 'puissance' => 83, 'prix' => 8699000, 'stock' => 2,
          'image' => '', 'description' => 'La GSX-8S est le nouveau roadster de Suzuki, avec un design moderne et un bicylindre de 776cc qui offre beaucoup de couple.'],
    5 => ['id' => 5, 'marque' => 'BMW', 'modele' => 'R 1250 GS', 'annee' => 2024,
          'cylindree' => 1254, 'puissance' => 136, 'prix' => 18999000, 'stock' => 1,
          'image' => '', 'description' => 'La R 1250 GS est la référence des trails. Son boxer de 1254cc vous emmène partout, sur route comme en tout-terrain.'],
    6 => ['id' => 6, 'marque' => 'Ducati', 'modele' => 'Monster', 'annee' => 2024,
          'cylindree' => 937, 'puissance' => 111, 'prix' => 13999000, 'stock' => 4,
          'image' => '', 'description' => 'Le nouveau Monster allie légèreté et puissance. Son bicylindre de 937cc délivre 111cv dans un package au design italien.'],
];

$moto = $motos_data[$id] ?? $motos_data[1];

$motos_similaires = [];
foreach ($motos_data as $m) {
    if ($m['marque'] === $moto['marque'] && $m['id'] !== $moto['id']) {
        $motos_similaires[] = $m;
    }
}
$motos_similaires = array_slice($motos_similaires, 0, 3);

$titre_page = $moto['marque'] . ' ' . $moto['modele'];
?>

<div class="conteneur" style="padding: 25px 0 60px;">

    <p style="color: var(--gris); margin-bottom: 20px; font-size: 0.9rem;">
        <a href="accueil.php" style="color: var(--gris);">Accueil</a> →
        <a href="catalogue.php" style="color: var(--gris);">Catalogue</a> →
        <span><?= htmlspecialchars($moto['marque']) ?> <?= htmlspecialchars($moto['modele']) ?></span>
    </p>

    <div class="detail-mise-en-page">

        <div>
            <div class="detail-image-zone">
                <?php if (!empty($moto['image']) && file_exists('images/motos/' . $moto['image'])): ?>
                    <img src="images/motos/<?= htmlspecialchars($moto['image']) ?>"
                         alt="<?= htmlspecialchars($moto['marque']) ?> <?= htmlspecialchars($moto['modele']) ?>">
                <?php else: ?>
                    <div class="detail-image-placeholder">🏍️</div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div style="color: var(--rouge); font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
                <?= htmlspecialchars($moto['marque']) ?>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--bleu-fonce); margin-bottom: 15px;">
                <?= htmlspecialchars($moto['modele']) ?>
            </h1>

            <div style="font-size: 2.2rem; font-weight: 800; color: var(--rouge); margin-bottom: 20px;">
                <?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA
            </div>

            <?php if ($moto['stock'] > 3): ?>
                <span class="badge-stock stock-dispo" style="margin-bottom: 20px; display: inline-block;">
                    ✅ En stock (<?= $moto['stock'] ?> disponibles)
                </span>
            <?php elseif ($moto['stock'] > 0): ?>
                <span class="badge-stock stock-faible" style="margin-bottom: 20px; display: inline-block;">
                    ⚠️ Dernières unités (<?= $moto['stock'] ?> restant<?= $moto['stock'] > 1 ? 's' : '' ?>)
                </span>
            <?php else: ?>
                <span class="badge-stock stock-epuise" style="margin-bottom: 20px; display: inline-block;">
                    ❌ Épuisé
                </span>
            <?php endif; ?>

            <div class="detail-specs">
                <div class="spec-item">
                    <span class="spec-label">Année</span>
                    <span class="spec-valeur"><?= $moto['annee'] ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Cylindrée</span>
                    <span class="spec-valeur"><?= $moto['cylindree'] ?> cc</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Puissance</span>
                    <span class="spec-valeur"><?= $moto['puissance'] ?> cv</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Stock</span>
                    <span class="spec-valeur"><?= $moto['stock'] ?> unité<?= $moto['stock'] > 1 ? 's' : '' ?></span>
                </div>
            </div>

            <?php if (!empty($moto['description'])): ?>
                <div style="background: #f8f9fa; padding: 18px; border-radius: var(--rayon-sm); margin-bottom: 25px; line-height: 1.7;">
                    <?= htmlspecialchars($moto['description']) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout au panier (non fonctionnel en statique) -->
            <div style="background:#e8f0fe; padding:10px; border-radius:8px; margin-bottom:15px;">
                <span style="color:var(--bleu-fonce);">ℹ️</span> 
                <span style="color:var(--gris);">Version démo - L'ajout au panier sera disponible prochainement</span>
            </div>

            <?php if ($moto['stock'] > 0): ?>
                <button type="button"
                        class="btn btn-principal" style="width: 100%; justify-content: center; padding: 14px; font-size: 1rem;">
                    🛒 Ajouter au panier (démo)
                </button>
            <?php else: ?>
                <button type="button" disabled
                        style="width:100%; background:#dee2e6; color:#6c757d; cursor:not-allowed; padding:14px; border:none; border-radius:var(--rayon-sm); font-size:1rem;">
                    ❌ Rupture de stock
                </button>
            <?php endif; ?>

            <a href="catalogue.php" class="btn btn-secondaire" style="margin-top: 12px; display: flex; justify-content: center;">
                ← Retour au catalogue
            </a>
        </div>
    </div>

    <?php if (!empty($motos_similaires)): ?>
        <div style="margin-top: 50px;">
            <h2 class="titre-section">Autres <?= htmlspecialchars($moto['marque']) ?></h2>
            <div class="grille-motos" style="margin-top: 20px;">
                <?php foreach ($motos_similaires as $similaire): ?>
                    <div class="carte-moto fade-in">
                        <div class="carte-moto-placeholder">🏍️</div>
                        <div class="carte-moto-corps">
                            <div class="carte-moto-marque"><?= htmlspecialchars($similaire['marque']) ?></div>
                            <div class="carte-moto-modele"><?= htmlspecialchars($similaire['modele']) ?></div>
                            <div class="carte-moto-specs">
                                <span>📅 <?= $similaire['annee'] ?></span>
                                <span>⚙️ <?= $similaire['cylindree'] ?> cc</span>
                            </div>
                        </div>
                        <div class="carte-moto-pied">
                            <span class="carte-moto-prix"><?= number_format($similaire['prix'], 0, ',', ' ') ?> FCFA</span>
                            <a href="detail.php?id=<?= $similaire['id'] ?>" class="btn btn-principal btn-sm">Voir →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>