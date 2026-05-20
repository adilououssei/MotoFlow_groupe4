<?php
/* ============================================================
   detail.php — Page de détail d'une moto
   ============================================================
   Affiche toutes les infos d'une moto et permet de l'ajouter
   au panier. L'ID de la moto vient de l'URL : detail.php?id=3
============================================================ */

require_once 'db.php';
require_once 'fonctions.php';
demarrer_session();

/* ----------------------------------------------------------
   ÉTAPE 1 : TRAITEMENT DU FORMULAIRE POST
   On traite d'abord le POST, AVANT de récupérer la moto.
   Comme ça, après ajout au panier on redirige immédiatement
   et on recharge la page proprement en GET.
   C'est le pattern "POST → REDIRECT → GET" (PRG).
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_panier'])) {

    // On récupère l'ID depuis le champ caché du formulaire
    $id_moto_post = intval($_POST['moto_id'] ?? 0);

    // Il faut être connecté pour ajouter au panier
    if (!est_connecte()) {
        set_message('erreur', 'Connectez-vous pour ajouter au panier.');
        rediriger('connexion.php');
    }

    // Vérifie que l'utilisateur en session existe bien en BD
    // (protège contre les sessions obsolètes après réimport de la BD)
    verifier_session($pdo);

    // On vérifie le stock directement depuis la BD (re-vérification sécurisée)
    $stmt_stock = $pdo->prepare("SELECT stock FROM motos WHERE id = ? AND actif = 1");
    $stmt_stock->execute([$id_moto_post]);
    $stock_actuel = $stmt_stock->fetchColumn(); // Retourne juste la valeur de stock

    // Si la moto n'existe pas ou plus de stock
    if ($stock_actuel === false || $stock_actuel <= 0) {
        set_message('erreur', 'Cette moto n\'est plus en stock.');
        rediriger('detail.php?id=' . $id_moto_post);
    }

    // On vérifie si cette moto est déjà dans le panier de l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE utilisateur_id = ? AND moto_id = ?");
    $stmt->execute([$_SESSION['user_id'], $id_moto_post]);
    $article_existant = $stmt->fetch();

    if ($article_existant) {
        // Déjà dans le panier → on augmente juste la quantité de 1
        $stmt = $pdo->prepare("UPDATE panier SET quantite = quantite + 1 WHERE id = ?");
        $stmt->execute([$article_existant['id']]);
        set_message('succes', ' Quantité mise à jour dans votre panier !');
    } else {
        // Pas encore dans le panier → on crée une nouvelle ligne
        $stmt = $pdo->prepare("INSERT INTO panier (utilisateur_id, moto_id, quantite) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['user_id'], $id_moto_post]);
        set_message('succes', 'Moto ajoutée au panier avec succès !');
    }

    // Redirection immédiate (pattern PRG : évite le re-submit si on recharge la page)
    rediriger('detail.php?id=' . $id_moto_post);
}

/* ----------------------------------------------------------
   ÉTAPE 2 : RÉCUPÉRATION DE LA MOTO (seulement en GET)
---------------------------------------------------------- */
// On récupère l'ID depuis l'URL (?id=...)
// intval() convertit en entier pour éviter les injections SQL
$id = intval($_GET['id'] ?? 0);

// Si l'ID est 0 ou négatif, c'est invalide
if ($id <= 0) {
    set_message('erreur', 'Moto introuvable.');
    rediriger('catalogue.php');
}

// On cherche la moto dans la base de données
$stmt = $pdo->prepare("SELECT * FROM motos WHERE id = ? AND actif = 1");
$stmt->execute([$id]);
$moto = $stmt->fetch(); // Retourne le tableau de la moto, ou false si pas trouvée

// Si la moto n'existe pas (mauvais ID dans l'URL)
if (!$moto) {
    set_message('erreur', 'Cette moto n\'existe pas.');
    rediriger('catalogue.php');
}

// Récupérer quelques motos similaires (même marque, pas la moto actuelle)
$stmt = $pdo->prepare("SELECT * FROM motos WHERE marque = ? AND id != ? AND actif = 1 LIMIT 3");
$stmt->execute([$moto['marque'], $id]);
$motos_similaires = $stmt->fetchAll();

$titre_page = $moto['marque'] . ' ' . $moto['modele'];
require_once 'header.php';
?>

<div class="conteneur" style="padding: 25px 0 60px;">

    <!-- Fil d'Ariane (navigation) -->
    <p style="color: var(--gris); margin-bottom: 20px; font-size: 0.9rem;">
        <a href="accueil.php" style="color: var(--gris);">Accueil</a> →
        <a href="catalogue.php" style="color: var(--gris);">Catalogue</a> →
        <span><?= propre($moto['marque']) ?> <?= propre($moto['modele']) ?></span>
    </p>

    <!-- Mise en page 2 colonnes : image + infos -->
    <div class="detail-mise-en-page">

        <!-- ── COLONNE GAUCHE : IMAGE ── -->
        <div>
            <div class="detail-image-zone">
                <?php if (!empty($moto['image']) && file_exists('images/motos/' . $moto['image'])): ?>
                    <img src="images/motos/<?= propre($moto['image']) ?>"
                         alt="<?= propre($moto['marque']) ?> <?= propre($moto['modele']) ?>">
                <?php else: ?>
                    <div class="detail-image-placeholder"></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── COLONNE DROITE : INFORMATIONS ── -->
        <div>
            <!-- Marque et modèle -->
            <div style="color: var(--rouge); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                <?= propre($moto['marque']) ?>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--bleu-fonce); margin-bottom: 15px;">
                <?= propre($moto['modele']) ?>
            </h1>

            <!-- Prix -->
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--rouge); margin-bottom: 20px;">
                <?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA
            </div>

            <!-- Badge stock -->
            <?php if ($moto['stock'] > 3): ?>
                <span class="badge-stock stock-dispo" style="margin-bottom: 20px; display: inline-block;">
                    En stock (<?= $moto['stock'] ?> disponibles)
                </span>
            <?php elseif ($moto['stock'] > 0): ?>
                <span class="badge-stock stock-faible" style="margin-bottom: 20px; display: inline-block;">
                    Dernières unités (<?= $moto['stock'] ?> restant<?= $moto['stock'] > 1 ? 's' : '' ?>)
                </span>
            <?php else: ?>
                <span class="badge-stock stock-epuise" style="margin-bottom: 20px; display: inline-block;">
                    Épuisé
                </span>
            <?php endif; ?>

            <!-- Spécifications techniques en grille -->
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

            <!-- Description -->
            <?php if (!empty($moto['description'])): ?>
                <div style="background: #f8f9fa; padding: 18px; border-radius: var(--rayon-sm); margin-bottom: 25px; line-height: 1.7; color: var(--texte);">
                    <?= propre($moto['description']) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire pour ajouter au panier -->
            <!-- class="form-ajout-panier" est utilisé par script.js pour l'animation -->
            <form method="POST" action="detail.php?id=<?= $id ?>" class="form-ajout-panier">
                <!-- Champ caché qui envoie l'ID de la moto avec le formulaire -->
                <!-- Sans ce champ, PHP ne saurait pas quelle moto ajouter au panier -->
                <input type="hidden" name="moto_id" value="<?= $id ?>">
                <input type="hidden" name="ajouter_panier" value="1">

                <?php if ($moto['stock'] > 0): ?>
                    <button type="submit"
                            class="btn btn-principal" style="width: 100%; justify-content: center; padding: 14px; font-size: 1rem;">
                        Ajouter au panier
                    </button>
                <?php else: ?>
                    <!-- Bouton désactivé si plus de stock -->
                    <button type="button" disabled
                            style="width:100%; background:#dee2e6; color:#6c757d; cursor:not-allowed; padding:14px; border:none; border-radius:var(--rayon-sm); font-size:1rem;">
                        Rupture de stock
                    </button>
                <?php endif; ?>
            </form>

            <!-- Bouton retour -->
            <a href="catalogue.php" class="btn btn-secondaire" style="margin-top: 12px; display: flex; justify-content: center;">
                ← Retour au catalogue
            </a>

        </div>
    </div>

    <!-- Section motos similaires -->
    <?php if (!empty($motos_similaires)): ?>
        <div style="margin-top: 50px;">
            <h2 class="titre-section">Autres <?= propre($moto['marque']) ?></h2>
            <div class="grille-motos" style="margin-top: 20px;">
                <?php foreach ($motos_similaires as $similaire): ?>
                    <div class="carte-moto fade-in">
                        <div class="carte-moto-placeholder"></div>
                        <div class="carte-moto-corps">
                            <div class="carte-moto-marque"><?= propre($similaire['marque']) ?></div>
                            <div class="carte-moto-modele"><?= propre($similaire['modele']) ?></div>
                            <div class="carte-moto-specs">
                                <span>annee <?= $similaire['annee'] ?></span>
                                <span>cylindre <?= $similaire['cylindree'] ?> cc</span>
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
