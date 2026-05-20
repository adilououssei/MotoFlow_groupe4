<?php
/* ============================================================
   commande.php — Passer une commande + Historique
   ============================================================
   Ce fichier gère 4 situations selon l'URL :

   1. POST vers commande.php?action=passer  → traitement de la commande
   2. commande.php?action=passer  → formulaire adresse
   3. commande.php?id=5           → détail d'une commande
   4. commande.php                → historique de toutes les commandes

   FLUX NORMAL :
   panier.php → [Commander] → commande.php?action=passer (formulaire)
             → [Confirmer]  → POST traité → commande créée
             → Redirect vers commande.php?id=XX (confirmation)
============================================================ */

require_once 'db.php';
require_once 'fonctions.php';
demarrer_session();

// Réservé aux utilisateurs connectés
if (!est_connecte()) {
    set_message('erreur', 'Connectez-vous pour accéder à vos commandes.');
    rediriger('connexion.php');
}

// Vérifie que l'utilisateur en session existe vraiment en BD
verifier_session($pdo);

/* ============================================================
   CAS 1 : TRAITEMENT POST — Création de la commande
   Déclenché quand on clique "Confirmer ma commande"
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $adresse = trim($_POST['adresse'] ?? '');

    // L'adresse est obligatoire
    if (empty($adresse)) {
        set_message('erreur', 'Veuillez indiquer une adresse de livraison.');
        rediriger('commande.php?action=passer');
    }

    // Récupérer le contenu du panier avec les infos de chaque moto
    $stmt = $pdo->prepare("
        SELECT p.moto_id, p.quantite, m.prix, m.stock, m.marque, m.modele
        FROM panier p
        JOIN motos m ON p.moto_id = m.id
        WHERE p.utilisateur_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $articles = $stmt->fetchAll();

    // Panier vide = impossible de commander
    if (empty($articles)) {
        set_message('erreur', 'Votre panier est vide.');
        rediriger('panier.php');
    }

    // Vérifier le stock pour chaque moto
    foreach ($articles as $art) {
        if ($art['quantite'] > $art['stock']) {
            set_message('erreur', 'Stock insuffisant pour ' . $art['marque'] . ' ' . $art['modele']);
            rediriger('panier.php');
        }
    }

    // Calculer le total
    $total = 0;
    foreach ($articles as $art) {
        $total += $art['prix'] * $art['quantite'];
    }

    /* ----------------------------------------------------------
       TRANSACTION : toutes les opérations ou aucune
    ---------------------------------------------------------- */
    try {
        $pdo->beginTransaction();

        // 1. Créer la commande principale
        $stmt = $pdo->prepare("
            INSERT INTO commandes (utilisateur_id, total, adresse, statut)
            VALUES (?, ?, ?, 'en_attente')
        ");
        $stmt->execute([$_SESSION['user_id'], $total, $adresse]);
        $id_cmd = $pdo->lastInsertId(); // ID de la nouvelle commande

        // 2. Enregistrer chaque moto dans commande_details + réduire le stock
        foreach ($articles as $art) {
            // Insérer la ligne de détail
            $stmt = $pdo->prepare("
                INSERT INTO commande_details (commande_id, moto_id, quantite, prix_unitaire)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$id_cmd, $art['moto_id'], $art['quantite'], $art['prix']]);

            // Réduire le stock de la moto
            $stmt = $pdo->prepare("UPDATE motos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$art['quantite'], $art['moto_id']]);
        }

        // 3. Vider le panier
        $stmt = $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        // Tout a réussi → on valide
        $pdo->commit();

        set_message('succes', '
         Commande #' . $id_cmd . ' confirmée ! L\'équipe MotoFlow va la traiter.');
        rediriger('commande.php?id=' . $id_cmd);

    } catch (Exception $e) {
        $pdo->rollBack(); // Annuler tout en cas d'erreur
        set_message('erreur', 'Erreur : ' . $e->getMessage());
        rediriger('panier.php');
    }
}

/* ============================================================
   CAS 2 : DÉTAIL D'UNE COMMANDE (?id=5)
============================================================ */
$id_commande = intval($_GET['id'] ?? 0);

if ($id_commande > 0) {

    // Récupérer la commande (seulement celle de l'utilisateur connecté)
    $stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ? AND utilisateur_id = ?");
    $stmt->execute([$id_commande, $_SESSION['user_id']]);
    $commande = $stmt->fetch();

    if (!$commande) {
        set_message('erreur', 'Commande introuvable.');
        rediriger('commande.php');
    }

    // Récupérer le détail des motos commandées
    $stmt = $pdo->prepare("
        SELECT cd.quantite, cd.prix_unitaire,
               m.marque, m.modele,
               cd.quantite * cd.prix_unitaire AS sous_total
        FROM commande_details cd
        JOIN motos m ON cd.moto_id = m.id
        WHERE cd.commande_id = ?
    ");
    $stmt->execute([$id_commande]);
    $details = $stmt->fetchAll();

    // Infos d'affichage pour chaque statut
    $statuts = [
        'en_attente' => ['icone' => '', 'texte' => 'En attente de traitement', 'fond' => '#fff3cd', 'couleur' => '#856404'],
        'confirmee'  => ['icone' => '', 'texte' => 'Commande confirmée',       'fond' => '#d4edda', 'couleur' => '#155724'],
        'expediee'   => ['icone' => '', 'texte' => 'En cours de livraison',    'fond' => '#cce5ff', 'couleur' => '#004085'],
        'livree'     => ['icone' => '', 'texte' => 'Livrée avec succès',       'fond' => '#d4edda', 'couleur' => '#155724'],
        'annulee'    => ['icone' => '', 'texte' => 'Commande annulée',         'fond' => '#f8d7da', 'couleur' => '#721c24'],
    ];
    $s = $statuts[$commande['statut']] ?? ['icone' => '?', 'texte' => $commande['statut'], 'fond' => '#eee', 'couleur' => '#333'];

    $titre_page = 'Commande #' . $id_commande;
    require_once 'header.php';
    ?>

    <div class="conteneur" style="padding:30px 0 60px;">

        <!-- En-tête avec badge statut -->
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:25px; flex-wrap:wrap;">
            <h1 class="titre-section" style="margin:0;"> Commande #<?= $id_commande ?></h1>
            <span style="background:<?= $s['fond'] ?>; color:<?= $s['couleur'] ?>; padding:6px 16px; border-radius:20px; font-weight:700; font-size:0.9rem;">
                <?= $s['icone'] ?> <?= $s['texte'] ?>
            </span>
        </div>

        <div style="display:grid; grid-template-columns:1fr 320px; gap:25px; align-items:start;">

            <!-- Détail des motos -->
            <div class="carte-blanche">
                <h3 style="color:var(--bleu-fonce); margin-bottom:18px;"> Motos commandées</h3>

                <?php foreach ($details as $det): ?>
                    <div style="display:grid; grid-template-columns:50px 1fr auto; gap:15px; align-items:center; padding:12px 0; border-bottom:1px solid var(--gris-clair);">
                        <div style="font-size:2rem; text-align:center;">🏍️</div>
                        <div>
                            <div style="color:var(--rouge); font-size:0.78rem; font-weight:700; text-transform:uppercase;"><?= propre($det['marque']) ?></div>
                            <div style="font-weight:700; color:var(--bleu-fonce);"><?= propre($det['modele']) ?></div>
                            <div style="color:var(--gris); font-size:0.83rem;">
                                <?= $det['quantite'] ?> × <?= number_format($det['prix_unitaire'], 0, ',', ' ') ?> FCFA
                            </div>
                        </div>
                        <div style="font-weight:800; color:var(--rouge); white-space:nowrap;">
                            <?= number_format($det['sous_total'], 0, ',', ' ') ?> FCFA
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Total -->
                <div style="display:flex; justify-content:space-between; padding-top:14px; font-weight:800; font-size:1.1rem;">
                    <span>Total payé</span>
                    <span style="color:var(--rouge); font-size:1.2rem;"><?= number_format($commande['total'], 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>

            <!-- Infos de livraison -->
            <div>
                <div class="carte-blanche" style="margin-bottom:12px;">
                    <h3 style="color:var(--bleu-fonce); margin-bottom:15px;"> Informations</h3>

                    <div style="margin-bottom:12px;">
                        <div style="color:var(--gris); font-size:0.75rem; font-weight:700; text-transform:uppercase; margin-bottom:3px;">Date</div>
                        <div style="font-weight:600;"><?= date('d/m/Y à H:i', strtotime($commande['cree_le'])) ?></div>
                    </div>

                    <div>
                        <div style="color:var(--gris); font-size:0.75rem; font-weight:700; text-transform:uppercase; margin-bottom:3px;">Adresse de livraison</div>
                        <div style="font-weight:600; line-height:1.6;"><?= nl2br(propre($commande['adresse'])) ?></div>
                    </div>
                </div>

                <!-- Message selon statut -->
                <div style="background:<?= $s['fond'] ?>; border-radius:var(--rayon-sm); padding:14px; font-size:0.87rem; color:<?= $s['couleur'] ?>; line-height:1.6; margin-bottom:12px;">
                    <?= $s['icone'] ?> <?= $s['texte'] ?>
                    <?php if ($commande['statut'] === 'en_attente'): ?>
                        <br><small>L'admin va traiter votre commande sous 24h.</small>
                    <?php elseif ($commande['statut'] === 'expediee'): ?>
                        <br><small>Livraison prévue sous 48h.</small>
                    <?php endif; ?>
                </div>

                <a href="commande.php" class="btn btn-secondaire btn-plein" style="justify-content:center;">
                    ← Toutes mes commandes
                </a>
            </div>
        </div>
    </div>

    <?php
    require_once 'footer.php';
    exit();
}

/* ============================================================
   CAS 3 : FORMULAIRE DE COMMANDE (?action=passer)
============================================================ */
$action = $_GET['action'] ?? '';

if ($action === 'passer') {

    // Vérifier que le panier n'est pas vide
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM panier WHERE utilisateur_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetchColumn() == 0) {
        set_message('erreur', 'Votre panier est vide.');
        rediriger('panier.php');
    }

    // Contenu du panier pour l'aperçu
    $stmt = $pdo->prepare("
        SELECT p.quantite, m.marque, m.modele, m.prix,
               p.quantite * m.prix AS sous_total
        FROM panier p
        JOIN motos m ON p.moto_id = m.id
        WHERE p.utilisateur_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $apercu = $stmt->fetchAll();

    $total_panier = array_sum(array_column($apercu, 'sous_total'));

    $titre_page = 'Finaliser ma commande';
    require_once 'header.php';
    ?>

    <div class="conteneur" style="padding:30px 0 60px;">
        <h1 class="titre-section"> Finaliser ma commande</h1>
        <p class="sous-titre-section">Vérifiez votre commande et indiquez votre adresse de livraison</p>

        <div style="display:grid; grid-template-columns:1fr 340px; gap:30px; align-items:start;">

            <!-- Formulaire adresse -->
            <!-- IMPORTANT : action="commande.php?action=passer" envoie le POST ici -->
            <form method="POST" action="commande.php?action=passer">

                <div class="formulaire-carte">
                    <h3 style="color:var(--bleu-fonce); margin-bottom:20px;"> Adresse de livraison</h3>

                    <!-- Téléphone -->
                    <div class="champ-groupe">
                        <label class="champ-label" for="telephone"> Téléphone de contact</label>
                        <input type="tel" id="telephone" name="telephone"
                               class="champ-input"
                               placeholder="+228 90 00 00 00">
                    </div>

                    <!-- Adresse -->
                    <div class="champ-groupe">
                        <label class="champ-label" for="adresse">Adresse complète *</label>
                        <textarea id="adresse" name="adresse"
                                  class="champ-input" rows="5"
                                  style="resize:vertical;"
                                  placeholder="Quartier, Rue, Ville&#10;Ex: Quartier Hédzranawoé, Lomé, Togo"
                                  required><?= propre($_POST['adresse'] ?? '') ?></textarea>
                    </div>

                    <!-- Bouton de confirmation -->
                    <button type="submit" class="btn btn-principal btn-plein" style="padding:14px; font-size:1rem; margin-top:5px;">
                         Confirmer la commande — <?= number_format($total_panier, 0, ',', ' ') ?> FCFA
                    </button>

                    <a href="panier.php" class="btn btn-secondaire btn-plein" style="margin-top:10px; justify-content:center;">
                        ← Retour au panier
                    </a>
                </div>

            </form>

            <!-- Récapitulatif -->
            <div>
                <div class="formulaire-carte">
                    <h3 style="color:var(--bleu-fonce); margin-bottom:15px;"> Récapitulatif</h3>

                    <?php foreach ($apercu as $ligne): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--gris-clair); gap:10px;">
                            <div>
                                <div style="font-weight:700; font-size:0.9rem; color:var(--bleu-fonce);">
                                    <?= propre($ligne['marque']) ?> <?= propre($ligne['modele']) ?>
                                </div>
                                <div style="color:var(--gris); font-size:0.8rem;">
                                    <?= $ligne['quantite'] ?> × <?= number_format($ligne['prix'], 0, ',', ' ') ?> FCFA
                                </div>
                            </div>
                            <div style="font-weight:700; color:var(--rouge); white-space:nowrap;">
                                <?= number_format($ligne['sous_total'], 0, ',', ' ') ?> FCFA
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="display:flex; justify-content:space-between; padding:10px 0; color:var(--vert); font-weight:600; font-size:0.9rem;">
                        <span> Livraison</span>
                        <span>Gratuite</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; padding-top:12px; border-top:2px solid var(--bleu-fonce); font-weight:800; font-size:1.1rem;">
                        <span>Total</span>
                        <span style="color:var(--rouge);"><?= number_format($total_panier, 0, ',', ' ') ?> FCFA</span>
                    </div>
                </div>

                <div style="background:#f0f4f8; border-radius:var(--rayon-sm); padding:12px; margin-top:12px; font-size:0.82rem; color:var(--gris); line-height:1.6;">
                     Vos données sont sécurisées.<br>
                    La commande sera traitée par notre équipe dans les 24h.
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once 'footer.php';
    exit();
}

/* ============================================================
   CAS 4 : HISTORIQUE DES COMMANDES (page par défaut)
============================================================ */
$stmt = $pdo->prepare("SELECT * FROM commandes WHERE utilisateur_id = ? ORDER BY cree_le DESC");
$stmt->execute([$_SESSION['user_id']]);
$commandes = $stmt->fetchAll();

$titre_page = 'Mes commandes';
require_once 'header.php';
?>

<div class="conteneur" style="padding:30px 0 60px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
            <h1 class="titre-section">Mes commandes</h1>
            <p class="sous-titre-section"><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></p>
        </div>
        <a href="catalogue.php" class="btn btn-principal"> Continuer mes achats</a>
    </div>

    <?php if (empty($commandes)): ?>
        <div style="text-align:center; padding:60px 20px; background:white; border-radius:var(--rayon); box-shadow:var(--ombre);">
            <div style="font-size:4rem; margin-bottom:15px;">📦</div>
            <h3 style="color:var(--bleu-fonce); margin-bottom:10px;">Aucune commande</h3>
            <p style="color:var(--gris); margin-bottom:25px;">Vous n'avez pas encore passé de commande.</p>
            <a href="catalogue.php" class="btn btn-principal">Découvrir le catalogue</a>
        </div>

    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:15px;">
            <?php foreach ($commandes as $cmd):
                $statuts = [
                    'en_attente' => ['icone' => '', 'texte' => 'En attente',  'fond' => '#fff3cd', 'couleur' => '#856404'],
                    'confirmee'  => ['icone' => '', 'texte' => 'Confirmée',   'fond' => '#d4edda', 'couleur' => '#155724'],
                    'expediee'   => ['icone' => '', 'texte' => 'Expédiée',    'fond' => '#cce5ff', 'couleur' => '#004085'],
                    'livree'     => ['icone' => '', 'texte' => 'Livrée',      'fond' => '#d4edda', 'couleur' => '#155724'],
                    'annulee'    => ['icone' => '', 'texte' => 'Annulée',     'fond' => '#f8d7da', 'couleur' => '#721c24'],
                ];
                $s = $statuts[$cmd['statut']] ?? ['icone' => '?', 'texte' => $cmd['statut'], 'fond' => '#eee', 'couleur' => '#333'];
            ?>
                <div style="background:white; border-radius:var(--rayon); padding:20px 25px; box-shadow:var(--ombre); display:grid; grid-template-columns:55px 1fr auto; gap:20px; align-items:center;">

                    <!-- Numéro commande -->
                    <div style="width:50px; height:50px; background:var(--bleu-fonce); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.8rem; flex-shrink:0;">
                        #<?= $cmd['id'] ?>
                    </div>

                    <!-- Infos -->
                    <div>
                        <div style="font-weight:700; color:var(--bleu-fonce); margin-bottom:3px;">
                            Commande du <?= date('d/m/Y à H:i', strtotime($cmd['cree_le'])) ?>
                        </div>
                        <div style="font-weight:800; color:var(--rouge); font-size:1.05rem;">
                            <?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA
                        </div>
                        <div style="color:var(--gris); font-size:0.82rem; margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:350px;">
                            📍 <?= propre($cmd['adresse']) ?>
                        </div>
                    </div>

                    <!-- Statut + bouton -->
                    <div style="display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
                        <span style="background:<?= $s['fond'] ?>; color:<?= $s['couleur'] ?>; padding:4px 12px; border-radius:20px; font-size:0.8rem; font-weight:700; white-space:nowrap;">
                            <?= $s['icone'] ?> <?= $s['texte'] ?>
                        </span>
                        <a href="commande.php?id=<?= $cmd['id'] ?>" class="btn btn-secondaire btn-sm">Voir →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>
