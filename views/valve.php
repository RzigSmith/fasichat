<?php $pageTitle = 'Valve — FasiChat Classroom'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1>📋 Valve</h1>
            <p>Annonces et informations officielles de l'administration</p>
        </div>
        <?php if ($isAdmin): ?>
        <a href="<?= BASE_PATH ?>/valve/publier" class="btn btn-primary">+ Nouvelle annonce</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['succes'])): ?>
    <div class="alert alert-success">Annonce enregistrée avec succès.</div>
    <?php endif; ?>

    <?php if (empty($annonces)): ?>
    <div class="empty-state">
        <svg width="64" height="64" fill="none" stroke="#CBD5E0" stroke-width="1.5" viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
        </svg>
        <p>Aucune annonce pour le moment.</p>
    </div>
    <?php else: ?>
    <div class="annonces-grid">
        <?php foreach ($annonces as $annonce): ?>
        <article class="annonce-card">
            <div class="annonce-header">
                <h3><?= htmlspecialchars($annonce['titre'], ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (!empty($annonce['date_expiration']) && strtotime($annonce['date_expiration']) < time()): ?>
                <span class="badge badge-expired">Expirée</span>
                <?php endif; ?>
            </div>
            <div class="annonce-body">
                <?= nl2br(htmlspecialchars($annonce['contenu'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
            <div class="annonce-footer">
                <span>Par <strong><?= htmlspecialchars($annonce['auteur_prenom'] . ' ' . $annonce['auteur_nom'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                <span><?= date('d/m/Y à H:i', strtotime($annonce['date_publication'])) ?></span>
                <?php if (!empty($annonce['date_expiration'])): ?>
                <span>Expire le <?= date('d/m/Y', strtotime($annonce['date_expiration'])) ?></span>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                <div class="annonce-actions">
                    <a href="<?= BASE_PATH ?>/valve/modifier?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                    <button onclick="supprimerAnnonce(<?= $annonce['id'] ?>)" class="btn btn-sm btn-danger">Supprimer</button>
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($isAdmin): ?>
<script>
function supprimerAnnonce(id) {
    if (!confirm('Supprimer cette annonce ?')) return;
    fetch('/valve/supprimer', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
        else alert('Erreur lors de la suppression.');
    });
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
