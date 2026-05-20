<?php
/* ============================================================
   admin.php — Dashboard administrateur
   ============================================================
   Page réservée aux admins. Elle contient tout en un seul
   fichier grâce aux onglets JavaScript :
   - Onglet 1 : Statistiques générales
   - Onglet 2 : Gestion des motos (CRUD)
   - Onglet 3 : Gestion des commandes
   - Onglet 4 : Liste des clients
============================================================ */

require_once 'db.php';
require_once 'fonctions.php';
demarrer_session();

// SÉCURITÉ : Réservé aux admins uniquement
if (!est_admin()) {
    set_message('erreur', 'Accès refusé. Réservé aux administrateurs.');
    rediriger('accueil.php');
}

/* ----------------------------------------------------------
   TRAITEMENT DES ACTIONS ADMIN (formulaires POST)
   On identifie l'action grâce au champ caché 'action'
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action_admin = $_POST['action_admin'] ?? '';

    /* ── AJOUTER UNE MOTO ── */
    if ($action_admin === 'ajouter_moto') {
        $marque      = trim($_POST['marque']      ?? '');
        $modele      = trim($_POST['modele']      ?? '');
        $annee       = intval($_POST['annee']     ?? 0);
        $prix        = floatval($_POST['prix']    ?? 0);
        $stock       = intval($_POST['stock']     ?? 0);
        $cylindree   = intval($_POST['cylindree'] ?? 0);
        $puissance   = intval($_POST['puissance'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $nom_image   = ''; // Nom du fichier image

        // Vérifications de base
        if (empty($marque) || empty($modele) || $prix <= 0) {
            set_message('erreur', 'Marque, modèle et prix sont obligatoires.');
            rediriger('admin.php#motos');
        }

        // Traitement de l'upload d'image si un fichier a été envoyé
        if (isset($_FILES['image_fichier']) && $_FILES['image_fichier']['size'] > 0) {
            $fichier   = $_FILES['image_fichier'];               // Infos du fichier uploadé
            $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION)); // Extension du fichier
            $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp']; // Extensions autorisées

            // On vérifie que l'extension est autorisée
            if (!in_array($extension, $extensions_autorisees)) {
                set_message('erreur', 'Format image non supporté. Utilisez JPG, PNG ou WebP.');
                rediriger('admin.php#motos');
            }

            // On vérifie la taille (max 2 Mo = 2*1024*1024 octets)
            if ($fichier['size'] > 2 * 1024 * 1024) {
                set_message('erreur', 'L\'image ne doit pas dépasser 2 Mo.');
                rediriger('admin.php#motos');
            }

            // On génère un nom unique pour éviter les conflits
            // uniqid() génère un identifiant unique basé sur le temps
            $nom_image = uniqid('moto_') . '.' . $extension;
            $chemin_destination = 'images/motos/' . $nom_image;

            // On déplace l'image dans notre dossier
            if (!move_uploaded_file($fichier['tmp_name'], $chemin_destination)) {
                set_message('erreur', 'Erreur lors de l\'upload de l\'image.');
                rediriger('admin.php#motos');
            }
        }

        // Insertion en base de données
        $stmt = $pdo->prepare("
            INSERT INTO motos (marque, modele, annee, prix, stock, cylindree, puissance, description, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$marque, $modele, $annee, $prix, $stock, $cylindree, $puissance, $description, $nom_image]);
        set_message('succes', 'Moto "' . $marque . ' ' . $modele . '" ajoutée avec succès !');
        rediriger('admin.php#motos');
    }

    /* ── MODIFIER UNE MOTO ── */
    if ($action_admin === 'modifier_moto') {
        $id_moto     = intval($_POST['id_moto']   ?? 0);
        $marque      = trim($_POST['marque']      ?? '');
        $modele      = trim($_POST['modele']      ?? '');
        $annee       = intval($_POST['annee']     ?? 0);
        $prix        = floatval($_POST['prix']    ?? 0);
        $stock       = intval($_POST['stock']     ?? 0);
        $cylindree   = intval($_POST['cylindree'] ?? 0);
        $puissance   = intval($_POST['puissance'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if (empty($marque) || empty($modele) || $prix <= 0 || $id_moto <= 0) {
            set_message('erreur', 'Données invalides.');
            rediriger('admin.php#motos');
        }

        // Mise à jour dans la base de données
        $stmt = $pdo->prepare("
            UPDATE motos
            SET marque=?, modele=?, annee=?, prix=?, stock=?, cylindree=?, puissance=?, description=?
            WHERE id = ?
        ");
        $stmt->execute([$marque, $modele, $annee, $prix, $stock, $cylindree, $puissance, $description, $id_moto]);
        set_message('succes', 'Moto modifiée avec succès !');
        rediriger('admin.php#motos');
    }

    /* ── SUPPRIMER UNE MOTO (soft delete : on met actif=0) ── */
    if ($action_admin === 'supprimer_moto') {
        $id_moto = intval($_POST['id_moto'] ?? 0);
        if ($id_moto > 0) {
            // On met actif=0 plutôt que de vraiment supprimer
            // Comme ça les commandes existantes gardent la référence
            $stmt = $pdo->prepare("UPDATE motos SET actif = 0 WHERE id = ?");
            $stmt->execute([$id_moto]);
            set_message('succes', 'Moto retirée du catalogue.');
        }
        rediriger('admin.php#motos');
    }

    /* ── CHANGER LE STATUT D'UNE COMMANDE ── */
    if ($action_admin === 'statut_commande') {
        $id_cmd = intval($_POST['id_cmd'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $statuts_valides = ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'];

        // On vérifie que le statut est dans la liste autorisée
        if ($id_cmd > 0 && in_array($statut, $statuts_valides)) {
            $stmt = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
            $stmt->execute([$statut, $id_cmd]);
            set_message('succes', 'Statut mis à jour.');
        }
        rediriger('admin.php#commandes');
    }

    // On sort si c'était un POST (les autres cas sont traités ci-dessus)
}

/* ----------------------------------------------------------
   RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
---------------------------------------------------------- */

// --- Statistiques ---
$stmt = $pdo->query("SELECT COUNT(*) FROM motos WHERE actif = 1");
$nb_motos = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'client'");
$nb_clients = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM commandes");
$nb_commandes = $stmt->fetchColumn();

// Chiffre d'affaires total des commandes livrées ou confirmées
$stmt = $pdo->query("SELECT SUM(total) FROM commandes WHERE statut IN ('confirmee','expediee','livree')");
$ca_total = $stmt->fetchColumn() ?? 0;

// Nombre de commandes en attente (pour l'alerte)
$stmt = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'");
$nb_en_attente = $stmt->fetchColumn();

// --- Liste des motos ---
$stmt = $pdo->query("SELECT * FROM motos WHERE actif = 1 ORDER BY cree_le DESC");
$toutes_motos = $stmt->fetchAll();

// --- Liste des commandes ---
$stmt = $pdo->query("
    SELECT c.*, u.prenom, u.nom, u.email
    FROM commandes c
    JOIN utilisateurs u ON c.utilisateur_id = u.id
    ORDER BY c.cree_le DESC
");
$toutes_commandes = $stmt->fetchAll();

// --- Liste des clients ---
$stmt = $pdo->query("SELECT * FROM utilisateurs WHERE role = 'client' ORDER BY cree_le DESC");
$tous_clients = $stmt->fetchAll();

// Moto à modifier (si on passe ?modifier=5 dans l'URL)
$id_modifier = intval($_GET['modifier'] ?? 0);
$moto_a_modifier = null;
if ($id_modifier > 0) {
    $stmt = $pdo->prepare("SELECT * FROM motos WHERE id = ?");
    $stmt->execute([$id_modifier]);
    $moto_a_modifier = $stmt->fetch();
}

$titre_page = 'Dashboard Admin';
require_once 'header.php';
?>

<div class="conteneur" style="padding: 25px 0 60px;">

    <!-- En-tête avec alerte si commandes en attente -->
    <div class="flex-entre" style="margin-bottom: 25px;">
        <div>
            <h1 class="titre-section">⚙️ Dashboard Admin</h1>
            <?php if ($nb_en_attente > 0): ?>
                <!-- Alerte visible si des commandes attendent -->
                <div style="color: var(--orange); font-weight: 600; margin-top: 5px;">
                    ⚠️ <?= $nb_en_attente ?> commande<?= $nb_en_attente > 1 ? 's' : '' ?> en attente de traitement
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Onglets de navigation -->
    <!-- data-onglet indique à script.js quel contenu afficher -->
    <div class="onglets">
        <button class="onglet-btn actif" data-onglet="onglet-stats"> Statistiques</button>
        <button class="onglet-btn" data-onglet="onglet-motos"> Motos</button>
        <button class="onglet-btn" data-onglet="onglet-commandes"> Commandes <?php if($nb_en_attente>0): ?><span style="color:var(--rouge)">(<?=$nb_en_attente?>)</span><?php endif; ?></button>
        <button class="onglet-btn" data-onglet="onglet-clients"> Clients</button>
    </div>

    <!-- ═══════════════════════════════
         ONGLET 1 : STATISTIQUES
    ═══════════════════════════════ -->
    <div class="onglet-contenu actif" id="onglet-stats">
        <div class="stats-admin">
            <div class="stat-admin-carte">
                <div class="stat-admin-icone">🏍️</div>
                <span class="stat-admin-chiffre" data-compteur="<?= $nb_motos ?>"><?= $nb_motos ?></span>
                <span class="stat-admin-label">Motos en catalogue</span>
            </div>
            <div class="stat-admin-carte">
                <div class="stat-admin-icone">👥</div>
                <span class="stat-admin-chiffre" data-compteur="<?= $nb_clients ?>"><?= $nb_clients ?></span>
                <span class="stat-admin-label">Clients inscrits</span>
            </div>
            <div class="stat-admin-carte">
                <div class="stat-admin-icone">📦</div>
                <span class="stat-admin-chiffre" data-compteur="<?= $nb_commandes ?>"><?= $nb_commandes ?></span>
                <span class="stat-admin-label">Commandes totales</span>
            </div>
            <div class="stat-admin-carte">
                <div class="stat-admin-icone">💶</div>
                <span class="stat-admin-chiffre"><?= number_format($ca_total, 0, ',', ' ') ?> FCFA</span>
                <span class="stat-admin-label">Chiffre d'affaires</span>
            </div>
        </div>

        <!-- Dernières commandes sur le dashboard stats -->
        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;"> Dernières commandes</h3>
            <table class="tableau-admin">
                <thead>
                    <tr><th>#</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($toutes_commandes, 0, 5) as $cmd): ?>
                        <tr>
                            <td><strong>#<?= $cmd['id'] ?></strong></td>
                            <td><?= propre($cmd['prenom']) ?> <?= propre($cmd['nom']) ?></td>
                            <td style="font-weight:700; color:var(--rouge);"><?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA</td>
                            <td><span class="statut statut-<?= $cmd['statut'] ?>"><?= ucfirst(str_replace('_',' ',$cmd['statut'])) ?></span></td>
                            <td style="color:var(--gris);"><?= date('d/m/Y', strtotime($cmd['cree_le'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══════════════════════════════
         ONGLET 2 : GESTION MOTOS
    ═══════════════════════════════ -->
    <div class="onglet-contenu" id="onglet-motos">

        <!-- Formulaire d'ajout ou de modification -->
        <div class="carte-blanche" style="margin-bottom: 25px;">
            <h3 style="color:var(--bleu-fonce); margin-bottom:20px;">
                <?= $moto_a_modifier ? ' Modifier la moto' : 'Ajouter une moto' ?>
            </h3>

            <form method="POST" enctype="multipart/form-data">
                <!-- Champ caché pour identifier l'action -->
                <input type="hidden" name="action_admin"
                       value="<?= $moto_a_modifier ? 'modifier_moto' : 'ajouter_moto' ?>">
                <?php if ($moto_a_modifier): ?>
                    <!-- Si modification, on envoie l'ID de la moto -->
                    <input type="hidden" name="id_moto" value="<?= $moto_a_modifier['id'] ?>">
                <?php endif; ?>

                <!-- Ligne 1 : Marque, Modèle, Année -->
                <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 15px;">
                    <div class="champ-groupe">
                        <label class="champ-label">Marque *</label>
                        <input type="text" name="marque" class="champ-input"
                               placeholder="Honda"
                               value="<?= propre($moto_a_modifier['marque'] ?? '') ?>"
                               required>
                    </div>
                    <div class="champ-groupe">
                        <label class="champ-label">Modèle *</label>
                        <input type="text" name="modele" class="champ-input"
                               placeholder="CB500F"
                               value="<?= propre($moto_a_modifier['modele'] ?? '') ?>"
                               required>
                    </div>
                    <div class="champ-groupe">
                        <label class="champ-label">Année</label>
                        <input type="number" name="annee" class="champ-input"
                               placeholder="2023" min="1900" max="2030"
                               value="<?= $moto_a_modifier['annee'] ?? date('Y') ?>">
                    </div>
                </div>

                <!-- Ligne 2 : Prix, Stock, Cylindrée, Puissance -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
                    <div class="champ-groupe">
                        <label class="champ-label">Prix (FCFA) *</label>
                        <input type="number" name="prix" class="champ-input"
                               placeholder="7999" min="0" step="0.01"
                               value="<?= $moto_a_modifier['prix'] ?? '' ?>"
                               required>
                    </div>
                    <div class="champ-groupe">
                        <label class="champ-label">Stock</label>
                        <input type="number" name="stock" class="champ-input"
                               placeholder="5" min="0"
                               value="<?= $moto_a_modifier['stock'] ?? 0 ?>">
                    </div>
                    <div class="champ-groupe">
                        <label class="champ-label">Cylindrée (cc)</label>
                        <input type="number" name="cylindree" class="champ-input"
                               placeholder="500"
                               value="<?= $moto_a_modifier['cylindree'] ?? '' ?>">
                    </div>
                    <div class="champ-groupe">
                        <label class="champ-label">Puissance (cv)</label>
                        <input type="number" name="puissance" class="champ-input"
                               placeholder="47"
                               value="<?= $moto_a_modifier['puissance'] ?? '' ?>">
                    </div>
                </div>

                <!-- Description -->
                <div class="champ-groupe">
                    <label class="champ-label">Description</label>
                    <textarea name="description" class="champ-input" rows="3"
                              style="resize: vertical;"
                              placeholder="Description de la moto..."><?= propre($moto_a_modifier['description'] ?? '') ?></textarea>
                </div>

                <?php if (!$moto_a_modifier): ?>
                    <!-- Upload image (seulement à l'ajout pour simplifier) -->
                    <div class="champ-groupe">
                        <label class="champ-label">Image</label>
                        <input type="file" id="image_fichier" name="image_fichier"
                               class="champ-input" accept="image/jpeg,image/png,image/webp">
                        <!-- Preview générée par script.js -->
                        <div id="previewZone" style="display:none; margin-top: 8px;">
                            <img id="previewImage" style="max-height: 150px; border-radius: 8px;">
                        </div>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-principal">
                        <?= $moto_a_modifier ? ' Sauvegarder' : ' Ajouter la moto' ?>
                    </button>
                    <?php if ($moto_a_modifier): ?>
                        <a href="admin.php#motos" class="btn btn-secondaire">Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Liste des motos -->
        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;">
                 Catalogue (<?= count($toutes_motos) ?> motos)
            </h3>
            <table class="tableau-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marque / Modèle</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Année</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($toutes_motos as $moto): ?>
                        <tr>
                            <td style="color:var(--gris);">#<?= $moto['id'] ?></td>
                            <td>
                                <strong><?= propre($moto['marque']) ?></strong>
                                <?= propre($moto['modele']) ?>
                            </td>
                            <td style="font-weight:700; color:var(--rouge);">
                                <?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td>
                                <!-- Couleur selon le niveau de stock -->
                                <span style="font-weight:600; color: <?= $moto['stock'] > 0 ? 'var(--vert)' : 'var(--rouge)' ?>">
                                    <?= $moto['stock'] ?>
                                </span>
                            </td>
                            <td><?= $moto['annee'] ?></td>
                            <td style="display: flex; gap: 5px;">
                                <!-- Lien modifier (charge le formulaire pré-rempli) -->
                                <a href="admin.php?modifier=<?= $moto['id'] ?>#motos"
                                   class="btn btn-secondaire btn-sm">✏️</a>

                                <!-- Formulaire de suppression -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action_admin" value="supprimer_moto">
                                    <input type="hidden" name="id_moto" value="<?= $moto['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            data-confirmer="Retirer '<?= propre($moto['modele']) ?>' du catalogue ?">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══════════════════════════════
         ONGLET 3 : COMMANDES
    ═══════════════════════════════ -->
    <div class="onglet-contenu" id="onglet-commandes">
        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;">
                 Toutes les commandes (<?= count($toutes_commandes) ?>)
            </h3>
            <?php if (empty($toutes_commandes)): ?>
                <p style="color:var(--gris); text-align:center; padding:30px;">Aucune commande pour le moment.</p>
            <?php else: ?>
                <table class="tableau-admin">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Statut actuel</th>
                            <th>Changer le statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($toutes_commandes as $cmd): ?>
                            <tr>
                                <td><strong>#<?= $cmd['id'] ?></strong></td>
                                <td>
                                    <div style="font-weight:600;"><?= propre($cmd['prenom']) ?> <?= propre($cmd['nom']) ?></div>
                                    <div style="color:var(--gris); font-size:0.8rem;"><?= propre($cmd['email']) ?></div>
                                </td>
                                <td style="font-weight:700; color:var(--rouge);">
                                    <?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA
                                </td>
                                <td>
                                    <span class="statut statut-<?= $cmd['statut'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Menu déroulant pour changer le statut directement -->
                                    <form method="POST" style="display:flex; gap:5px; align-items:center;">
                                        <input type="hidden" name="action_admin" value="statut_commande">
                                        <input type="hidden" name="id_cmd" value="<?= $cmd['id'] ?>">
                                        <select name="statut" class="champ-input" style="padding:5px 8px; font-size:0.82rem;">
                                            <option value="en_attente"  <?= $cmd['statut'] === 'en_attente'  ? 'selected' : '' ?>>En attente</option>
                                            <option value="confirmee"   <?= $cmd['statut'] === 'confirmee'   ? 'selected' : '' ?>>Confirmée</option>
                                            <option value="expediee"    <?= $cmd['statut'] === 'expediee'    ? 'selected' : '' ?>>Expédiée</option>
                                            <option value="livree"      <?= $cmd['statut'] === 'livree'      ? 'selected' : '' ?>>Livrée</option>
                                            <option value="annulee"     <?= $cmd['statut'] === 'annulee'     ? 'selected' : '' ?>>Annulée</option>
                                        </select>
                                        <button type="submit" class="btn btn-vert btn-sm">✓</button>
                                    </form>
                                </td>
                                <td style="color:var(--gris); font-size:0.85rem;">
                                    <?= date('d/m/Y', strtotime($cmd['cree_le'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════
         ONGLET 4 : CLIENTS
    ═══════════════════════════════ -->
    <div class="onglet-contenu" id="onglet-clients">
        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;">
                 Clients inscrits (<?= count($tous_clients) ?>)
            </h3>
            <table class="tableau-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tous_clients as $client): ?>
                        <tr>
                            <td style="color:var(--gris);">#<?= $client['id'] ?></td>
                            <td>
                                <!-- Avatar avec initiale + nom -->
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:32px; height:32px; background:var(--rouge); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0;">
                                        <?= strtoupper(substr($client['prenom'], 0, 1)) ?>
                                    </div>
                                    <?= propre($client['prenom']) ?> <?= propre($client['nom']) ?>
                                </div>
                            </td>
                            <td><?= propre($client['email']) ?></td>
                            <td><?= propre($client['telephone'] ?: '—') ?></td>
                            <td style="color:var(--gris);"><?= date('d/m/Y', strtotime($client['cree_le'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
