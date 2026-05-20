<?php
/* ============================================================
   inscription.php — Page d'inscription
   ============================================================
   Comme connexion.php, ce fichier traite le formulaire
   en POST et l'affiche en GET. Tout dans un seul fichier !
============================================================ */

require_once 'db.php';
require_once 'fonctions.php';
demarrer_session();

// Si déjà connecté, on redirige
if (est_connecte()) {
    rediriger('accueil.php');
}

// Tableau pour stocker les erreurs de validation
$erreurs = [];

/* ----------------------------------------------------------
   TRAITEMENT DU FORMULAIRE EN POST
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On récupère et nettoie toutes les valeurs du formulaire
    $prenom    = trim($_POST['prenom']             ?? '');
    $nom       = trim($_POST['nom']                ?? '');
    $email     = trim($_POST['email']              ?? '');
    $telephone = trim($_POST['telephone']          ?? '');
    $mdp       = trim($_POST['mot_de_passe']       ?? '');
    $mdp2      = trim($_POST['mot_de_passe_confirm'] ?? '');

    /* ── VALIDATION DES DONNÉES ──
       On vérifie que tout est correct AVANT d'insérer en base
    */

    // Vérification du prénom
    if (empty($prenom)) {
        $erreurs[] = 'Le prénom est obligatoire.';
    } elseif (strlen($prenom) < 2) {
        $erreurs[] = 'Le prénom doit avoir au moins 2 caractères.';
    }

    // Vérification du nom
    if (empty($nom)) {
        $erreurs[] = 'Le nom est obligatoire.';
    }

    // Vérification de l'email
    if (empty($email)) {
        $erreurs[] = 'L\'adresse email est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var avec FILTER_VALIDATE_EMAIL vérifie le format email
        $erreurs[] = 'L\'adresse email n\'est pas valide.';
    } else {
        // On vérifie que l'email n'est pas déjà utilisé
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // fetch() retourne un résultat si l'email existe déjà
            $erreurs[] = 'Cette adresse email est déjà utilisée.';
        }
    }

    // Vérification du mot de passe
    if (empty($mdp)) {
        $erreurs[] = 'Le mot de passe est obligatoire.';
    } elseif (strlen($mdp) < 8) {
        // On impose un minimum de 8 caractères
        $erreurs[] = 'Le mot de passe doit avoir au moins 8 caractères.';
    }

    // Vérification de la confirmation du mot de passe
    if ($mdp !== $mdp2) {
        $erreurs[] = 'Les mots de passe ne correspondent pas.';
    }

    /* ── INSERTION EN BASE DE DONNÉES ──
       Seulement s'il n'y a aucune erreur
    */
    if (empty($erreurs)) {
        // On HASH le mot de passe avant de le stocker
        // PASSWORD_DEFAULT utilise bcrypt, l'algorithme recommandé
        // On ne stocke JAMAIS un mot de passe en clair !
        $mdp_hache = password_hash($mdp, PASSWORD_DEFAULT);

        // Requête d'insertion dans la table utilisateurs
        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs (prenom, nom, email, mot_de_passe, telephone, role)
            VALUES (?, ?, ?, ?, ?, 'client')
        ");
        // On exécute avec nos données dans l'ordre des ?
        $stmt->execute([$prenom, $nom, $email, $mdp_hache, $telephone]);

        // On récupère l'ID du nouvel utilisateur inséré
        $nouvel_id = $pdo->lastInsertId();

        // On connecte automatiquement le nouvel inscrit
        $_SESSION['user_id'] = $nouvel_id;
        $_SESSION['prenom']  = $prenom;
        $_SESSION['nom']     = $nom;
        $_SESSION['email']   = $email;
        $_SESSION['role']    = 'client';
        session_regenerate_id(true); // Sécurité : nouveau ID de session

        set_message('succes', 'Compte créé avec succès ! Bienvenue ' . $prenom . ' !');
        rediriger('accueil.php'); // On redirige vers l'accueil
    }
}

$titre_page = 'Inscription';
require_once 'header.php';
?>

<div class="page-auth">

    <!-- Panneau gauche -->
    <div class="auth-visuel auth-visuel-register">
        <div class="auth-visuel-contenu">
            <img src="logo.png" alt="MotoFlow" onerror="this.style.display='none'">
            <h2 class="auth-visuel-titre">
                Rejoignez la<br>communauté MotoFlow
            </h2>
            <p class="auth-visuel-texte">
                Créez votre compte en quelques secondes et accédez
                à notre catalogue de motos premium.
            </p>
            <!-- Étapes pour rassurer l'utilisateur -->
            <div class="auth-avantages">
                <div class="auth-avantage">
                    <span class="auth-avantage-icone">1️</span>
                    <span class="auth-avantage-texte">Créez votre compte gratuit</span>
                </div>
                <div class="auth-avantage">
                    <span class="auth-avantage-icone">2️</span>
                    <span class="auth-avantage-texte">Parcourez le catalogue</span>
                </div>
                <div class="auth-avantage">
                    <span class="auth-avantage-icone">3️</span>
                    <span class="auth-avantage-texte">Commandez votre moto</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panneau droit : formulaire -->
    <div class="auth-formulaire">
        <div class="auth-formulaire-interieur">

            <h1 class="auth-titre">Créer un compte</h1>
            <p class="auth-sous-titre">Rejoignez MotoFlow — c'est gratuit !</p>

            <!-- Affichage des erreurs s'il y en a -->
            <?php if (!empty($erreurs)): ?>
                <div class="message message-erreur" style="flex-direction: column; align-items: flex-start; gap: 5px;">
                    <?php foreach ($erreurs as $erreur): ?>
                        <div>❌ <?= propre($erreur) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" novalidate>

                <!-- Prénom et Nom sur la même ligne -->
                <div class="champs-ligne">
                    <div class="champ-groupe">
                        <label class="champ-label" for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="champ-input"
                               placeholder="Marie"
                               value="<?= propre($_POST['prenom'] ?? '') ?>"
                               autocomplete="given-name" required>
                    </div>
                    <div class="champ-groupe">
                        <label class="champ-label" for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="champ-input"
                               placeholder="Dupont"
                               value="<?= propre($_POST['nom'] ?? '') ?>"
                               autocomplete="family-name" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="champ-groupe">
                    <label class="champ-label" for="email">Email *</label>
                    <input type="email" id="email" name="email" class="champ-input"
                           placeholder="marie.dupont@email.com"
                           value="<?= propre($_POST['email'] ?? '') ?>"
                           autocomplete="email" required>
                </div>

                <!-- Téléphone (optionnel) -->
                <div class="champ-groupe">
                    <label class="champ-label" for="telephone">Téléphone
                        <span style="color: var(--gris); font-weight: normal;">(optionnel)</span>
                    </label>
                    <input type="tel" id="telephone" name="telephone" class="champ-input"
                           placeholder="+33 6 12 34 56 78"
                           value="<?= propre($_POST['telephone'] ?? '') ?>"
                           autocomplete="tel">
                </div>

                <!-- Mots de passe sur la même ligne -->
                <div class="champs-ligne">
                    <div class="champ-groupe">
                        <label class="champ-label" for="mot_de_passe">Mot de passe *</label>
                        <div class="champ-mdp-wrap">
                            <input type="password" id="mot_de_passe" name="mot_de_passe"
                                   class="champ-input" placeholder="Min. 8 caractères"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn-oeil" data-cible="mot_de_passe">👁️</button>
                        </div>
                        <!-- Zone de force du mot de passe (gérée par script.js) -->
                        <div id="zoneForceMdp" class="force-mdp">
                            <div class="force-barre-fond">
                                <div id="forceBarreRemplie" class="force-barre-remplie"></div>
                            </div>
                            <span id="forceTexte" class="force-texte"></span>
                        </div>
                    </div>

                    <div class="champ-groupe">
                        <label class="champ-label" for="mot_de_passe_confirm">Confirmer *</label>
                        <div class="champ-mdp-wrap">
                            <input type="password" id="mot_de_passe_confirm"
                                   name="mot_de_passe_confirm" class="champ-input"
                                   placeholder="Répétez le mot de passe"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn-oeil" data-cible="mot_de_passe_confirm">👁️</button>
                        </div>
                        <!-- Message de correspondance géré par script.js -->
                        <span id="indiceCorrespondance" class="indice-correspondance"></span>
                    </div>
                </div>

                <button type="submit" class="btn-auth">
                    Créer mon compte →
                </button>
            </form>

            <p class="auth-lien-bas">
                Déjà un compte ? <a href="connexion.php">Se connecter</a>
            </p>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
