<?php $pageTitle = ($annonce ?? null) ? 'Modifier l\'annonce' : 'Nouvelle annonce'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="page-container narrow">
    <div class="page-header">
        <h1><?= $annonce ? 'Modifier l\'annonce' : 'Nouvelle annonce' ?></h1>
        <a href="<?= BASE_PATH ?>/valve" class="btn btn-secondary">← Retour</a>
    </div>

    <?php if ($erreur ?? null): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <?php if ($annonce ?? null): ?>
        <input type="hidden" name="id" value="<?= $annonce['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Titre *</label>
            <input type="text" name="titre" value="<?= htmlspecialchars($annonce['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label>Contenu *</label>
            <textarea name="contenu" rows="6" required><?= htmlspecialchars($annonce['contenu'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="form-group">
            <label>Date d'expiration (optionnel)</label>
            <input type="date" name="date_expiration" value="<?= $annonce['date_expiration'] ?? '' ?>">
        </div>
        <div class="form-actions">
            <a href="<?= BASE_PATH ?>/valve" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary"><?= $annonce ? 'Enregistrer' : 'Publier' ?></button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
