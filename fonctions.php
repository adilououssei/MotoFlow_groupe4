<?php
/* ============================================================
   fonctions.php — Fonctions utilitaires réutilisables
   ============================================================
   Ce fichier contient de petites fonctions qu'on utilise
   souvent dans les autres pages. On l'inclut avec :
   require_once 'fonctions.php';
============================================================ */

/* ----------------------------------------------------------
   Démarrer la session PHP
   La session permet de mémoriser qui est connecté.
   PHP utilise un cookie côté navigateur pour identifier
   le visiteur et retrouver ses données côté serveur.
---------------------------------------------------------- */
function demarrer_session() {
    // On démarre la session seulement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Démarre ou reprend la session
    }
}

/* ----------------------------------------------------------
   Vérifier si l'utilisateur est connecté
   Retourne true si connecté, false sinon
---------------------------------------------------------- */
function est_connecte() {
    demarrer_session(); // On s'assure que la session est démarrée
    return isset($_SESSION['user_id']); // true si user_id existe en session
}

/* ----------------------------------------------------------
   Vérifier si l'utilisateur est administrateur
   Retourne true si admin, false sinon
---------------------------------------------------------- */
function est_admin() {
    demarrer_session(); // On s'assure que la session est démarrée
    // On vérifie que l'utilisateur est connecté ET que son rôle est 'admin'
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/* ----------------------------------------------------------
   Nettoyer une valeur pour l'affichage HTML (protection XSS)
   XSS = Cross-Site Scripting = injection de code malveillant
   Exemple : si quelqu'un écrit <script>alert('hack')</script>
   dans un champ, htmlspecialchars transforme les < > en &lt; &gt;
   pour que le navigateur l'affiche comme texte et non comme code.
---------------------------------------------------------- */
function propre($valeur) {
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}

/* ----------------------------------------------------------
   Rediriger vers une autre page
   Exemple : rediriger('connexion.php');
---------------------------------------------------------- */
function rediriger($page) {
    header('Location: ' . $page); // Envoie l'en-tête HTTP de redirection
    exit(); // On arrête le script pour ne pas continuer après la redirection
}

/* ----------------------------------------------------------
   Afficher un message flash (message temporaire)
   Un message flash est stocké en session, affiché une fois,
   puis supprimé. Utilisé pour les messages de succès/erreur.
   Exemple après une inscription : "Compte créé avec succès !"
---------------------------------------------------------- */
function set_message($type, $texte) {
    demarrer_session(); // On s'assure que la session est démarrée
    $_SESSION['message_type'] = $type;   // 'succes' ou 'erreur'
    $_SESSION['message_texte'] = $texte; // Le texte du message
}

function afficher_message() {
    demarrer_session(); // On s'assure que la session est démarrée

    // On vérifie s'il y a un message en attente
    if (isset($_SESSION['message_texte'])) {
        $type  = $_SESSION['message_type'];   // Récupère le type
        $texte = $_SESSION['message_texte'];  // Récupère le texte

        // On supprime le message de la session (il ne s'affiche qu'une fois)
        unset($_SESSION['message_type']);
        unset($_SESSION['message_texte']);

        // On choisit la couleur selon le type
        $couleur = ($type === 'succes') ? '#27ae60' : '#c0392b';

        // On affiche le message en HTML
        echo '<div class="message message-' . $type . '" id="flashMsg">
                <span>' . propre($texte) . '</span>
                <button onclick="this.parentElement.remove()">×</button>
              </div>';
    }
}

/* ----------------------------------------------------------
   Vérifier que l'utilisateur connecté existe vraiment en BD
   À appeler dans toutes les pages protégées qui font des INSERT.
   Si la BD a été réimportée, l'ancien user_id en session
   ne correspond plus → on déconnecte proprement.
   Exemple : verifier_session($pdo);
---------------------------------------------------------- */
function verifier_session($pdo) {
    demarrer_session();
    if (!isset($_SESSION['user_id'])) return; // Pas connecté, pas de vérif

    // On cherche l'utilisateur en BD
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    if (!$stmt->fetch()) {
        // L'utilisateur n'existe plus en BD (BD réimportée, compte supprimé...)
        // On nettoie la session et on redirige vers la connexion
        session_unset();   // Vide toutes les variables de session
        session_destroy(); // Détruit la session côté serveur
        header('Location: connexion.php?session_expiree=1');
        exit();
    }
}
  /* Utilisé pour afficher le badge (chiffre) sur l'icône panier
---------------------------------------------------------- */
function compter_panier($pdo, $user_id) {
    // Requête SQL pour compter les lignes dans la table panier
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM panier WHERE utilisateur_id = ?");
    // On execute avec l'id de l'utilisateur connecté
    $stmt->execute([$user_id]);
    // On retourne le chiffre trouvé (0 si rien dans le panier)
    return $stmt->fetchColumn();
}
?>
