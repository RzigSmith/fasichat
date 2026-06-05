<?php $pageTitle = 'Convocations — FasiChat Classroom'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1>📨 Convocations</h1>
            <p>Réunions convoquées par le Doyen ou le Vice-Doyen</p>
        </div>
        <?php if ($canConvoquer): ?>
        <a href="<?= BASE_PATH ?>/convocation/envoyer" class="btn btn-primary">+ Convoquer une réunion</a>
        <?php endif; ?>
    </div>

    <?php if (empty($convocations)): ?>
    <div class="empty-state">
        <svg width="64" height="64" fill="none" stroke="#CBD5E0" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 9"/>
        </svg>
        <p>Aucune convocation reçue.</p>
    </div>
    <?php else: ?>
    <div class="convocations-list">
        <?php foreach ($convocations as $conv): ?>
        <div class="convocation-card <?= $conv['lu'] ? '' : 'unread' ?>" onclick="marquerLu(<?= $conv['id'] ?>, this)">
            <div class="conv-indicator"></div>
            <div class="conv-content">
                <div class="conv-header">
                    <h3><?= htmlspecialchars($conv['objet'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if (!$conv['lu']): ?>
                    <span class="badge badge-new">Nouveau</span>
                    <?php endif; ?>
                </div>
                <div class="conv-meta">
                    <span>📅 <?= date('d/m/Y à H:i', strtotime($conv['date_reunion'])) ?></span>
                    <span>📍 <?= htmlspecialchars($conv['lieu'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span>De : <strong><?= htmlspecialchars($conv['exp_prenom'] . ' ' . $conv['exp_nom'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= ROLES[$conv['exp_role']] ?? $conv['exp_role'] ?>)</span>
                </div>
                <?php if (!empty($conv['message'])): ?>
                <p class="conv-message"><?= nl2br(htmlspecialchars($conv['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                <?php endif; ?>
                <span class="conv-date">Reçu le <?= date('d/m/Y à H:i', strtotime($conv['date_envoi'])) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function marquerLu(id, el) {
    if (!el.classList.contains('unread')) return;
    fetch('/convocation/lire', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'convocation_id=' + id
    }).then(() => {
        el.classList.remove('unread');
        const badge = el.querySelector('.badge-new');
        if (badge) badge.remove();
    });
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
