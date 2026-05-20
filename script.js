/* ============================================================
   script.js — Animations et interactivité MotoFlow
   ============================================================
   Ce fichier s'occupe de tout ce qui bouge et interagit
   côté client (dans le navigateur).
   Il est inclus en bas de chaque page via :
   <script src="script.js"></script>
============================================================ */

/* ==========================================================
   1. DISPARITION AUTOMATIQUE DES MESSAGES FLASH
   Les messages de succès/erreur disparaissent après 4 secondes
========================================================== */
function auto_supprimer_message() {
    var msg = document.getElementById('flashMsg'); // On cherche le message

    if (msg) {
        // On attend 4000 millisecondes (4 secondes)
        setTimeout(function() {
            msg.style.transition = 'opacity 0.5s ease'; // Animation de disparition
            msg.style.opacity = '0';                     // On rend le message invisible
            setTimeout(function() {
                msg.remove(); // On supprime l'élément du HTML après l'animation
            }, 500); // On attend que l'animation finisse (500ms)
        }, 4000); // 4 secondes avant de commencer à disparaître
    }
}

/* ==========================================================
   2. MENU HAMBURGER (menu mobile)
   Sur mobile, le menu se cache et un bouton hamburger apparaît
========================================================== */
function init_menu_hamburger() {
    var btnHamburger = document.getElementById('btnHamburger'); // Bouton hamburger
    var menu = document.getElementById('navbarMenu');           // Le menu à ouvrir/fermer

    if (btnHamburger && menu) {
        // Quand on clique sur le bouton hamburger
        btnHamburger.addEventListener('click', function() {
            // On ajoute ou enlève la classe 'ouvert' sur le menu
            menu.classList.toggle('ouvert');

            // On change l'icône du bouton (☰ ↔ ✕)
            if (menu.classList.contains('ouvert')) {
                btnHamburger.textContent = '✕'; // Icône fermer
            } else {
                btnHamburger.textContent = '☰'; // Icône ouvrir
            }
        });
    }
}

/* ==========================================================
   3. TOGGLE MOT DE PASSE (montrer/cacher)
   Permet de voir le mot de passe tapé en cliquant sur l'œil
========================================================== */
function init_toggle_mdp() {
    // On sélectionne tous les boutons avec la classe 'btn-oeil'
    var boutonsOeil = document.querySelectorAll('.btn-oeil');

    // Pour chaque bouton oeil trouvé
    boutonsOeil.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // On récupère l'ID du champ cible depuis data-cible="..."
            var cible = btn.getAttribute('data-cible');
            var champ = document.getElementById(cible); // On trouve le champ

            if (champ) {
                // On bascule entre 'password' (caché) et 'text' (visible)
                if (champ.type === 'password') {
                    champ.type = 'text';   // On montre le mot de passe
                    btn.textContent = '🙈'; // Icône "cacher"
                } else {
                    champ.type = 'password'; // On cache le mot de passe
                    btn.textContent = '👁️';   // Icône "montrer"
                }
            }
        });
    });
}

/* ==========================================================
   4. INDICATEUR DE FORCE DU MOT DE PASSE
   Affiche une barre colorée qui indique si le mot de passe
   est faible, moyen, fort ou très fort
========================================================== */
function init_force_mdp() {
    var champMdp   = document.getElementById('mot_de_passe');      // Champ mot de passe
    var barreRemplie = document.getElementById('forceBarreRemplie'); // Barre colorée
    var texteForce = document.getElementById('forceTexte');         // Texte (Faible, Fort...)
    var zoneForce  = document.getElementById('zoneForceMdp');       // Conteneur à afficher

    // Si le champ et les éléments d'affichage existent
    if (champMdp && barreRemplie && texteForce && zoneForce) {

        // On écoute chaque touche tapée dans le champ
        champMdp.addEventListener('input', function() {
            var valeur = champMdp.value; // Le mot de passe tapé

            // Si le champ est vide, on cache la barre
            if (valeur.length === 0) {
                zoneForce.style.display = 'none';
                return; // On arrête ici
            }

            zoneForce.style.display = 'block'; // On montre la barre

            // On calcule un score de 0 à 4
            var score = 0;

            if (valeur.length >= 8)              score++; // Au moins 8 caractères
            if (/[A-Z]/.test(valeur))            score++; // Au moins 1 majuscule
            if (/[0-9]/.test(valeur))            score++; // Au moins 1 chiffre
            if (/[^A-Za-z0-9]/.test(valeur))     score++; // Au moins 1 caractère spécial

            // Selon le score, on change la couleur et le texte
            var niveaux = [
                { pourcentage: '20%', couleur: '#e74c3c', texte: 'Très faible' },
                { pourcentage: '40%', couleur: '#e67e22', texte: 'Faible'      },
                { pourcentage: '65%', couleur: '#f1c40f', texte: 'Moyen'       },
                { pourcentage: '85%', couleur: '#2ecc71', texte: 'Fort'        },
                { pourcentage:'100%', couleur: '#27ae60', texte: 'Très fort'   },
            ];

            // On choisit le niveau selon le score (max 4)
            var niveau = niveaux[Math.min(score, niveaux.length - 1)];

            // On applique le style à la barre
            barreRemplie.style.width      = niveau.pourcentage; // Largeur de la barre
            barreRemplie.style.background = niveau.couleur;     // Couleur
            texteForce.textContent        = niveau.texte;        // Texte
            texteForce.style.color        = niveau.couleur;     // Couleur du texte
        });
    }
}

/* ==========================================================
   5. VÉRIFICATION CORRESPONDANCE DES MOTS DE PASSE
   Affiche un message si les deux mots de passe sont différents
========================================================== */
function init_verif_mdp() {
    var champMdp1   = document.getElementById('mot_de_passe');         // Premier champ
    var champMdp2   = document.getElementById('mot_de_passe_confirm'); // Confirmation
    var indice      = document.getElementById('indiceCorrespondance');  // Message d'indice

    if (champMdp1 && champMdp2 && indice) {
        // On écoute les modifications dans le champ de confirmation
        champMdp2.addEventListener('input', function() {
            if (champMdp2.value.length === 0) {
                indice.textContent = ''; // Vide si rien tapé
                return;
            }

            // On compare les deux valeurs
            if (champMdp1.value === champMdp2.value) {
                indice.textContent = '✅ Les mots de passe correspondent';
                indice.style.color = '#27ae60'; // Vert
            } else {
                indice.textContent = '❌ Les mots de passe ne correspondent pas';
                indice.style.color = '#e74c3c'; // Rouge
            }
        });
    }
}

/* ==========================================================
   6. REMPLISSAGE AUTO DES COMPTES DE DÉMO
   Cliquer sur un compte de démo remplit les champs email/mdp
========================================================== */
function init_comptes_demo() {
    // On sélectionne tous les boutons de démo
    var btnsDemos = document.querySelectorAll('.demo-btn');

    btnsDemos.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var email = btn.getAttribute('data-email'); // Email du compte démo
            var mdp   = btn.getAttribute('data-mdp');   // Mot de passe du compte démo

            // On remplit les champs
            var champEmail = document.getElementById('email');
            var champMdp   = document.getElementById('mot_de_passe');

            if (champEmail) champEmail.value = email; // On écrit l'email
            if (champMdp)   champMdp.value   = mdp;   // On écrit le mot de passe

            // Effet visuel : le bouton change brièvement de style
            btn.style.background    = '#d4edda'; // Fond vert temporaire
            btn.style.borderColor   = '#27ae60'; // Bordure verte
            setTimeout(function() {
                btn.style.background  = '';  // On réinitialise après 800ms
                btn.style.borderColor = '';
            }, 800);
        });
    });
}

/* ==========================================================
   7. ANIMATION D'APPARITION DES CARTES (Intersection Observer)
   Les cartes apparaissent progressivement au scroll
   L'Intersection Observer détecte quand un élément est visible
========================================================== */
function init_animations_scroll() {
    // On sélectionne tous les éléments avec la classe 'fade-in'
    var elements = document.querySelectorAll('.fade-in');

    if (elements.length === 0) return; // Rien à animer, on sort

    // On crée un observateur qui surveille la visibilité des éléments
    var observateur = new IntersectionObserver(function(entrees) {
        entrees.forEach(function(entree) {
            // Si l'élément est visible dans la fenêtre
            if (entree.isIntersecting) {
                entree.target.classList.add('visible'); // On ajoute la classe 'visible'
                observateur.unobserve(entree.target);   // On arrête de surveiller cet élément
            }
        });
    }, {
        threshold: 0.1 // L'élément doit être visible à 10% minimum
    });

    // On surveille chaque élément
    elements.forEach(function(el, index) {
        // On ajoute un délai progressif à chaque élément (effet cascade)
        el.style.transitionDelay = (index * 0.08) + 's'; // 0s, 0.08s, 0.16s...
        observateur.observe(el); // On commence à surveiller
    });
}

/* ==========================================================
   8. COMPTEUR ANIMÉ (pour les statistiques)
   Les chiffres s'incrémentent de 0 jusqu'à leur valeur finale
========================================================== */
function animer_compteur(element, valeurFinale, duree) {
    var debut = 0;             // Commence à 0
    var increment = valeurFinale / (duree / 16); // Calcule l'incrément par frame (60fps)

    var minuterie = setInterval(function() {
        debut += increment;    // On incrémente la valeur

        // Si on a atteint ou dépassé la valeur finale
        if (debut >= valeurFinale) {
            element.textContent = valeurFinale; // On affiche la valeur exacte
            clearInterval(minuterie);           // On arrête le minuteur
        } else {
            element.textContent = Math.floor(debut); // On affiche la valeur arrondie
        }
    }, 16); // Toutes les 16ms (~60 images par seconde)
}

function init_compteurs() {
    // On sélectionne tous les éléments avec data-compteur="1234"
    var compteurs = document.querySelectorAll('[data-compteur]');

    compteurs.forEach(function(el) {
        var valeur = parseInt(el.getAttribute('data-compteur')); // Valeur cible

        // On utilise l'Intersection Observer pour démarrer l'animation
        // seulement quand l'élément est visible à l'écran
        var obs = new IntersectionObserver(function(entrees) {
            if (entrees[0].isIntersecting) {
                animer_compteur(el, valeur, 1500); // 1.5 secondes d'animation
                obs.unobserve(el); // Une seule fois
            }
        });
        obs.observe(el);
    });
}

/* ==========================================================
   9. ONGLETS DU DASHBOARD ADMIN
   Permet de basculer entre les différents onglets
========================================================== */
function init_onglets() {
    var btnsOnglets = document.querySelectorAll('.onglet-btn'); // Tous les boutons onglets

    btnsOnglets.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // On récupère l'ID du contenu à afficher
            var cible = btn.getAttribute('data-onglet');

            // On retire la classe 'actif' de tous les boutons et contenus
            document.querySelectorAll('.onglet-btn').forEach(function(b) {
                b.classList.remove('actif');
            });
            document.querySelectorAll('.onglet-contenu').forEach(function(c) {
                c.classList.remove('actif');
            });

            // On ajoute la classe 'actif' au bouton cliqué
            btn.classList.add('actif');

            // On affiche le contenu correspondant
            var contenu = document.getElementById(cible);
            if (contenu) contenu.classList.add('actif');
        });
    });
}

/* ==========================================================
   10. CONFIRMATION AVANT SUPPRESSION
   Demande confirmation avant de supprimer quelque chose
========================================================== */
function init_confirmations() {
    // On sélectionne tous les liens/boutons avec data-confirmer="..."
    var elementsConfirm = document.querySelectorAll('[data-confirmer]');

    elementsConfirm.forEach(function(el) {
        el.addEventListener('click', function(e) {
            var message = el.getAttribute('data-confirmer'); // Le message de confirmation
            // confirm() affiche une boîte de dialogue Oui/Non
            if (!confirm(message)) {
                e.preventDefault(); // On annule l'action si l'utilisateur dit Non
            }
        });
    });
}

/* ==========================================================
   11. PRÉVISUALISATION D'IMAGE AVANT UPLOAD
   Affiche un aperçu de l'image choisie avant de l'envoyer
========================================================== */
function init_preview_image() {
    var champFichier   = document.getElementById('image_fichier');  // Champ file
    var previewImage   = document.getElementById('previewImage');    // Balise <img> de preview
    var previewZone    = document.getElementById('previewZone');     // Conteneur de preview

    if (champFichier && previewImage && previewZone) {
        champFichier.addEventListener('change', function() {
            var fichier = champFichier.files[0]; // Premier fichier sélectionné

            if (fichier) {
                var reader = new FileReader(); // Lecteur de fichier côté navigateur

                // Quand le fichier est chargé
                reader.onload = function(e) {
                    previewImage.src = e.target.result; // On met l'image dans le <img>
                    previewZone.style.display = 'block'; // On affiche la zone de preview
                };

                reader.readAsDataURL(fichier); // On lit le fichier comme URL
            }
        });
    }
}

/* ==========================================================
   12. ANIMATION DU BOUTON PANIER
   Montre un retour visuel lors du clic sur "Ajouter au panier"
   NOTE : On ne désactive PAS le bouton avant soumission,
   sinon les données du bouton ne sont pas envoyées avec le formulaire.
========================================================== */
function init_animation_panier() {
    var formulairesPanier = document.querySelectorAll('.form-ajout-panier');

    formulairesPanier.forEach(function(form) {
        form.addEventListener('submit', function() {
            // On change juste le texte du bouton pour indiquer le chargement
            // mais on ne désactive PAS le bouton (sinon le formulaire ne s'envoie pas bien)
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.textContent = '⏳ Ajout en cours...'; // Retour visuel
                // Le formulaire continue de se soumettre normalement
            }
        });
    });
}

/* ==========================================================
   INITIALISATION GÉNÉRALE
   On appelle toutes les fonctions au chargement de la page
========================================================== */
document.addEventListener('DOMContentLoaded', function() {
    // On appelle chaque fonction d'initialisation
    // Si l'élément concerné n'existe pas sur la page, la fonction sort sans erreur
    auto_supprimer_message();    // Messages flash
    init_menu_hamburger();       // Menu mobile
    init_toggle_mdp();           // Toggle mot de passe
    init_force_mdp();            // Indicateur de force
    init_verif_mdp();            // Vérification correspondance
    init_comptes_demo();         // Comptes de démo
    init_animations_scroll();    // Animations au scroll
    init_compteurs();            // Compteurs animés
    init_onglets();              // Onglets admin
    init_confirmations();        // Confirmations suppression
    init_preview_image();        // Preview image upload
    init_animation_panier();     // Animation panier

    // Message de confirmation dans la console du navigateur
    console.log('MotoFlow chargé avec succès !');
});
