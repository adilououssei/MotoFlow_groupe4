<?php
/* ============================================================
   panier.php — Gestion du panier
   ============================================================
   Affiche les articles du panier et gère les actions :
   - Supprimer un article
   - Vider tout le panier
   - Passer à la commande
============================================================ */

require_once 'db.php';
require_once 'fonctions.php';
demarrer_session();

// Le panier est réservé aux utilisateurs connectés
if (!est_connecte()) {
    set_message('erreur', 'Connectez-vous pour accéder à votre panier.');
    rediriger('connexion.php');
}

// Vérifie que l'utilisateur en session existe en BD
// (protège contre les sessions obsolètes après réimport de la BD)
verifier_session($pdo);

/* ----------------------------------------------------------
   TRAITEMENT DES ACTIONS (POST)
   On vérifie quelle action a été demandée
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ACTION : Supprimer un article du panier
    if (isset($_POST['supprimer'])) {
        $panier_id = intval($_POST['panier_id']); // ID de la ligne panier

        // On supprime UNIQUEMENT si ça appartient à l'utilisateur connecté
        // La condition "AND utilisateur_id = ?" est une sécurité essentielle !
        // Sans elle, un utilisateur pourrait supprimer le panier d'un autre.
        $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$panier_id, $_SESSION['user_id']]);
        set_message('succes', 'Article retiré du panier.');
    }

    // ACTION : Vider tout le panier
    if (isset($_POST['vider'])) {
        $stmt = $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        set_message('succes', 'Panier vidé.');
    }

    // Dans les deux cas, on redirige pour éviter le re-submit
    rediriger('panier.php');
}

/* ----------------------------------------------------------
   RÉCUPÉRATION DES ARTICLES DU PANIER
   On fait un JOIN avec la table motos pour avoir les détails
   (prix, image, nom...) de chaque moto dans le panier
---------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT
        p.id AS panier_id,       -- ID de la ligne dans la table panier
        p.quantite,              -- Quantité souhaitée
        m.id AS moto_id,         -- ID de la moto
        m.marque,                -- Marque de la moto
        m.modele,                -- Modèle de la moto
        m.prix,                  -- Prix unitaire
        m.image,                 -- Image
        m.stock,                 -- Stock disponible (pour vérif)
        m.prix * p.quantite AS sous_total  -- Prix × Quantité
    FROM panier p
    JOIN motos m ON p.moto_id = m.id   -- On relie panier et motos
    WHERE p.utilisateur_id = ?          -- Seulement le panier de l'utilisateur
");
$stmt->execute([$_SESSION['user_id']]);
$articles = $stmt->fetchAll();

// Calculer le total général du panier
$total = 0;
foreach ($articles as $article) {
    $total += $article['sous_total']; // On additionne chaque sous-total
}

$titre_page = 'Mon Panier';
require_once 'header.php';
?>

<div class="conteneur" style="padding: 25px 0 60px;">

    <h1 class="titre-section">🛒 Mon Panier</h1>
    <p class="sous-titre-section">
        <?= count($articles) ?> article<?= count($articles) > 1 ? 's' : '' ?>
    </p>

    <?php if (empty($articles)): ?>
        <!-- Panier vide -->
        <div style="text-align:center; padding:60px 20px; background:white; border-radius:var(--rayon); box-shadow:var(--ombre);">
            <div style="font-size:4rem; margin-bottom:15px;">🛒</div>
            <h3 style="color:var(--bleu-fonce); margin-bottom:10px;">Votre panier est vide</h3>
            <p style="color:var(--gris); margin-bottom:25px;">Découvrez notre catalogue pour trouver votre moto idéale</p>
            <a href="catalogue.php" class="btn btn-principal">Voir le catalogue</a>
        </div>

    <?php else: ?>
        <!-- Panier avec articles : mise en page 2 colonnes -->
        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 25px; align-items: start;">

            <!-- ── COLONNE GAUCHE : Articles ── -->
            <div>
                <?php foreach ($articles as $article): ?>
                    <!-- Un article par moto dans le panier -->
                    <div class="panier-article">

                        <!-- Image ou icône -->
                        <div class="panier-article-image">
                            <?php if (!empty($article['image']) && file_exists('images/motos/' . $article['image'])): ?>
                                <img src="images/motos/<?= propre($article['image']) ?>"
                                     alt="<?= propre($article['marque']) ?>">
                            <?php else: ?>
                                🏍️ <!-- Icône si pas d'image -->
                            <?php endif; ?>
                        </div>

                        <!-- Infos de la moto -->
                        <div>
                            <div style="color:var(--rouge); font-size:0.8rem; font-weight:700; text-transform:uppercase;">
                                <?= propre($article['marque']) ?>
                            </div>
                            <div style="font-weight:700; color:var(--bleu-fonce); font-size:1rem; margin-bottom:4px;">
                                <?= propre($article['modele']) ?>
                            </div>
                            <div style="color:var(--gris); font-size:0.85rem;">
                                <?= number_format($article['prix'], 0, ',', ' ') ?> FCFA × <?= $article['quantite'] ?>
                            </div>
                            <div style="font-weight:700; color:var(--rouge); margin-top:4px;">
                                = <?= number_format($article['sous_total'], 0, ',', ' ') ?> FCFA
                            </div>
                        </div>

                        <!-- Bouton supprimer -->
                        <div>
                            <!-- Formulaire de suppression d'UN article -->
                            <form method="POST" style="display:inline;">
                                <!-- Champ caché qui envoie l'ID de la ligne panier -->
                                <input type="hidden" name="panier_id" value="<?= $article['panier_id'] ?>">
                                <button type="submit" name="supprimer" value="1"
                                        class="btn btn-danger btn-sm"
                                        data-confirmer="Retirer cette moto du panier ?">
                                    
                                </button>
                            </form>
                            <!-- data-confirmer est géré par script.js pour demander confirmation -->
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Bouton vider le panier -->
                <form method="POST" style="margin-top: 10px;">
                    <button type="submit" name="vider" value="1"
                            class="btn btn-secondaire btn-sm"
                            data-confirmer="Vider tout le panier ?">
                        Vider le panier
                    </button>
                </form>
            </div>

            <!-- ── COLONNE DROITE : Résumé et commande ── -->
            <div class="panier-total-carte">
                <h3 style="color:var(--bleu-fonce); margin-bottom:20px;">Résumé</h3>

                <!-- Récapitulatif ligne par ligne -->
                <?php foreach ($articles as $article): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.88rem;">
                        <span style="color:var(--gris);"><?= propre($article['modele']) ?> ×<?= $article['quantite'] ?></span>
                        <span><?= number_format($article['sous_total'], 0, ',', ' ') ?> FCFA</span>
                    </div>
                <?php endforeach; ?>

                <hr class="separateur">

                <!-- Total -->
                <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-weight:800; color:var(--rouge);">
                    <span>Total</span>
                    <span><?= number_format($total, 0, ',', ' ') ?> FCFA</span>
                </div>

                <!-- Livraison gratuite -->
                <div style="text-align:center; color:var(--vert); font-size:0.85rem; margin:12px 0; font-weight:600;">
                     Livraison offerte
                </div>

                <!-- Bouton commander → redirige vers le formulaire de commande -->
                <a href="commande.php?action=passer" class="btn btn-principal btn-plein">
                    Commander →
                </a>

                <a href="catalogue.php" class="btn btn-secondaire btn-plein" style="margin-top:8px; justify-content:center;">
                    Continuer mes achats
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>
