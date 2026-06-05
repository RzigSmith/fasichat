<?php $pageTitle = 'Administration — FasiChat Classroom'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1>⚙️ Administration</h1>
            <p>Gestion des utilisateurs, promotions et cours</p>
        </div>
        <a href="<?= BASE_PATH ?>/register" class="btn btn-primary">+ Créer un compte</a>
    </div>

    <!-- Statistiques rapides -->
    <div class="stats-grid">
        <?php
        $rolesCount = array_count_values(array_column($utilisateurs, 'role'));
        ?>
        <div class="stat-card"><span class="stat-num"><?= $rolesCount['etudiant'] ?? 0 ?></span><span class="stat-label">Étudiants</span></div>
        <div class="stat-card"><span class="stat-num"><?= ($rolesCount['enseignant'] ?? 0) + ($rolesCount['assistant'] ?? 0) ?></span><span class="stat-label">Enseignants/Assistants</span></div>
        <div class="stat-card"><span class="stat-num"><?= count($promotions) ?></span><span class="stat-label">Promotions</span></div>
        <div class="stat-card"><span class="stat-num"><?= count($cours) ?></span><span class="stat-label">Cours</span></div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="dashboard-card" style="margin-top:24px">
        <div class="card-header">
            <h2>👥 Utilisateurs</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Promotion</th><th>Inscrit le</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="role-badge role-<?= $u['role'] ?>"><?= ROLES[$u['role']] ?? $u['role'] ?></span></td>
                        <td><?= htmlspecialchars($u['promo_nom'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
