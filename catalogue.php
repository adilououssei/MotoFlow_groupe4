<?php
/* ============================================================
   catalogue.php — Liste de toutes les motos avec filtres
   ============================================================
   Cette page affiche toutes les motos disponibles.
   L'utilisateur peut filtrer par : recherche, marque, prix.
   Les filtres utilisent la méthode GET (données dans l'URL).
   Exemple d'URL : catalogue.php?recherche=honda&prix_max=8000
============================================================ */

require_once 'db.php';
require_once 'fonctions.php';
demarrer_session();

/* ----------------------------------------------------------
   RÉCUPÉRATION DES FILTRES
   On lit les valeurs envoyées dans l'URL (?recherche=...)
   Si pas de valeur, on met une valeur par défaut
---------------------------------------------------------- */
// ?? '' signifie "si vide ou non défini, utiliser ''"
$recherche  = trim($_GET['recherche']  ?? '');  // Texte de recherche
$filtre_marque = trim($_GET['marque']  ?? '');  // Filtre par marque
$prix_min   = (float)($_GET['prix_min'] ?? 0);  // Prix minimum (0 = pas de limite)
$prix_max   = (float)($_GET['prix_max'] ?? 0);  // Prix maximum (0 = pas de limite)
$tri        = $_GET['tri']             ?? 'recent'; // Tri par défaut : plus récent

/* ----------------------------------------------------------
   CONSTRUCTION DE LA REQUÊTE SQL DYNAMIQUE
   On construit la requête selon les filtres actifs.
   On utilise des requêtes préparées avec des ?
   pour sécuriser les valeurs envoyées par l'utilisateur.
---------------------------------------------------------- */
$sql    = "SELECT * FROM motos WHERE actif = 1"; // Base de la requête
$params = []; // Tableau des paramètres pour la requête préparée

// Si l'utilisateur a tapé quelque chose dans la recherche
if (!empty($recherche)) {
    // LIKE '%?%' cherche dans le marque ET le modele ET la description
    // On utilise CONCAT pour construire le pattern avec %
    $sql .= " AND (marque LIKE ? OR modele LIKE ? OR description LIKE ?)";
    $params[] = '%' . $recherche . '%'; // Pour la marque
    $params[] = '%' . $recherche . '%'; // Pour le modele
    $params[] = '%' . $recherche . '%'; // Pour la description
}

// Si l'utilisateur a choisi une marque dans le menu déroulant
if (!empty($filtre_marque)) {
    $sql .= " AND marque = ?"; // Filtre exact sur la marque
    $params[] = $filtre_marque;
}

// Si un prix minimum est défini
if ($prix_min > 0) {
    $sql .= " AND prix >= ?"; // Prix supérieur ou égal au minimum
    $params[] = $prix_min;
}

// Si un prix maximum est défini
if ($prix_max > 0) {
    $sql .= " AND prix <= ?"; // Prix inférieur ou égal au maximum
    $params[] = $prix_max;
}

// Ajout du tri (ORDER BY)
// On utilise un switch pour éviter l'injection SQL dans ORDER BY
switch ($tri) {
    case 'prix_asc':
        $sql .= " ORDER BY prix ASC";
        break; // Prix croissant
    case 'prix_desc':
        $sql .= " ORDER BY prix DESC";
        break; // Prix décroissant
    case 'annee_desc':
        $sql .= " ORDER BY annee DESC";
        break; // Plus récent
    default:
        $sql .= " ORDER BY cree_le DESC";
        break; // Plus récemment ajouté
}

// On exécute la requête avec les paramètres
$stmt = $pdo->prepare($sql);
$stmt->execute($params); // On envoie tous les paramètres
$motos = $stmt->fetchAll(); // On récupère toutes les motos trouvées

// On compte le total pour l'affichage
$nb_resultats = count($motos);

// Récupérer la liste des marques pour le menu déroulant de filtre
// DISTINCT = une seule fois chaque marque, ORDER BY = ordre alphabétique
$stmt_marques = $pdo->query("SELECT DISTINCT marque FROM motos WHERE actif = 1 ORDER BY marque");
$marques = $stmt_marques->fetchAll();

$titre_page = 'Catalogue';
require_once 'header.php';
?>

<div class="conteneur" style="padding-top: 25px;">

    <!-- En-tête de la page -->
    <div class="flex-entre" style="margin-bottom: 20px;">
        <div>
            <h1 class="titre-section">Catalogue</h1>
            <p class="sous-titre-section">
                <?= $nb_resultats ?> moto<?= $nb_resultats > 1 ? 's' : '' ?> trouvée<?= $nb_resultats > 1 ? 's' : '' ?>
                <?php if (!empty($recherche)): ?>
                    pour "<strong><?= propre($recherche) ?></strong>"
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Mise en page : filtres à gauche, grille à droite -->
    <div class="catalogue-mise-en-page">

        <!-- ── SIDEBAR FILTRES (gauche) ── -->
        <!-- Le formulaire utilise GET pour mettre les filtres dans l'URL -->
        <!-- Comme ça l'URL peut être partagée ou mise en favori -->
        <aside>
            <form action="" method="GET" class="filtres">
                <div class="filtres-titre"> Filtres</div>

                <!-- Recherche textuelle -->
                <div class="filtre-groupe">
                    <label for="recherche">Rechercher</label>
                    <input type="text" id="recherche" name="recherche"
                        placeholder="Honda, Yamaha..."
                        value="<?= propre($recherche) ?>"
                        class="champ-input">
                </div>

                <!-- Filtre par marque -->
                <div class="filtre-groupe">
                    <label for="marque">Marque</label>
                    <select id="marque" name="marque" class="champ-input">
                        <option value="">Toutes les marques</option>
                        <?php foreach ($marques as $m): ?>
                            <!-- selected si c'est la marque actuellement filtrée -->
                            <option value="<?= propre($m['marque']) ?>"
                                <?= $filtre_marque === $m['marque'] ? 'selected' : '' ?>>
                                <?= propre($m['marque']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre par prix -->
                <div class="filtre-groupe">
                    <label for="prix_min">Prix minimum (FCFA)</label>
                    <input type="number" id="prix_min" name="prix_min"
                        placeholder="0"
                        value="<?= $prix_min > 0 ? $prix_min : '' ?>"
                        min="0" step="100"
                        class="champ-input">
                </div>

                <div class="filtre-groupe">
                    <label for="prix_max">Prix maximum (FCFA)</label>
                    <input type="number" id="prix_max" name="prix_max"
                        placeholder="Sans limite"
                        value="<?= $prix_max > 0 ? $prix_max : '' ?>"
                        min="0" step="100"
                        class="champ-input">
                </div>

                <!-- Tri -->
                <div class="filtre-groupe">
                    <label for="tri">Trier par</label>
                    <select id="tri" name="tri" class="champ-input">
                        <option value="recent" <?= $tri === 'recent'     ? 'selected' : '' ?>>Plus récent</option>
                        <option value="prix_asc" <?= $tri === 'prix_asc'   ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="prix_desc" <?= $tri === 'prix_desc'  ? 'selected' : '' ?>>Prix décroissant</option>
                        <option value="annee_desc" <?= $tri === 'annee_desc' ? 'selected' : '' ?>>Année (récent)</option>
                    </select>
                </div>

                <!-- Boutons -->
                <button type="submit" class="btn btn-principal btn-plein" style="margin-bottom:8px;">
                    Appliquer
                </button>
                <a href="catalogue.php" class="btn btn-secondaire btn-plein" style="justify-content:center; font-size:0.9rem;">
                    ✕ Réinitialiser
                </a>
            </form>
        </aside>

        <!-- ── GRILLE DES MOTOS (droite) ── -->
        <div>
            <?php if (empty($motos)): ?>
                <!-- Message si aucun résultat -->
                <div style="text-align:center; padding: 60px 20px; background:white; border-radius:var(--rayon); box-shadow:var(--ombre);">
                    <div style="font-size: 4rem; margin-bottom: 15px;"></div>
                    <h3 style="color:var(--bleu-fonce); margin-bottom: 10px;">Aucune moto trouvée</h3>
                    <p style="color:var(--gris);">Essayez d'autres critères de recherche</p>
                    <a href="catalogue.php" class="btn btn-principal" style="margin-top:20px;">
                        Voir toutes les motos
                    </a>
                </div>

            <?php else: ?>
                <!-- Grille des cartes -->
                <div class="grille-motos">
                    <?php foreach ($motos as $moto): ?>
                        <div class="carte-moto fade-in">

                            <!-- Image ou placeholder -->
                            <?php if (!empty($moto['image']) && file_exists('images/motos/' . $moto['image'])): ?>
                                <img class="carte-moto-image"
                                    src="images/motos/<?= propre($moto['image']) ?>"
                                    alt="<?= propre($moto['marque']) ?> <?= propre($moto['modele']) ?>">
                            <?php else: ?>
                                <div class="carte-moto-placeholder"></div>
                            <?php endif; ?>

                            <div class="carte-moto-corps">
                                <div class="carte-moto-marque"><?= propre($moto['marque']) ?></div>
                                <div class="carte-moto-modele"><?= propre($moto['modele']) ?></div>
                                <div class="carte-moto-specs">
                                    <span>annee <?= $moto['annee'] ?></span>
                                    <span>cylindre <?= $moto['cylindree'] ?> cc</span>
                                    <span>puissa <?= $moto['puissance'] ?> cv</span>
                                </div>
                                <?php if ($moto['stock'] > 3): ?>
                                    <span class="badge-stock stock-dispo">En stock</span>
                                <?php elseif ($moto['stock'] > 0): ?>
                                    <span class="badge-stock stock-faible"> Stock limité (<?= $moto['stock'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge-stock stock-epuise"> Épuisé</span>
                                <?php endif; ?>
                            </div>

                            <div class="carte-moto-pied">
                                <span class="carte-moto-prix">
                                    <?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA
                                </span>
                                <a href="detail.php?id=<?= $moto['id'] ?>" class="btn btn-principal btn-sm">
                                    Voir →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>