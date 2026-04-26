<?php
/* ============================================================
   admin.php — Dashboard administrateur (VERSION STATIQUE)
   ============================================================
   Version sans base de données pour la démonstration
   Les fonctionnalités d'ajout/modif/suppression ne sont PAS actives
============================================================ */

require_once 'header.php';

// Données statiques
$nb_motos = 24;
$nb_clients = 156;
$nb_commandes = 42;
$ca_total = 425000000;
$nb_en_attente = 3;

$toutes_motos = [
    ['id' => 1, 'marque' => 'Yamaha', 'modele' => 'MT-07', 'prix' => 7599000, 'stock' => 5, 'annee' => 2024],
    ['id' => 2, 'marque' => 'Honda', 'modele' => 'CBR 650R', 'prix' => 8999000, 'stock' => 3, 'annee' => 2024],
    ['id' => 3, 'marque' => 'Kawasaki', 'modele' => 'Z900', 'prix' => 10499000, 'stock' => 7, 'annee' => 2023],
    ['id' => 4, 'marque' => 'Suzuki', 'modele' => 'GSX-8S', 'prix' => 8699000, 'stock' => 2, 'annee' => 2024],
    ['id' => 5, 'marque' => 'BMW', 'modele' => 'R 1250 GS', 'prix' => 18999000, 'stock' => 1, 'annee' => 2024],
];

$toutes_commandes = [
    ['id' => 101, 'prenom' => 'Jean', 'nom' => 'Dupont', 'email' => 'jean@email.com', 'total' => 7599000, 'statut' => 'en_attente', 'cree_le' => '2024-01-15 10:30:00'],
    ['id' => 102, 'prenom' => 'Marie', 'nom' => 'Martin', 'email' => 'marie@email.com', 'total' => 8999000, 'statut' => 'confirmee', 'cree_le' => '2024-01-14 14:20:00'],
    ['id' => 103, 'prenom' => 'Pierre', 'nom' => 'Bernard', 'email' => 'pierre@email.com', 'total' => 10499000, 'statut' => 'expediee', 'cree_le' => '2024-01-13 09:15:00'],
];

$tous_clients = [
    ['id' => 1, 'prenom' => 'Jean', 'nom' => 'Dupont', 'email' => 'jean@email.com', 'telephone' => '90123456', 'cree_le' => '2024-01-01 10:00:00'],
    ['id' => 2, 'prenom' => 'Marie', 'nom' => 'Martin', 'email' => 'marie@email.com', 'telephone' => '90234567', 'cree_le' => '2024-01-02 11:00:00'],
    ['id' => 3, 'prenom' => 'Pierre', 'nom' => 'Bernard', 'email' => 'pierre@email.com', 'telephone' => '90345678', 'cree_le' => '2024-01-03 12:00:00'],
];

$titre_page = 'Dashboard Admin';
?>

<div class="conteneur" style="padding: 25px 0 60px;">

    <div class="flex-entre" style="margin-bottom: 25px;">
        <div>
            <h1 class="titre-section">⚙️ Dashboard Admin</h1>
            <?php if ($nb_en_attente > 0): ?>
                <div style="color: var(--orange); font-weight: 600; margin-top: 5px;">
                    ⚠️ <?= $nb_en_attente ?> commande<?= $nb_en_attente > 1 ? 's' : '' ?> en attente de traitement
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Onglets -->
    <div class="onglets">
        <button class="onglet-btn actif" data-onglet="onglet-stats">📊 Statistiques</button>
        <button class="onglet-btn" data-onglet="onglet-motos">🏍️ Motos</button>
        <button class="onglet-btn" data-onglet="onglet-commandes">📦 Commandes <?php if($nb_en_attente>0): ?><span style="color:var(--rouge)">(<?=$nb_en_attente?>)</span><?php endif; ?></button>
        <button class="onglet-btn" data-onglet="onglet-clients">👥 Clients</button>
    </div>

    <!-- ONGLET STATISTIQUES -->
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

        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;">📋 Dernières commandes</h3>
            <table class="tableau-admin">
                <thead>
                    <tr><th>#</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($toutes_commandes, 0, 3) as $cmd): ?>
                        <tr>
                            <td><strong>#<?= $cmd['id'] ?></strong></td>
                            <td><?= htmlspecialchars($cmd['prenom']) ?> <?= htmlspecialchars($cmd['nom']) ?></td>
                            <td style="font-weight:700; color:var(--rouge);"><?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA</td>
                            <td><span class="statut statut-<?= $cmd['statut'] ?>"><?= ucfirst(str_replace('_',' ',$cmd['statut'])) ?></span></td>
                            <td style="color:var(--gris);"><?= date('d/m/Y', strtotime($cmd['cree_le'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ONGLET MOTOS (version statique - pas de formulaire actif) -->
    <div class="onglet-contenu" id="onglet-motos">
        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;">
                📋 Catalogue (<?= count($toutes_motos) ?> motos)
            </h3>
            <div style="margin-bottom:15px; padding:10px; background:#e8f0fe; border-radius:8px;">
                <span style="color:var(--bleu-fonce);">ℹ️</span> 
                <span style="color:var(--gris);">Version démo - Les fonctionnalités d'ajout/modification seront ajoutées plus tard</span>
            </div>
            <table class="tableau-admin">
                <thead>
                    <tr><th>ID</th><th>Marque / Modèle</th><th>Prix</th><th>Stock</th><th>Année</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($toutes_motos as $moto): ?>
                        <tr>
                            <td>#<?= $moto['id'] ?></td>
                            <td><strong><?= htmlspecialchars($moto['marque']) ?></strong> <?= htmlspecialchars($moto['modele']) ?></td>
                            <td><?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= $moto['stock'] ?></td>
                            <td><?= $moto['annee'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ONGLET COMMANDES -->
    <div class="onglet-contenu" id="onglet-commandes">
        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;">
                📦 Toutes les commandes (<?= count($toutes_commandes) ?>)
            </h3>
            <table class="tableau-admin">
                <thead>
                    <tr><th>#</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($toutes_commandes as $cmd): ?>
                        <tr>
                            <td><strong>#<?= $cmd['id'] ?></strong></td>
                            <td>
                                <div><strong><?= htmlspecialchars($cmd['prenom']) ?> <?= htmlspecialchars($cmd['nom']) ?></strong></div>
                                <div style="color:var(--gris); font-size:0.8rem;"><?= htmlspecialchars($cmd['email']) ?></div>
                            </td>
                            <td><?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA</td>
                            <td><span class="statut statut-<?= $cmd['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($cmd['cree_le'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ONGLET CLIENTS -->
    <div class="onglet-contenu" id="onglet-clients">
        <div class="carte-blanche">
            <h3 style="color:var(--bleu-fonce); margin-bottom:15px;">
                👥 Clients inscrits (<?= count($tous_clients) ?>)
            </h3>
            <table class="tableau-admin">
                <thead>
                    <tr><th>#</th><th>Nom complet</th><th>Email</th><th>Téléphone</th><th>Inscrit le</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tous_clients as $client): ?>
                        <tr>
                            <td>#<?= $client['id'] ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:32px; height:32px; background:var(--rouge); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                        <?= strtoupper(substr($client['prenom'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($client['prenom']) ?> <?= htmlspecialchars($client['nom']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($client['email']) ?></td>
                            <td><?= htmlspecialchars($client['telephone'] ?: '—') ?></td>
                            <td><?= date('d/m/Y', strtotime($client['cree_le'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>