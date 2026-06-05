<?php $pageTitle = 'Tableau de bord — FasiChat Classroom'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1>🎓 Tableau de bord</h1>
            <p>Bienvenue, <?= htmlspecialchars($userData['prenom'] . ' ' . $userData['nom'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Mes cours -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>📚 Mes cours</h2>
                <span class="badge"><?= count($cours) ?></span>
            </div>
            <?php if (empty($cours)): ?>
            <p class="empty-text">Aucun cours affecté.</p>
            <?php else: ?>
            <ul class="cours-list">
                <?php foreach ($cours as $c): ?>
                <li>
                    <a href="<?= BASE_PATH ?>/dashboard?cours_id=<?= $c['id'] ?>" class="cours-item <?= $coursId === (int)$c['id'] ? 'active' : '' ?>">
                        <span><?= htmlspecialchars($c['titre'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="promo-tag"><?= htmlspecialchars($c['promotion_nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <!-- Mur pédagogique -->
        <?php if ($coursId): ?>
        <div class="dashboard-card span-2">
            <div class="card-header">
                <h2>📌 Mur pédagogique</h2>
            </div>
            <form method="POST" class="mur-form">
                <input type="hidden" name="action" value="publier_mur">
                <input type="hidden" name="cours_id" value="<?= $coursId ?>">
                <div class="form-group">
                    <textarea name="contenu" rows="3" placeholder="Publiez une question ou une annonce pour ce cours…" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Publier</button>
            </form>
            <div class="publications-list">
                <?php if (empty($publications)): ?>
                <p class="empty-text">Aucune publication sur ce mur.</p>
                <?php else: ?>
                <?php foreach ($publications as $pub): ?>
                <div class="publication-item">
                    <div class="pub-avatar role-<?= $pub['auteur_role'] ?>">
                        <?= strtoupper(substr($pub['auteur_prenom'], 0, 1) . substr($pub['auteur_nom'], 0, 1)) ?>
                    </div>
                    <div class="pub-body">
                        <div class="pub-header">
                            <span class="pub-author"><?= htmlspecialchars($pub['auteur_prenom'] . ' ' . $pub['auteur_nom'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="pub-date"><?= date('d/m/Y H:i', strtotime($pub['date_publication'])) ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($pub['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des étudiants -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>👩‍🎓 Mes étudiants</h2>
                <span class="badge"><?= count($etudiants) ?></span>
            </div>
            <?php if (empty($etudiants)): ?>
            <p class="empty-text">Aucun étudiant affilié.</p>
            <?php else: ?>
            <ul class="etudiants-list">
                <?php foreach ($etudiants as $etu): ?>
                <li class="etudiant-item">
                    <div class="msg-avatar-small role-etudiant">
                        <?= strtoupper(substr($etu['prenom'], 0, 1) . substr($etu['nom'], 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($etu['prenom'] . ' ' . $etu['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                    <a href="<?= BASE_PATH ?>/chat?avec=<?= $etu['id'] ?>" class="btn btn-sm btn-secondary">Message</a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <!-- Convocations reçues -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>📨 Convocations</h2>
                <span class="badge"><?= count(array_filter($convocations, fn($c) => !$c['lu'])) ?> non lues</span>
            </div>
            <?php if (empty($convocations)): ?>
            <p class="empty-text">Aucune convocation reçue.</p>
            <?php else: ?>
            <ul class="conv-mini-list">
                <?php foreach (array_slice($convocations, 0, 3) as $conv): ?>
                <li class="conv-mini-item <?= $conv['lu'] ? '' : 'unread' ?>">
                    <strong><?= htmlspecialchars($conv['objet'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= date('d/m/Y', strtotime($conv['date_reunion'])) ?> — <?= htmlspecialchars($conv['lieu'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?= BASE_PATH ?>/convocations" class="btn btn-sm btn-secondary" style="margin-top:12px;display:inline-block">Voir toutes</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
