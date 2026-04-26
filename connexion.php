<?php
/* ============================================================
   inscription.php — Page d'inscription (VERSION STATIQUE)
   ============================================================
   Version démo - le formulaire n'est pas fonctionnel
============================================================ */

$titre_page = 'Inscription';
require_once 'header.php';
?>

<div class="page-auth">
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
            <div class="auth-avantages">
                <div class="auth-avantage">
                    <span class="auth-avantage-icone">1️⃣</span>
                    <span class="auth-avantage-texte">Créez votre compte gratuit</span>
                </div>
                <div class="auth-avantage">
                    <span class="auth-avantage-icone">2️⃣</span>
                    <span class="auth-avantage-texte">Parcourez le catalogue</span>
                </div>
                <div class="auth-avantage">
                    <span class="auth-avantage-icone">3️⃣</span>
                    <span class="auth-avantage-texte">Commandez votre moto</span>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-formulaire">
        <div class="auth-formulaire-interieur">
            <h1 class="auth-titre">Créer un compte</h1>
            <p class="auth-sous-titre">Rejoignez MotoFlow — c'est gratuit !</p>

            <div style="background:#e8f0fe; padding:10px 15px; border-radius:8px; margin-bottom:20px;">
                <span style="color:var(--bleu-fonce);">ℹ️</span> 
                <span style="color:var(--gris);">Version démo - Formulaire non fonctionnel pour l'instant</span>
            </div>

            <form action="" method="POST" novalidate>
                <div class="champs-ligne">
                    <div class="champ-groupe">
                        <label class="champ-label" for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="champ-input"
                               placeholder="Marie" autocomplete="given-name">
                    </div>
                    <div class="champ-groupe">
                        <label class="champ-label" for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="champ-input"
                               placeholder="Dupont" autocomplete="family-name">
                    </div>
                </div>

                <div class="champ-groupe">
                    <label class="champ-label" for="email">📧 Email *</label>
                    <input type="email" id="email" name="email" class="champ-input"
                           placeholder="marie.dupont@email.com" autocomplete="email">
                </div>

                <div class="champ-groupe">
                    <label class="champ-label" for="telephone">📱 Téléphone
                        <span style="color: var(--gris); font-weight: normal;">(optionnel)</span>
                    </label>
                    <input type="tel" id="telephone" name="telephone" class="champ-input"
                           placeholder="+33 6 12 34 56 78" autocomplete="tel">
                </div>

                <div class="champs-ligne">
                    <div class="champ-groupe">
                        <label class="champ-label" for="mot_de_passe">🔒 Mot de passe *</label>
                        <div class="champ-mdp-wrap">
                            <input type="password" id="mot_de_passe" name="mot_de_passe"
                                   class="champ-input" placeholder="Min. 8 caractères">
                            <button type="button" class="btn-oeil" data-cible="mot_de_passe">👁️</button>
                        </div>
                    </div>

                    <div class="champ-groupe">
                        <label class="champ-label" for="mot_de_passe_confirm">Confirmer *</label>
                        <div class="champ-mdp-wrap">
                            <input type="password" id="mot_de_passe_confirm"
                                   name="mot_de_passe_confirm" class="champ-input"
                                   placeholder="Répétez le mot de passe">
                            <button type="button" class="btn-oeil" data-cible="mot_de_passe_confirm">👁️</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-auth" disabled style="opacity:0.6; cursor:not-allowed;">
                    Créer mon compte → (démo)
                </button>
            </form>

            <p class="auth-lien-bas">
                Déjà un compte ? <a href="connexion.php">Se connecter</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>