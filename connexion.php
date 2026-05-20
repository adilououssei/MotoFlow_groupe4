<?php
/* ============================================================
   connexion.php — Page de connexion
   ============================================================
   Cette page fait 2 choses :
   1. Affiche le formulaire de connexion (méthode GET)
   2. Traite le formulaire quand on le soumet (méthode POST)
   Tout est dans le même fichier pour que ce soit simple !
============================================================ */

// On inclut nos fichiers nécessaires
require_once 'db.php';         // Connexion à la base de données
require_once 'fonctions.php';  // Nos fonctions utilitaires
demarrer_session();            // Démarre la session

// Si l'utilisateur est déjà connecté, on le redirige
// Il n'a pas besoin de voir la page de connexion
if (est_connecte()) {
    rediriger('accueil.php'); // On renvoie vers l'accueil
}

/* ----------------------------------------------------------
   TRAITEMENT DU FORMULAIRE (seulement si soumis en POST)
   $_SERVER['REQUEST_METHOD'] === 'POST' signifie que le
   formulaire a été soumis (bouton "Se connecter" cliqué)
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On récupère les données du formulaire envoyées en POST
    // trim() enlève les espaces en début/fin de chaîne
    $email = trim($_POST['email'] ?? '');
    $mdp   = trim($_POST['mot_de_passe'] ?? '');

    // Vérification basique : les deux champs doivent être remplis
    if (empty($email) || empty($mdp)) {
        // On stocke le message d'erreur et on réaffiche la page
        set_message('erreur', 'Veuillez remplir tous les champs.');

    } else {
        // On cherche l'utilisateur dans la base de données par son email
        // On utilise une requête préparée pour éviter les injections SQL
        // Le ? sera remplacé par $email de façon sécurisée
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]); // On lance la requête avec l'email
        $utilisateur = $stmt->fetch(); // On récupère l'utilisateur trouvé (ou false)

        // On vérifie deux conditions :
        // 1. L'utilisateur existe en base de données
        // 2. Le mot de passe correspond au hash stocké
        //    password_verify() compare le mot de passe tapé avec le hash
        if ($utilisateur && password_verify($mdp, $utilisateur['mot_de_passe'])) {

            // Connexion réussie ! On crée la session
            // On stocke les infos de l'utilisateur dans $_SESSION
            $_SESSION['user_id'] = $utilisateur['id'];      // ID en base de données
            $_SESSION['prenom']  = $utilisateur['prenom'];  // Prénom pour l'affichage
            $_SESSION['nom']     = $utilisateur['nom'];     // Nom
            $_SESSION['email']   = $utilisateur['email'];   // Email
            $_SESSION['role']    = $utilisateur['role'];    // 'client' ou 'admin'

            // session_regenerate_id() crée un nouvel ID de session
            // C'est une mesure de sécurité contre le vol de session
            session_regenerate_id(true);

            // Message de bienvenue
            set_message('succes', 'Bienvenue ' . $utilisateur['prenom'] . ' !');

            // On redirige selon le rôle
            if ($utilisateur['role'] === 'admin') {
                rediriger('admin.php');     // Admin → dashboard admin
            } else {
                rediriger('accueil.php');   // Client → page d'accueil
            }

        } else {
            // Email ou mot de passe incorrect
            // On donne un message vague pour ne pas aider un pirate
            // (on ne dit pas si c'est l'email ou le mdp qui est faux)
            set_message('erreur', 'Email ou mot de passe incorrect.');
        }
    }
}

// Si l'utilisateur arrive depuis une session expirée, on affiche un message
if (isset($_GET['session_expiree'])) {
    set_message('erreur', 'Votre session a expiré. Veuillez vous reconnecter.');
}

// Titre de la page
$titre_page = 'Connexion';
require_once 'header.php'; // Affiche le header HTML
?>

<!-- ============================================================
     MISE EN PAGE SPLIT : Visuel à gauche, Formulaire à droite
============================================================ -->
<div class="page-auth">

    <!-- ── PANNEAU GAUCHE : visuel et avantages ── -->
    <div class="auth-visuel">
        <div class="auth-visuel-contenu">
            <!-- Logo MotoFlow -->
            <img src="logo.png" alt="MotoFlow" onerror="this.style.display='none'">
            <h2 class="auth-visuel-titre">
                La fluidité dans<br>l'achat de motos
            </h2>
            <p class="auth-visuel-texte">
                Accédez à notre catalogue de motos premium
                et vivez une expérience d'achat unique.
            </p>
            <!-- Liste des avantages -->
            <div class="auth-avantages">
                <div class="auth-avantage">
                    <span class="auth-avantage-icone"></span>
                    <span class="auth-avantage-texte">Motos certifiées qualité</span>
                </div>
                <div class="auth-avantage">
                    <span class="auth-avantage-icone"></span>
                    <span class="auth-avantage-texte">Livraison en 48h partout</span>
                </div>
                <div class="auth-avantage">
                    <span class="auth-avantage-icone"></span>
                    <span class="auth-avantage-texte">Paiement 100% sécurisé</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── PANNEAU DROIT : formulaire ── -->
    <div class="auth-formulaire">
        <div class="auth-formulaire-interieur">

            <h1 class="auth-titre">Bon retour </h1>
            <p class="auth-sous-titre">Connectez-vous à votre compte MotoFlow</p>

            <!-- Formulaire de connexion -->
            <!-- action="" = envoie vers la même page (connexion.php) -->
            <!-- method="POST" = les données sont envoyées en POST (sécurisé) -->
            <form action="" method="POST" novalidate>

                <!-- Champ Email -->
                <div class="champ-groupe">
                    <label class="champ-label" for="email"> Adresse email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="champ-input"
                        placeholder="exemple@email.com"
                        value="<?= propre($_POST['email'] ?? '') ?>"
                        autocomplete="email"
                        required
                    >
                    <!-- value="..." pré-remplit avec la valeur si erreur (évite de retaper) -->
                </div>

                <!-- Champ Mot de passe avec bouton œil -->
                <div class="champ-groupe">
                    <label class="champ-label" for="mot_de_passe"> Mot de passe</label>
                    <div class="champ-mdp-wrap">
                        <input
                            type="password"
                            id="mot_de_passe"
                            name="mot_de_passe"
                            class="champ-input"
                            placeholder="Votre mot de passe"
                            autocomplete="current-password"
                            required
                        >
                        <!-- Bouton pour montrer/cacher le mot de passe -->
                        <!-- data-cible="mot_de_passe" indique à JS quel champ cibler -->
                        <button type="button" class="btn-oeil" data-cible="mot_de_passe" title="Afficher/masquer">
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Bouton de connexion -->
                <button type="submit" class="btn-auth">
                    Se connecter →
                </button>
            </form>

            <!-- Lien vers l'inscription -->
            <p class="auth-lien-bas">
                Pas encore de compte ?
                <a href="inscription.php">Créer un compte gratuit</a>
            </p>

            <!-- Comptes de démonstration pour les tests -->
            <div class="demo-comptes">
                <p class="demo-titre">Comptes de test</p>
                <!-- data-email et data-mdp sont lus par script.js pour remplir les champs -->
                <button class="demo-btn" data-email="admin@motoflow.com" data-mdp="password">
                    <span class="demo-role role-admin">Admin</span>
                    admin@motoflow.com
                </button>
                <button class="demo-btn" data-email="client@motoflow.com" data-mdp="password">
                    <span class="demo-role role-client">Client</span>
                    client@motoflow.com
                </button>
            </div>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
